import { HttpClient } from '@angular/common/http';
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Router } from '@angular/router';
import { ActionsService } from '@appRoot/actions/actions.service';
import { Action, MessageActionInterface } from '@models/actions.model';
import { StampInterface } from '@models/signature-book.model';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { Subscription, catchError, of, tap } from 'rxjs';
import { SignatureBookConfig, SignatureBookService } from '../signature-book.service';
import { Attachment } from '@models/attachment.model';

@Component({
    selector: 'app-maarch-sb-actions',
    templateUrl: 'signature-book-actions.component.html',
    styleUrls: ['signature-book-actions.component.scss'],
})
export class SignatureBookActionsComponent implements OnInit {
    @Input() resId: number;
    @Input() basketId: number;
    @Input() groupId: number;
    @Input() userId: number;
    @Input() stamp: StampInterface;
    @Input() docsToSign: Attachment[] = [];

    @Output() openPanelSignatures = new EventEmitter<true>();
    @Output() docsToSignedUpdated = new EventEmitter<Attachment[]>();

    subscription: Subscription;

    documentDatas: { resId: number; title: string; encodedDocument: Blob; signatures: any[]; } = {
        resId: null,
        title: '',
        encodedDocument: null,
        signatures: []
    };

    loading: boolean = true;

    leftActions: Action[] = [];
    rightActions: Action[] = [];

    signatureBookConfig = new SignatureBookConfig();

    constructor(
        public http: HttpClient,
        public functions: FunctionsService,
        private notify: NotificationService,
        private actionsService: ActionsService,
        private router: Router,
        private signatureBookService: SignatureBookService
    ) {
        this.subscription = this.actionsService
            .catchActionWithData()
            .pipe(
                tap((res: MessageActionInterface) => {
                    if (res.id === 'documentToCreate') {
                        this.documentDatas = { ...this.documentDatas, ...res.data };
                        if (this.docsToSign.find((resource: Attachment) => resource.resId === res.data.resId) !== undefined && !this.functions.empty(res.data.signatures)) {
                            this.docsToSign.find((resource: Attachment) => resource.resId === res.data.resId).stamps = res.data.signatures;
                        }
                        if (res.data.encodedDocument) {
                            this.functions.blobToBase64(res.data.encodedDocument).then((value: any) => {
                                this.documentDatas.encodedDocument = value.split(',')[1];
                            });
                        }
                    }
                })
            )
            .subscribe();
    }

    async ngOnInit(): Promise<void> {
        await this.loadActions();
        this.loading = false;
    }

    openSignaturesList() {
        this.openPanelSignatures.emit(true);
    }

    loadActions() {
        return new Promise((resolve) => {
            this.actionsService
                .getActions(this.userId, this.groupId, this.basketId, this.resId)
                .pipe(
                    tap((actions: Action[]) => {
                        this.leftActions = [actions[1]];
                        this.rightActions = actions.filter((action: Action, key: number) => key !== 1);
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notify.handleSoftErrors(err.error.errors);
                        return of(false);
                    })
                )
                .subscribe();
        });
    }

    async processAction(action: any) {
        this.signatureBookConfig = await this.signatureBookService.getInternalSignatureBookConfig();
        this.http
            .get(`../rest/resources/${this.resId}?light=true`)
            .pipe(
                tap((data: any) => {
                    this.actionsService.launchAction(
                        action,
                        this.userId,
                        this.groupId,
                        this.basketId,
                        [this.resId],
                        {
                            ...data,
                            documentToCreate: this.documentDatas,
                            signatureBookConfig: this.signatureBookConfig,
                            docsToSign: this.docsToSign
                        },
                        false
                    );
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            )
            .subscribe();
    }

    processAfterAction() {
        this.backToBasket();
    }

    backToBasket() {
        const path = '/basketList/users/' + this.userId + '/groups/' + this.groupId + '/baskets/' + this.basketId;
        this.router.navigate([path]);
    }

    signWithStamp(stamp: StampInterface) {
        this.actionsService.emitActionWithData({
            id: 'selectedStamp',
            data: stamp,
        });
    }
}
