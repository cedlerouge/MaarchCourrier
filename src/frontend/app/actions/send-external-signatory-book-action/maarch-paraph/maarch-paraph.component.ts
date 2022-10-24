import { Component, OnInit, Input, ViewChild } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { SignaturePositionComponent } from './signature-position/signature-position.component';
import { catchError, filter, finalize, tap } from 'rxjs/operators';
import { of } from 'rxjs';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { MatDialog } from '@angular/material/dialog';
import { ExternalVisaWorkflowComponent } from '@appRoot/visa/externalVisaWorkflow/external-visa-workflow.component';
import { ExternalSignatoryBookManagerService } from '@service/externalSignatoryBook/external-signatory-book-manager.service';

@Component({
    selector: 'app-maarch-paraph',
    templateUrl: 'maarch-paraph.component.html',
    styleUrls: ['maarch-paraph.component.scss'],
})
export class MaarchParaphComponent implements OnInit {

    @ViewChild('appExternalVisaWorkflow', { static: true }) appExternalVisaWorkflow: ExternalVisaWorkflowComponent;

    @Input() resIds: number[] = [];
    @Input() resourcesToSign: any[] = [];
    @Input() additionalsInfos: any;
    @Input() externalSignatoryBookDatas: any;

    loading: boolean = false;

    currentAccount: any = null;
    usersWorkflowList: any[] = [];

    signaturePositions: any = {};

    injectDatasParam = {
        resId: 0,
        editable: true
    };

    constructor(
        public translate: TranslateService,
        private notify: NotificationService,
        public http: HttpClient,
        private functions: FunctionsService,
        public dialog: MatDialog,
        public externalSignatoryBookManagerService: ExternalSignatoryBookManagerService
    ) { }

    ngOnInit(): void {
        if (typeof this.additionalsInfos.destinationId !== 'undefined' && this.additionalsInfos.destinationId !== '') {
            setTimeout(() => {
                this.appExternalVisaWorkflow.loadListModel(this.additionalsInfos.destinationId);
            }, 0);
        }
    }

    isValidParaph(): boolean {
        return this.externalSignatoryBookManagerService.isValidParaph(this.additionalsInfos, this.appExternalVisaWorkflow.getWorkflow(), this.appExternalVisaWorkflow.getUserOtpsWorkflow(), this.resourcesToSign);
    }

    openSignaturePosition(resource: any) {
        const dialogRef = this.dialog.open(SignaturePositionComponent, {
            height: '99vh',
            panelClass: 'maarch-modal',
            disableClose: true,
            data: {
                resource: resource,
                workflow: this.appExternalVisaWorkflow.getWorkflow()
            }
        });
        dialogRef.afterClosed().pipe(
            filter((res: any) => !this.functions.empty(res)),
            tap((res: any) => {
                this.appExternalVisaWorkflow.setPositionsWorkfow(resource, res);
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    hasPositions(resource: any): boolean {
        return this.appExternalVisaWorkflow?.getDocumentsFromPositions().filter((document: any) => document.resId === resource.resId && document.mainDocument === resource.mainDocument).length > 0;
    }
}
