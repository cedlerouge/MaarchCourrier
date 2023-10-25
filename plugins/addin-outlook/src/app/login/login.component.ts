import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';
import { NotificationService } from '../service/notification/notification.service';
import { AuthService } from '../service/auth.service';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '../service/functions.service';
import { FormControl, FormGroup, UntypedFormGroup, Validators } from '@angular/forms';


@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

    loginForm: UntypedFormGroup;

    loading: boolean = true;

    @Output() success = new EventEmitter<boolean>();

    constructor(
        public http: HttpClient,
        private notificationService: NotificationService,
        public authService: AuthService,
        public translate: TranslateService,
        public functions: FunctionsService
    ) { }

    ngOnInit() {
        this.loginForm = new FormGroup({
            login: new FormControl('', Validators.required),
            password: new FormControl('', Validators.required),
        });
        this.loading = false;
    }

    onSubmit() {
        this.loading = true;

        let url = '../rest/authenticate';

        this.http.post(
            url,
            {
                'login': this.loginForm.get('login').value ,
                'password': this.loginForm.get('password').value,
            },
            {
                observe: 'response'
            }
        ).pipe(
            tap(async (data: any) => {
                this.authService.clearTokens();
                this.authService.saveTokens(data.headers.get('Token'), data.headers.get('Refresh-Token'));
                this.authService.updateUserInfo(data.headers.get('Token'));
                this.success.emit(true);
            }),
            catchError((err: any) => {
                if (err.error.errors === 'Authentication Failed') {
                    this.notificationService.error(this.translate.instant('lang.wrongLoginPassword'));
                } else {
                    this.notificationService.handleSoftErrors(err);
                }
                return of(false);
            }),
            finalize(() => this.loading = false)
        ).subscribe();
    }
}
