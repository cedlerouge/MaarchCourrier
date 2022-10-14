import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { FunctionsService } from '@service/functions.service';
import { catchError, of, tap } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';

@Injectable({
    providedIn: 'root'
})

export class MaarchParapheurService {

    autocompleteUsersRoute: string = '/rest/autocomplete/maarchParapheurUsers';

    constructor(
        public functions: FunctionsService,
        private http: HttpClient,
        private notify: NotificationService

    ) { }

    loadListModel(entityId: number): Promise<any> {
        return new Promise((resolve) => {
            this.http.get(`../rest/listTemplates/entities/${entityId}?type=visaCircuit&maarchParapheur=true`).pipe(
                tap((data: any) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }

    loadWorkflow(attachmentId: number, type: string): Promise<any> {
        return new Promise((resolve) => {
            this.http.get(`../rest/documents/${attachmentId}/maarchParapheurWorkflow?type=${type}`).pipe(
                tap((data: any) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getUserAvatar(externalId: number): Promise<any> {
        return new Promise((resolve) => {
            this.http.get(`../rest/maarchParapheur/user/${externalId}/picture`).pipe(
                tap((data: any) => {
                    resolve(data.picture);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getOtpConfig(): Promise<any> {
        return new Promise((resolve) => {
            this.http.get('../rest/maarchParapheurOtp').pipe(
                tap((data: any) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }
}
