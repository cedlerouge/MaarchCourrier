import { Component, Inject, ViewChild, AfterViewInit, Input, EventEmitter, Output } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { MAT_LEGACY_DIALOG_DATA as MAT_DIALOG_DATA, MatLegacyDialogRef as MatDialogRef } from '@angular/material/legacy-dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { tap, finalize, catchError, exhaustMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { FunctionsService } from '@service/functions.service';
import { VisaWorkflowComponent } from '../../visa/visa-workflow.component';
import { ActionsService } from '../actions.service';
import { Router } from '@angular/router';
import { SessionStorageService } from '@service/session-storage.service';
import { UserWorkflow } from '@models/user-workflow.model';
import { MatSidenav } from '@angular/material/sidenav';
import { AttachmentsListComponent } from '@appRoot/attachments/attachments-list.component';
import { AppService } from '@service/app.service';

@Component({
    templateUrl: 'send-signature-book-action.component.html',
    styleUrls: ['send-signature-book-action.component.scss'],
})
export class SendSignatureBookActionComponent implements AfterViewInit {

    @ViewChild('noteEditor', { static: false }) noteEditor: NoteEditorComponent;
    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('attachmentsList', { static: false }) attachmentsList: AttachmentsListComponent;
    @ViewChild('snav2', { static: false }) public snav2: MatSidenav;

    @Output() sidenavStateChanged = new EventEmitter<boolean>();

    actionService: ActionsService; // To resolve circular dependencies

    loading: boolean = true;

    resourcesMailing: any[] = [];
    resourcesError: any[] = [];

    noResourceToProcess: boolean = null;

    integrationsInfo: any = {
        inSignatureBook: {
            icon: 'fas fa-file-signature'
        }
    };

    minimumVisaRole: number = 0;
    maximumSignRole: number = 0;
    visaNumberCorrect: boolean = true;
    signNumberCorrect: boolean = true;
    atLeastOneSign: boolean = true;
    lastOneIsSign: boolean = true;
    lastOneMustBeSignatory: boolean = false;
    workflowSignatoryRole: string = '';
    lockVisaCircuit: boolean;
    visaWorkflowClone: UserWorkflow[];

    canGoToNextRes: boolean = false;
    showToggle: boolean = false;
    inLocalStorage: boolean = false;

    constructor(
        @Inject(MAT_DIALOG_DATA) public data: any,
        public translate: TranslateService,
        public http: HttpClient,
        public dialogRef: MatDialogRef<SendSignatureBookActionComponent>,
        public functions: FunctionsService,
        public route: Router,
        private notify: NotificationService,
        public appService: AppService,
        private sessionStorage: SessionStorageService
    ) { }

    async ngAfterViewInit(): Promise<void> {
        if (this.data.resIds.length === 0) {
            // Indexing page
            this.checkSignatureBookInIndexingPage();
        }
        this.initVisaWorkflow();
        this.showToggle = this.data.additionalInfo.showToggle;
        this.canGoToNextRes = this.data.additionalInfo.canGoToNextRes;
        this.inLocalStorage = this.data.additionalInfo.inLocalStorage;
        this.loading = false;
    }

    async onSubmit(): Promise<any> {
        this.loading = true;

        if (this.data.resIds.length === 0) {
            let res: boolean = await this.indexDocument();
            if (res) {
                res = await this.appVisaWorkflow.saveVisaWorkflow(this.data.resIds) as boolean;
            }
            if (res) {
                this.executeIndexingAction(this.data.resIds[0]);
            }
        } else {
            const realResSelected: number[] = this.data.resIds.filter((resId: any) => this.resourcesError.map(resErr => resErr.res_id).indexOf(resId) === -1);

            const res = await this.appVisaWorkflow.saveVisaWorkflow(realResSelected);

            if (res) {
                this.sessionStorage.checkSessionStorage(this.inLocalStorage, this.canGoToNextRes, this.data);
                this.executeAction(realResSelected);
            }
        }
        this.loading = false;
    }

    indexDocument(): Promise<boolean> {
        this.data.resource['integrations'] = {
            inSignatureBook: true
        };

        return new Promise((resolve) => {
            this.http.post('../rest/resources', this.data.resource).pipe(
                tap((data: any) => {
                    this.data.resIds = [data.resId];
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    executeAction(realResSelected: number[]): void {
        this.http.put(this.data.processActionRoute, { resources: realResSelected, note: this.noteEditor.getNote() }).pipe(
            tap((data: any) => {
                if (!data) {
                    this.dialogRef.close(realResSelected);
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.actionService.stopRefreshResourceLock();
                const path: string = `resourcesList/users/${this.data.userId}/groups/${this.data.groupId}/baskets/${this.data.basketId}?limit=10&offset=0`;
                this.http.get(`../rest/${path}`).pipe(
                    tap((data: any) => {
                        if (!this.route.url.includes('signatureBook')) {
                            this.dialogRef.close(data.allResources[0]);
                        } else {
                            if (data.defaultAction?.component === 'signatureBookAction' && data.defaultAction?.data.goToNextDocument) {
                                if (data.count > 0) {
                                    this.dialogRef.close();
                                    this.route.navigate(['/signatureBook/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/resources/' + data.allResources[0]]);
                                } else {
                                    this.dialogRef.close();
                                    this.route.navigate([`/basketList/users/${this.data.userId}/groups/${this.data.groupId}/baskets/${this.data.basketId}`]);
                                    this.notify.handleSoftErrors(err);
                                }
                            } else {
                                this.dialogRef.close();
                                this.route.navigate([`/basketList/users/${this.data.userId}/groups/${this.data.groupId}/baskets/${this.data.basketId}`]);
                                this.notify.handleSoftErrors(err);
                            }
                        }
                    })
                ).subscribe();
                return of(false);
            })
        ).subscribe();
    }

    executeIndexingAction(resId: number): void {
        this.http.put(this.data.indexActionRoute, { resource: resId, note: this.noteEditor.getNote() }).pipe(
            tap((data: any) => {
                if (!data) {
                    this.dialogRef.close(this.data.resIds);
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    async initVisaWorkflow(): Promise<any> {
        if (this.data.resIds.length === 0) {
            // Indexing page
            if (!this.functions.empty(this.data.resource.destination) && !this.noResourceToProcess) {
                this.noResourceToProcess = false;
                await this.appVisaWorkflow.loadListModel(this.data.resource.destination);
                await this.loadVisaSignParameters();
            }
        } else if (this.data.resIds.length > 1) {
            // List page
            await this.checkSignatureBook();
        } else {
            // Process page
            await this.checkSignatureBook();
            if (!this.noResourceToProcess) {
                await this.appVisaWorkflow.loadWorkflow(this.data.resIds[0]);
                await this.loadWorkflowEntity();
            }
        }
        if (!this.noResourceToProcess) {
            this.checkWorkflowParameters(this.appVisaWorkflow.visaWorkflow.items);
        }
    }

    async loadWorkflowEntity(): Promise<any> {
        if (this.appVisaWorkflow !== undefined) {
            if (this.appVisaWorkflow.emptyWorkflow()) {
                await this.appVisaWorkflow.loadDefaultWorkflow(this.data.resIds[0]);
            }
        } else {
            // issue component undefined ??
            setTimeout(async () => {
                if (this.appVisaWorkflow?.emptyWorkflow()) {
                    await this.appVisaWorkflow.loadDefaultWorkflow(this.data.resIds[0]);
                }
            }, 100);
        }
    }

    checkSignatureBookInIndexingPage(): void {
        if (this.data.resource.encodedFile === null) {
            this.noResourceToProcess = true;
            this.resourcesError = [
                {
                    alt_identifier: this.translate.instant('lang.currentIndexingMail'),
                    reason: 'noDocumentToSend'
                }
            ];
        }
    }

    checkSignatureBook(): Promise<boolean> {
        this.resourcesError = [];
        return new Promise((resolve) => {
            this.http.post('../rest/resourcesList/users/' + this.data.userId +
                '/groups/' + this.data.groupId +
                '/baskets/' + this.data.basketId +
                '/actions/' + this.data.action.id +
                '/checkSignatureBook', { resources: this.data.resIds })
                .pipe(
                    tap((data: any) => {
                        if (!this.functions.empty(data.resourcesInformations.error)) {
                            this.resourcesError = data.resourcesInformations.error;
                        }
                        this.noResourceToProcess = this.data.resIds.length === this.resourcesError.length;
                        if (data.resourcesInformations.success) {
                            this.resourcesMailing = data.resourcesInformations.success.filter((element: any) => element.mailing);
                        }
                        this.minimumVisaRole = data.minimumVisaRole;
                        this.maximumSignRole = data.maximumSignRole;
                        this.lastOneMustBeSignatory = data.workflowEndBySignatory;
                        this.workflowSignatoryRole = data.workflowSignatoryRole;
                        this.lastOneMustBeSignatory = this.workflowSignatoryRole === 'mandatory_final';
                        this.lockVisaCircuit = data.lockVisaCircuit;
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notify.handleSoftErrors(err);
                        this.dialogRef.close();
                        resolve(false);
                        return of(false);
                    })
                ).subscribe();
        });
    }

    toggleIntegration(integrationId: string): void {
        this.loading = true;
        this.http.put('../rest/resourcesList/integrations', { resources: this.data.resIds, integrations: { [integrationId]: !this.data.resource.integrations[integrationId] } }).pipe(
            tap(async () => {
                this.data.resource.integrations[integrationId] = !this.data.resource.integrations[integrationId];
                await this.checkSignatureBook();
                setTimeout(async () => {
                    if (this.appVisaWorkflow?.emptyWorkflow()) {
                        await this.appVisaWorkflow.loadWorkflow(this.data.resIds[0]);
                    }
                    this.loadWorkflowEntity();
                    if (!this.noResourceToProcess) {
                        this.checkWorkflowParameters(this.appVisaWorkflow.visaWorkflow.items);
                    }
                }, 100);
                this.loading = false
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    async afterAttachmentToggle(): Promise<void> {
        await this.checkSignatureBook();
        this.loadWorkflowEntity();
        this.attachmentsList.setTaget(this.attachmentsList.currentIntegrationTarget);
    }

    isValidAction(): boolean {
        return !this.noResourceToProcess && this.appVisaWorkflow !== undefined && !this.appVisaWorkflow.emptyWorkflow() && !this.appVisaWorkflow.workflowEnd() && this.signNumberCorrect && this.visaNumberCorrect && this.atLeastOneSign && ((this.lastOneIsSign && this.lastOneMustBeSignatory) || !this.lastOneMustBeSignatory);
    }

    checkWorkflowParameters(items: any[]): void {
        let nbVisaRole = 0;
        let nbSignRole = 0;
        this.visaWorkflowClone = JSON.parse(JSON.stringify(items));
        items.forEach(item => {
            if (this.functions.empty(item.process_date)) {
                if (item.requested_signature) {
                    nbSignRole++;
                } else {
                    nbVisaRole++;
                }
            } else {
                if (item.signatory) {
                    nbSignRole++;
                } else {
                    nbVisaRole++;
                }
            }
        });

        if (['optional', 'mandatory_final'].indexOf(this.workflowSignatoryRole) > -1) {
            this.lastOneMustBeSignatory = this.workflowSignatoryRole === 'mandatory_final';
            this.atLeastOneSign = true;
        } else {
            this.atLeastOneSign = nbSignRole >= 1;
        }

        if (this.maximumSignRole !== 0 || this.minimumVisaRole !== 0) {
            this.visaNumberCorrect = this.minimumVisaRole === 0 || nbVisaRole >= this.minimumVisaRole;
            this.signNumberCorrect = this.maximumSignRole === 0 || nbSignRole <= this.maximumSignRole;
        }

        if (this.lastOneMustBeSignatory) {
            const lastItem = items[items.length - 1];
            this.lastOneIsSign = this.functions.empty(lastItem.process_date) ? lastItem.requested_signature : lastItem.signatory;
        }
    }

    async loadVisaSignParameters(): Promise<boolean> {
        return new Promise((resolve) => {
            this.http.get('../rest/parameters/minimumVisaRole').pipe(
                tap((data: any) => {
                    this.minimumVisaRole = data.parameter.param_value_int;
                }),
                exhaustMap(() => this.http.get('../rest/parameters/maximumSignRole')),
                tap((data: any) => {
                    this.maximumSignRole = data.parameter.param_value_int;
                    resolve(true);
                }),
                exhaustMap(() => this.http.get('../rest/parameters/workflowSignatoryRole')),
                tap((data: any) => {
                    if (!this.functions.empty(data.parameter)) {
                        this.workflowSignatoryRole = data.parameter.param_value_string;
                    }
                    resolve(true);
                }),
                finalize(() => this.checkWorkflowParameters(this.appVisaWorkflow.getWorkflow())),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    onSidenavStateChanged(): void {
        /*
         * Toggle mat-sidenav &
         * Emit an event indicating the current state of the sidenav (true for open, false for closed)
         * Used in the actions.service sendSignatureBookAction() function
        */
        this.snav2?.toggle();
        this.sidenavStateChanged.emit(this.snav2?.opened);
    }

    getIntegratedAttachmentsLength(): number {
        return this.attachmentsList?.attachmentsClone.filter((attachment: any) => attachment.inSignatureBook).length;
    }
}
