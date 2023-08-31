import { Component, OnInit, ViewChild, TemplateRef, ViewContainerRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { AppService } from '@service/app.service';
import { FunctionsService } from '@service/functions.service';
import { of } from 'rxjs';
import { MatDialogRef, MatDialog } from '@angular/material/dialog';
import { AdministrationService } from '../administration.service';
import {catchError, debounceTime, exhaustMap, filter, finalize, map, tap} from 'rxjs/operators';
import {UntypedFormControl, Validators} from '@angular/forms';
import { DocumentViewerComponent } from '../../viewer/document-viewer.component';

@Component({
    templateUrl: 'lad-administration.component.html',
    styleUrls: ['./lad-administration.component.scss']
})

export class LadAdministrationComponent implements OnInit {
    @ViewChild('adminMenuTemplate', { static: true }) adminMenuTemplate: TemplateRef<any>;
    @ViewChild('appDocumentViewer', { static: false }) appDocumentViewer: DocumentViewerComponent;

    loading: boolean = false;
    dialogRef: MatDialogRef<any>;

    tmpFilename: string = '';

    config: any = {
        enabledLad: new UntypedFormControl(false),
        mws: {
            url: '',
            login: '',
            password: '',
            tokenMws: '',
            loginMaarch: '',
            passwordMaarch: ''
        }
    };

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        private dialog: MatDialog,
        public functions: FunctionsService,
        public adminService: AdministrationService,
        private viewContainerRef: ViewContainerRef
    ) { }

    ngOnInit(): void {
        this.headerService.setHeader(this.translate.instant('lang.administration') + ' ' + this.translate.instant('lang.lad'));
        this.headerService.injectInSideBarLeft(this.adminMenuTemplate, this.viewContainerRef, 'adminMenu');
    }


}
