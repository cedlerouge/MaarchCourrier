import { Component, OnInit, AfterViewInit, ViewChild, ViewContainerRef } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { MatDialog } from '@angular/material/dialog';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { AppService } from '@service/app.service';
import { FeatureTourService } from '@service/featureTour.service';
import { DomSanitizer } from '@angular/platform-browser';
import { FunctionsService } from '@service/functions.service';
import { catchError, of, tap } from 'rxjs';

@Component({
    templateUrl: 'home.component.html',
    styleUrls: ['home.component.scss']
})
export class HomeComponent implements OnInit, AfterViewInit {

    @ViewChild('remotePlugin2', { read: ViewContainerRef, static: true }) remotePlugin2: ViewContainerRef;

    loading: boolean = false;

    homeData: any;
    homeMessage: any;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public dialog: MatDialog,
        public appService: AppService,
        public functions: FunctionsService,
        private notify: NotificationService,
        private headerService: HeaderService,
        private featureTourService: FeatureTourService,
        private sanitizer: DomSanitizer
    ) { }

    async ngOnInit(): Promise<void> {
        this.headerService.setHeader(this.translate.instant('lang.home'));

        this.http.get('../rest/home').pipe(
            tap((data: any) => {
                this.homeData = data;
                const sanitizedHtml = this.functions.sanitizeHtml(data['homeMessage']);
                this.homeMessage = this.sanitizer.bypassSecurityTrustHtml(sanitizedHtml);
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    ngAfterViewInit(): void {
        if (!this.featureTourService.isComplete()) {
            this.featureTourService.init();
        }
    }
}
