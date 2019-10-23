import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { NoteEditorComponent } from '../../notes/note-editor.component';
import { map, tap, finalize, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
    templateUrl: "send-external-note-book-action.component.html",
    styleUrls: ['send-external-note-book-action.component.scss'],
    providers: [NotificationService],
})
export class SendExternalNoteBookActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;
    additionalsInfos: any = {
        users: [],
        mails: [],
        noMail: []
    };

    externalSignatoryBookDatas: any = {
        processingUser: ''
    };
    errors: any;

    @ViewChild('noteEditor', { static: true }) noteEditor: NoteEditorComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<SendExternalNoteBookActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        this.loading = true;

        this.http.post('../../rest/resourcesList/users/' + this.data.userId + '/groups/' + this.data.groupId + '/baskets/' + this.data.basketId + '/checkExternalNoteBook', { resources: this.data.resIds }).pipe(
            map((data: any) => {
                data.additionalsInfos.users.forEach((element: any) => {
                    element.displayName = element.firstname + ' ' + element.lastname;
                });
                return data;
            }),
            tap((data) => {
                this.additionalsInfos = data.additionalsInfos;
                this.errors = data.errors;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    onSubmit() {
        this.loading = true;

        if ( this.data.resIds.length === 0) {
            // this.indexDocumentAndExecuteAction();
        } else {
            this.executeAction();
        }
    }

    /* indexDocumentAndExecuteAction() {
        
        this.http.post('../../rest/resources', this.data.resource).pipe(
            tap((data: any) => {
                this.data.resIds = [data.resId];
            }),
            exhaustMap(() => this.http.put(this.data.indexActionRoute, {resource : this.data.resIds[0], note : this.noteEditor.getNoteContent()})),
            tap(() => {
                this.dialogRef.close('success');
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe()
    } */

    executeAction() {
        let realResSelected: string[];
        let datas: any;

        realResSelected = this.additionalsInfos.mails.map((e: any) => { return e.res_id; });
        datas = this.externalSignatoryBookDatas;

        this.http.put(this.data.processActionRoute, {resources : realResSelected, note : this.noteEditor.getNoteContent(), data: datas}).pipe(
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

    checkValidAction() {
        if (this.additionalsInfos.mails.length == 0 || !this.externalSignatoryBookDatas.processingUser || this.additionalsInfos.users.length == 0) {
            return true;
        } else {
            return false;
        }
    }

    setVal(user: any) {
        this.externalSignatoryBookDatas.processingUser = user.id;
    }
}
