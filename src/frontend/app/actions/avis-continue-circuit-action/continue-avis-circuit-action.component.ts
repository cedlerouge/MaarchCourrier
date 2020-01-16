import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';
import { FunctionsService } from '../../../service/functions.service';
import { AvisWorkflowComponent } from '../../avis/avis-workflow.component';

@Component({
    templateUrl: "continue-avis-circuit-action.component.html",
    styleUrls: ['continue-avis-circuit-action.component.scss'],
})
export class ContinueAvisCircuitActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    resourcesWarnings: any[] = [];
    resourcesErrors: any[] = [];

    noResourceToProcess: boolean = null;

    @ViewChild('noteEditor', { static: true }) noteEditor: NoteEditorComponent;
    @ViewChild('appAvisWorkflow', { static: false }) appAvisWorkflow: AvisWorkflowComponent;

    constructor(
        public http: HttpClient, 
        private notify: NotificationService, 
        public dialogRef: MatDialogRef<ContinueAvisCircuitActionComponent>, 
        @Inject(MAT_DIALOG_DATA) public data: any,
        public functions: FunctionsService) { }

    async ngOnInit(): Promise<void> {
        this.loading = true;
        await this.checkAvisCircuit();
        this.loading = false;
    }

    checkAvisCircuit() {
        this.resourcesErrors = [];
        this.resourcesWarnings = [];

        // TO DO : WAIT BACK
        return new Promise((resolve, reject) => {
            this.http.post('../../rest/resourcesList/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/actions/' + this.data.action.id + '/checkContinueVisaCircuit', { resources: this.data.resIds })
            .subscribe((data: any) => {
                if (!this.functions.empty(data.resourcesInformations.warning)) {
                    this.resourcesWarnings = data.resourcesInformations.warning;
                }

                if(!this.functions.empty(data.resourcesInformations.error)) {
                    this.resourcesErrors = data.resourcesInformations.error;
                    this.noResourceToProcess = this.resourcesErrors.length === this.data.resIds.length;
                }
                resolve(true);
            }, (err: any) => {
                this.notify.handleSoftErrors(err);
            });
        });
    }

    async onSubmit() {
        const realResSelected: number[] = this.data.resIds.filter((resId: any) => this.resourcesErrors.map(resErr => resErr.res_id).indexOf(resId) === -1);
        this.executeAction(realResSelected);
    }

    executeAction(realResSelected: number[]) {
        const noteContent: string = `[avis] ${this.noteEditor.getNoteContent()}`;
        this.http.put(this.data.processActionRoute, {resources : realResSelected, note : noteContent}).pipe(
            tap((data: any) => {
                if (!data) {
                    this.dialogRef.close('success');
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isValidAction() {
        if (!this.noResourceToProcess && !this.functions.empty(this.noteEditor.getNoteContent())) {
            return true;
        } else {
            return false; 
        }
    }
}