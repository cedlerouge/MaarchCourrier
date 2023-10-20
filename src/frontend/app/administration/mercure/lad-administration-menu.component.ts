import { Component, OnInit, ViewContainerRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { AppService } from '@service/app.service';
import { FunctionsService } from '@service/functions.service';
import { of } from 'rxjs';
import { MatDialogRef, MatDialog } from '@angular/material/dialog';
import { AdministrationService } from '../administration.service';
import { catchError, tap } from 'rxjs/operators';
import { UntypedFormControl } from '@angular/forms';

@Component({
    selector: 'app-admin-menu-mercure',
    templateUrl: 'lad-administration-menu.component.html',
    styleUrls: ['./lad-administration-menu.component.scss']
})

export class LadAdministrationMenuComponent implements OnInit {


    loading: boolean = false;
    dialogRef: MatDialogRef<any>;

    config: any = {
        enabledLad: new UntypedFormControl(false),
        mws: {
            url: '',
            login: '',
            password: '',
            tokenMws: '',
            loginMaarch: '',
            passwordMaarch: ''
        },
        mwsLadPriority: new UntypedFormControl(false)
    };

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        public appService: AppService,
        public functions: FunctionsService,
        public adminService: AdministrationService
    ) { }

    ngOnInit(): void {
        // this.headerService.injectInSideBarLeft(this.adminMenuTemplate, this.viewContainerRef, 'adminMenu');
        this.initConfiguration();
    }

    toggleLadConf() {
        this.config.enabledLad = !this.config.enabledLad;
        this.saveConfiguration();
    }

    initConfiguration() {
        this.http.get('../rest/configurations/admin_mercure').pipe(
            tap((data: any) => {
                this.config = data.configuration.value;
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    saveConfiguration() {
        this.http.put('../rest/configurations/admin_mercure', this.config).pipe(
            tap(() => {
                if (!this.config.enabledLad) {
                    this.notify.success(this.translate.instant('lang.ladDisabled'));
                } else {
                    this.notify.success(this.translate.instant('lang.ladEnabled'));
                }
            }),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }


    setLadConf(activate: boolean){
        this.config.enabledLad = activate;
        this.saveConfiguration();
    }

    setConfig(conf: any){
        this.config = conf;
    }

    launchTest(){
        this.loading = true;
        this.http.post('../rest/administration/mercure/test', this.config).pipe(
            tap(() => {
                this.setLadConf(true);
                this.loading = false;
            }),
            catchError((err: any) => {
                this.loading = false;
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

}
