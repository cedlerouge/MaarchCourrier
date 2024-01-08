import { Component, OnInit, Inject, ViewChild, ViewContainerRef } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { FunctionsService } from '@service/functions.service';
import { VisaWorkflowComponent } from '../../visa/visa-workflow.component';
import { PluginManagerService } from '@service/plugin-manager.service';
import { AuthService } from '@service/auth.service';

@Component({
    templateUrl: 'continue-visa-circuit-action.component.html',
    styleUrls: ['continue-visa-circuit-action.component.scss'],
})
export class ContinueVisaCircuitActionComponent implements OnInit {

    @ViewChild('myPlugin', { read: ViewContainerRef, static: true }) myPlugin: ViewContainerRef;
    @ViewChild('noteEditor', { static: true }) noteEditor: NoteEditorComponent;
    @ViewChild('appVisaWorkflow', { static: false }) appVisaWorkflow: VisaWorkflowComponent;

    loading: boolean = false;

    resourcesMailing: any[] = [];
    resourcesWarnings: any[] = [];
    resourcesErrors: any[] = [];

    noResourceToProcess: boolean = null;
    componentInstance: any = null;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        public dialogRef: MatDialogRef<ContinueVisaCircuitActionComponent>,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public functions: FunctionsService,
        private pluginManagerService: PluginManagerService,
        private authService: AuthService,
    ) { }

    async ngOnInit(): Promise<void> {
        this.loading = true;
        await this.checkSignatureBook();
        this.loading = false;
        const data: any = {
            functions: this.functions,
            notification: this.notify,
            translate: this.translate,
            pluginUrl: this.authService.maarchUrl.replace(/\/$/, '') + '/plugins/maarch-plugins'
        };
        this.componentInstance = await this.pluginManagerService.initPlugin('maarch-plugins-fortify', this.myPlugin, data);
    }

    checkSignatureBook() {
        this.resourcesErrors = [];
        this.resourcesWarnings = [];

        return new Promise((resolve, reject) => {
            this.http.post('../rest/resourcesList/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/actions/' + this.data.action.id + '/checkContinueVisaCircuit', { resources: this.data.resIds })
                .subscribe((data: any) => {
                    if (!this.functions.empty(data.resourcesInformations.warning)) {
                        this.resourcesWarnings = data.resourcesInformations.warning;
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
                }, (err: any) => {
                    this.notify.handleSoftErrors(err);
                    this.dialogRef.close();
                });
        });
    }

    async onSubmit() {
        this.loading = true;
        if (this.componentInstance.maarchFortifyService.signatureMode === 'rgs_2stars') {
            this.componentInstance.open();
        } else {
            const realResSelected: number[] = this.data.resIds.filter((resId: any) => this.resourcesErrors.map(resErr => resErr.res_id).indexOf(resId) === -1);
            this.executeAction(realResSelected);
        }
        this.loading = false;
    }

    executeAction(realResSelected: number[]) {

        this.http.put(this.data.processActionRoute, { resources : realResSelected, note : this.noteEditor.getNote() }).pipe(
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
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isValidAction() {
        return !this.noResourceToProcess;
    }
}
