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
    async initCheck(): Promise<void> {
        await this.getMigrationStatus();
        setTimeout(() => {
            this.startMigrationCheck();
        }, 100);
    }

    startMigrationCheck(): any {
        // Subscribe to an interval that triggers every 10000 milliseconds (10 seconds)
        this.migrationLock = interval(10000).subscribe(() => {
            this.getMigrationStatus();
        });
    }

    getMigrationStatus(): any {
        return new Promise((resolve) => {
            this.http.get('../rest/authenticationInformations').pipe(
                // Map the received data to extract the 'isMigrating' property
                map((data: any) => data.isMigrating),
                tap((data: any) => {
                    // Update the 'isMigrating' property based on received data, default to false
                    this.isMigrating = data ?? false;
                    // Check if the user can log out and handle migration state changes
                    if (this.authService.canLogOut()) {
                        if (this.isMigrating && this.functions.empty(this.isMigratingClone)) {
                            // If migrating and there's no previous clone state, trigger logout and alert
                            this.logoutAndShowAlert();
                        } else if (!this.isMigrating && this.isMigratingClone) {
                            // If not migrating but there was a previous clone state, unsubscribe
                            this.unsubscribeObservable();
                        }
                    }
                    resolve(true);
                }),
                // Handle errors and display notifications
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    this.dialog.closeAll();
                    resolve(true);
                    return of(false);
                })
            ).subscribe();
        });
    }

    logoutAndShowAlert(): any {
        this.authService.logout();
        setTimeout(() => {
            this.showAlertComponent();
        }, 100);
    }

    showAlertComponent(): any {
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

    unsubscribeObservable(): any {
        // this.migrationLock.unsubscribe();
        this.dialog.closeAll();
        window.location.reload();
    }
}
