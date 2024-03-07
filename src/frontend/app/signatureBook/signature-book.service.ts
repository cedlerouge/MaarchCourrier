import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { NotificationService } from "@service/notification/notification.service";
import { catchError, of, tap } from "rxjs";

@Injectable({
    providedIn: 'root',
})

export class SignatureBookService {
    config = new SignatureBookConfig();

    constructor(
        private http: HttpClient,
        private notifications: NotificationService

    ) {}

    getInternalSignatureBookConfig(): Promise<SignatureBookInterface | null> {
        return new Promise((resolve) => {
            this.http.get('../rest/signatureBook/config').pipe(
                tap((data: SignatureBookInterface) => {
                    this.config = data;
                    resolve(this.config);
                }),
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        })
    }
}

// InternalSignatureBook class implementing interface

export interface SignatureBookInterface {
    isNewInternalParaph: boolean;
    url: string;
}

export class SignatureBookConfig implements SignatureBookInterface {
    isNewInternalParaph: boolean = false;
    url: string = '';
}