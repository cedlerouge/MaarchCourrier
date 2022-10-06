import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { LatinisePipe } from 'ngx-pipes';
import { DatePipe } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { catchError, of, tap } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';

@Injectable({
    providedIn: 'root'
})

export class FastParapheurService {

    constructor(
        private http: HttpClient,
        private notify: NotificationService
    ) { }

    getUserAvatar() {
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
}
