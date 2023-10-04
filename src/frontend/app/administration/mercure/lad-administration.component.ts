import { Component, OnInit, ViewChild, TemplateRef, ViewContainerRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';

import { AppService } from '@service/app.service';
import { FunctionsService } from '@service/functions.service';
import { MatDialog } from '@angular/material/dialog';
import { AdministrationService } from '../administration.service';

import { DocumentViewerComponent } from '../../viewer/document-viewer.component';

@Component({
    templateUrl: 'lad-administration.component.html',
    styleUrls: ['./lad-administration.component.scss']
})

export class LadAdministrationComponent implements OnInit {
    @ViewChild('adminMenuTemplate', { static: true }) adminMenuTemplate: TemplateRef<any>;
    @ViewChild('appDocumentViewer', { static: false }) appDocumentViewer: DocumentViewerComponent;

    loading: boolean = false;

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
