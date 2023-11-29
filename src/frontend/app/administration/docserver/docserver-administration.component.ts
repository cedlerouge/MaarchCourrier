import { Component, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { HeaderService } from '@service/header.service';
import { AppService } from '@service/app.service';
import { FunctionsService } from '@service/functions.service';


@Component({
    templateUrl: 'docserver-administration.component.html'
})

export class DocserverAdministrationComponent implements OnInit {

    @ViewChild(MatPaginator, { static: false }) paginator: MatPaginator;
    @ViewChild(MatSort, { static: false }) sort: MatSort;

    loading: boolean = false;
    dataSource: any;

    docserver: any = { coll_id: 'letterbox_coll', docserver_type_id: 'DOC', limitSizeFormatted: '50', is_encrypted: false };
    docserversTypes: any = [];

    forbiddenDocserversTypesForEncrypted: string[] = ['MIGRATION', 'FULLTEXT'];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private router: Router,
        private notify: NotificationService,
        private headerService: HeaderService,
        public appService: AppService,
        private functions: FunctionsService
    ) { }

    async ngOnInit(): Promise<void> {
        this.headerService.setHeader(this.translate.instant('lang.docserverCreation'));

        this.loading = true;
        this.docserversTypes = await this.getDocserverTypes();
        this.loading = false;
    }

    async getDocserverTypes(forbiddenTypesById: string[] = []): Promise<any> {
        let types = await new Promise<any[]>((resolve) => {
            this.http.get('../rest/docserverTypes')
                .subscribe((data: any) => {
                    resolve(data.docserverTypes);
                });
        });

        if (!this.functions.empty(forbiddenTypesById)) {
            types = types.filter((v: any) => !forbiddenTypesById.includes(v.docserver_type_id));
        }

        return types;
    }

    checkForbiddenDocserversTypesForEncrypted(docserverTypeId: string) {
        if (this.forbiddenDocserversTypesForEncrypted.indexOf(docserverTypeId) >  -1) {
            this.docserver.is_encrypted = false;
        }
    }

    onSubmit(docserver: any) {
        docserver.size_limit_number = docserver.limitSizeFormatted * 1000000000;
        this.http.post('../rest/docservers', docserver)
            .subscribe((data: any) => {
                this.notify.success(this.translate.instant('lang.docserverAdded'));
                this.router.navigate(['/administration/docservers/']);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }
}
