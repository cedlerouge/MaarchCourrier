import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { catchError, of, tap } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';

@Injectable({
    providedIn: 'root'
})

export class FastParapheurService {

    autocompleteUsersRoute: string = '';

    constructor(
        private http: HttpClient,
        private notify: NotificationService
    ) { }

    getUserAvatar(externalId: number = null): Promise<any> {
        return new Promise((resolve) => {
            this.http.get('assets/fast.png', { responseType: 'blob' }).pipe(
                tap((response: any) => {
                    const reader = new FileReader();
                    reader.readAsDataURL(response);
                    reader.onloadend = () => {
                        resolve(reader.result as any);
                    };
                }),
                catchError(err => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getOtpConfig(): Promise<any> {
        const otpConfigUrl: string = ''; // To do : set get OTP config URL
        return new Promise((resolve) => {
            this.http.get(`../rest/${otpConfigUrl}`).pipe(
                tap((data: any) => {
                    resolve(data.otp.length ?? null);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }

    loadListModel() {
        /**
         * Load list model from Fast Parapheur API
         */
    }

    loadWorkflow() {
        /**
         * Load worfklow from Fast Parapheur API
         */
    }

    synchronizeSignatures(data: any) {
        /**
         * Synchronize signatures
         */
    }
}
