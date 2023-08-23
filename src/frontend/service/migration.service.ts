import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { interval, of, Subscription } from 'rxjs';
import { tap, catchError, map } from 'rxjs/operators';
import { NotificationService } from './notification/notification.service';
import { FunctionsService } from './functions.service';
import { AuthService } from './auth.service';
import { MatDialog } from '@angular/material/dialog';
import { AlertComponent } from '@plugins/modal/alert.component';
import { TranslateService } from '@ngx-translate/core';

@Injectable()
export class MigrationService {

    migrationLock: Subscription;
    isMigrating: boolean = false;
    isMigratingClone: any = null;

    constructor(
        private http: HttpClient,
        private notifications: NotificationService,
        private functions: FunctionsService,
        private authService: AuthService,
        private dialog: MatDialog,
        private translate: TranslateService,
    ) { }

    startMigrationCheck() {
        this.migrationLock = interval(10000).subscribe(() => {
            this.http.get('../rest/authenticationInformations').pipe(
                map((data: any) => data.isMigrating),
                tap((data: any) => {
                    this.isMigrating = data ?? false;
                    if (this.authService.canLogOut()) {
                        if (this.isMigrating && this.functions.empty(this.isMigratingClone)) {
                            this.logoutAndShowAlert();
                        } else if (!this.isMigrating && this.isMigratingClone) {
                            this.unsubscribeObservable();
                        }
                    }
                }),
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    this.dialog.closeAll();
                    return of(false);
                })
            ).subscribe();
        });
    }

    logoutAndShowAlert() {
        this.authService.logout();
        setTimeout(() => {
            this.showAlertComponent();
        }, 100);
    }

    showAlertComponent() {
        this.dialog.open(AlertComponent,
            {
                panelClass: 'maarch-modal',
                autoFocus: true,
                disableClose: true,
                data: {
                    title: this.translate.instant('lang.information'),
                    msg: this.translate.instant('lang.migrationProcessing'),
                    hideButton: true
                }
            });
        this.isMigratingClone = JSON.parse(JSON.stringify(this.isMigrating));
    }

    unsubscribeObservable() {
        // this.migrationLock.unsubscribe();
        this.dialog.closeAll();
        window.location.reload();
    }
}
