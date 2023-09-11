import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, finalize, tap } from 'rxjs/operators';
import { TranslateService } from '@ngx-translate/core';
import { HeaderService } from '@service/header.service';
import { Router } from '@angular/router';
import { of } from 'rxjs';

@Component({
    templateUrl: 'forgotPassword.component.html',
    styleUrls: ['forgotPassword.component.scss'],
})
export class ForgotPasswordComponent implements OnInit {


    loadingForm: boolean = false;
    loading: boolean = false;
    newLogin: any = {
        login: ''
    };
    labelButton: string = this.translate.instant('lang.send');

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private router: Router,
        public notificationService: NotificationService,
        private headerService: HeaderService
    ) {
    }

    ngOnInit(): void {
        this.headerService.hideSideBar = true;
    }

    generateLink() {
        this.labelButton = this.translate.instant('lang.generation');
        this.loading = true;

        this.http.post('../rest/password', { 'login': this.newLogin.login })
            .pipe(
                tap((data: any) => {
                    this.loadingForm = true;
                    this.notificationService.success(this.translate.instant('lang.requestSentByEmail'));
                    this.router.navigate(['/login']);
                }),
                finalize(() => {
                    this.labelButton = this.translate.instant('lang.send');
                    this.loading = false;
                }),
                catchError((err: any) => {
                    this.notificationService.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
    }

    cancel() {
        this.router.navigate(['/login']);
    }
}
