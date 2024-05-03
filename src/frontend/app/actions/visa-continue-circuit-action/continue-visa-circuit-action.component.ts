import { Component, OnInit, Inject, ViewChild, ViewContainerRef } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import {
    MAT_LEGACY_DIALOG_DATA as MAT_DIALOG_DATA,
    MatLegacyDialogRef as MatDialogRef,
} from '@angular/material/legacy-dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { tap, finalize, catchError } from 'rxjs/operators';
import { Subscription, of } from 'rxjs';
import { FunctionsService } from '@service/functions.service';
import { VisaWorkflowComponent } from '../../visa/visa-workflow.component';
import { PluginManagerService } from '@service/plugin-manager.service';
import { AuthService } from '@service/auth.service';
import { HeaderService } from '@service/header.service';
import { SignatureBookInterface } from '@appRoot/signatureBook/signature-book.service';
import { ContinueVisaCircuitDataToSendInterface, ContinueVisaCircuitObjectInterface } from "@models/actions.model";

@Component({
    templateUrl: 'continue-visa-circuit-action.component.html',
    styleUrls: ['continue-visa-circuit-action.component.scss'],
})
export class ContinueVisaCircuitActionComponent implements OnInit {
    @ViewChild('myPlugin', { read: ViewContainerRef, static: true }) myPlugin: ViewContainerRef;
    @ViewChild('noteEditor', { static: false }) noteEditor: NoteEditorComponent;
    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;
    @ViewChild('snav2', { static: false }) public snav2: MatSidenav;

    subscription: Subscription;

    loading: boolean = false;

    resourcesMailing: any[] = [];
    resourcesWarnings: any[] = [];
    resourcesErrors: any[] = [];

    noResourceToProcess: boolean = null;
    componentInstance: any = null;

    digitalCertificate: boolean = true;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public dialogRef: MatDialogRef<ContinueVisaCircuitActionComponent>,
        @Inject(MAT_DIALOG_DATA) public data: any,
        private notify: NotificationService,
        public functions: FunctionsService,
        private pluginManagerService: PluginManagerService,
        private authService: AuthService,
        private headerService: HeaderService
    ) {}

    async ngOnInit(): Promise<void> {
        this.loading = true;
        await this.checkSignatureBook();
        this.data.resource.signatureBookConfig.url = (this.data.resource.signatureBookConfig as SignatureBookInterface)?.url?.replace(/\/$/, '')
        this.loading = false;
        const data: any = {
            functions: this.functions,
            notification: this.notify,
            translate: this.translate,
            pluginUrl: this.authService.maarchUrl.replace(/\/$/, '') + '/plugins/maarch-plugins',
            additionalInfo: {
                resources: this.data.resource.docsToSign,
                sender: `${this.headerService.user.firstname} ${this.headerService.user.lastname}`,
                externalUserId: this.headerService.user.externalId,
                signatureBookConfig: (this.data.resource.signatureBookConfig as SignatureBookInterface)
            },
        };
        this.componentInstance = await this.pluginManagerService.initPlugin(
            'maarch-plugins-fortify',
            this.myPlugin,
            data
        );
    }

    checkSignatureBook() {
        this.resourcesErrors = [];
        this.resourcesWarnings = [];

        return new Promise((resolve) => {
            this.http
                .post(
                    '../rest/resourcesList/users/' +
                        this.data.userId +
                        '/groups/' +
                        this.data.groupId +
                        '/baskets/' +
                        this.data.basketId +
                        '/actions/' +
                        this.data.action.id +
                        '/checkContinueVisaCircuit',
                    { resources: this.data.resIds }
                )
                .subscribe(
                    (data: any) => {
                        if (!this.functions.empty(data.resourcesInformations.warning)) {
                            this.resourcesWarnings = (data.resourcesInformations.warning as any[]).filter((warning: any) => warning.reason !== 'userHasntSigned');
                        }

                        if (!this.functions.empty(data.resourcesInformations.error)) {
                            this.resourcesErrors = data.resourcesInformations.error;
                            this.noResourceToProcess = this.resourcesErrors.length === this.data.resIds.length;
                        }
                        if (data.resourcesInformations.success) {
                            data.resourcesInformations.success.forEach((value: any) => {
                                if (value.mailing) {
                                    this.resourcesMailing.push(value);
                                }
                            });
                        }
                        resolve(true);
                    },
                    (err: any) => {
                        this.notify.handleSoftErrors(err);
                        this.dialogRef.close();
                    }
                );
        });
    }

    async onSubmit() {
        this.loading = true;
        const realResSelected: number[] = this.data.resIds.filter(
            (resId: any) => this.resourcesErrors.map((resErr) => resErr.res_id).indexOf(resId) === -1
        );
        if ((this.data.resource.signatureBookConfig as SignatureBookInterface).isNewInternalParaph && this.digitalCertificate) {
            this.componentInstance
                .open()
                .pipe(
                    tap((data: any) => {
                        if (!this.functions.empty(data) && typeof data === 'object') {
                            this.executeAction(realResSelected, this.formatDataToSend(data));
                        }
                    }),
                    catchError((err: any) => {
                        this.notify.handleSoftErrors(err);
                        return of(false);
                    })
                )
                .subscribe();
        } else {
            this.executeAction(realResSelected);
        }
        this.loading = false;
    }

    executeAction(realResSelected: number[], objToSend: ContinueVisaCircuitObjectInterface = null) {
        const dataToSend : ContinueVisaCircuitDataToSendInterface = {
            resources : realResSelected,
            note: this.noteEditor.getNote(),
            data: objToSend
        };
        this.http
            .put(this.data.processActionRoute, dataToSend)
            .pipe(
                tap((data: any) => {
                    if (!data) {
                        this.dialogRef.close(realResSelected);
                    }
                    if (data && data.errors != null) {
                        this.notify.error(data.errors);
                    }
                }),
                finalize(() => (this.loading = false)),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            )
            .subscribe();
    }

    formatDataToSend(data: any[]): ContinueVisaCircuitObjectInterface {
        const formatedData: ContinueVisaCircuitObjectInterface = {} as ContinueVisaCircuitObjectInterface;

        for (const item of data) {
            if (!formatedData[item.resId]) {
                formatedData[item.resId] = [];
            }
            formatedData[item.resId].push(item);
        }
        return formatedData;
    }

    isValidAction() {
        return !this.noResourceToProcess;
    }
}
