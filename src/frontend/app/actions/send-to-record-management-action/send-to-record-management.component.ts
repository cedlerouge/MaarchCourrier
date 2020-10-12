import { HttpClient } from '@angular/common/http';
import { Component, Inject, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { of } from 'rxjs';
import { catchError, finalize, tap } from 'rxjs/operators';

@Component({
    selector: 'app-send-to-record-management',
    templateUrl: 'send-to-record-management.component.html',
    styleUrls: ['send-to-record-management.component.scss']
})
export class SendToRecordManagementComponent implements OnInit {

    loading: boolean = false;
    entityArchiveRecipients = [
        {
            id: 1,
            label: 'Service 1'
        }
    ];
    archivalAgreements = [
        {
            id: 1,
            label: 'Accord 1'
        }
    ];

    descriptionLevels = [
        {
            id: 'Class',
            label: 'Classe'
        },
        {
            id: 'Collection',
            label: 'Collection'
        },
        {
            id: 'File',
            label: 'Dossier'
        },
        {
            id: 'Fonds',
            label: 'Fonds'
        },
        {
            id: 'Item',
            label: 'Pièce'
        },
        {
            id: 'RecordGrp',
            label: 'Groupe de documents'
        },
        {
            id: 'Series',
            label: 'Série organique'
        },
        {
            id: 'Subfonds',
            label: 'Sous-fonds'
        },
        {
            id: 'SubGrp',
            label: 'Sous-groupe de documents'
        },
        {
            id: 'Subseries',
            label: 'Sous-série organique'
        }
    ];

    actionFormGroup: FormGroup;
    archives: any[] = [];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        public dialogRef: MatDialogRef<SendToRecordManagementComponent>,
        private _formBuilder: FormBuilder,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public functions: FunctionsService
    ) {
        this.actionFormGroup = this._formBuilder.group({
            packageName: ['', Validators.required],
            slipId: [{value: '', disabled: true}, Validators.required],
            slipDate: [new Date(), Validators.required],
            archivalAgreement: [{value: '', disabled: false}, Validators.required],
            entityArchiveRecipient: [{value: '', disabled: false}, Validators.required],
            entityLabelTransferEntity: [{value: '', disabled: true}, Validators.required],
            producerTransferEntity: [{value: '', disabled: true}, Validators.required],
            senderArchiveEntity: [{value: '', disabled: true}, Validators.required],
            archiveId: [{value: '', disabled: true}, Validators.required],
            archiveDescriptionLevel: [{value: 'File', disabled: false}, Validators.required],
            doctype: [{value: '', disabled: true}, Validators.required],
            entityRetentionRule: [{value: '', disabled: true}, Validators.required],
            doctypeRetentionFinalDisposition: [{value: '', disabled: true}, Validators.required]
        });
    }

    ngOnInit(): void {
        this.getData();
    }

    getData() {
        this.http.post(`../rest/resourcesList/users/${this.data.userId}/groups/${this.data.groupId}/baskets/${this.data.basketId}/actions/${this.data.action.id}/checkSendToRecordManagement`, { resources: this.data.resIds }).pipe(
            tap((data: any) => {
                this.archives = data.archiveUnits;
                this.archives.forEach((element: any) => {
                    element.type = this.translate.instant('lang.' + element.type);
                });
                this.actionFormGroup = this._formBuilder.group({
                    packageName: ['', Validators.required],
                    slipId: [{value: data.data.slipInfo.slipId, disabled: true}, Validators.required],
                    slipDate: [new Date(), Validators.required],
                    archivalAgreement: [{value: '', disabled: false}, Validators.required],
                    entityArchiveRecipient: [{value: '', disabled: false}, Validators.required],
                    entityLabelTransferEntity: [{value: data.data.entity.label, disabled: true}, Validators.required],
                    producerTransferEntity: [{value: data.data.entity.producerService, disabled: true}, Validators.required],
                    senderArchiveEntity: [{value: data.data.entity.senderArchiveEntity, disabled: true}, Validators.required],
                    archiveId: [{value: data.data.slipInfo.archiveId, disabled: true}, Validators.required],
                    archiveDescriptionLevel: [{value: 'File', disabled: false}, Validators.required],
                    doctype: [{value: data.data.doctype.label, disabled: true}, Validators.required],
                    entityRetentionRule: [{value: data.data.doctype.retentionRule, disabled: true}, Validators.required],
                    doctypeRetentionFinalDisposition: [{value: data.data.doctype.retentionFinalDisposition, disabled: true}, Validators.required],
                });
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    onSubmit() {
        this.loading = true;

        if (this.data.resIds.length > 0) {
            this.executeAction();
        }
    }

    executeAction() {

        const realResSelected: number[] = this.data.resIds;

        this.http.put(this.data.processActionRoute, { resources: realResSelected, data: {} }).pipe(
            tap((data: any) => {
                if (!data) {
                    this.dialogRef.close('success');
                }
                if (data && data.errors != null) {
                    this.notify.error(data.errors);
                }
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    isValidAction() {
        return true;
    }

}