import { HttpClient } from '@angular/common/http';
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Router } from '@angular/router';
import { ActionsService } from '@appRoot/actions/actions.service';
import { Action } from '@models/actions.model';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { Subscription, catchError, of, tap } from 'rxjs';
import { SignatureBookConfig, SignatureBookService } from '../signature-book.service';
import { UserStampInterface } from '@models/user-stamp.model';

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
    @Input() userStamp: UserStampInterface;

    @Output() openPanelSignatures = new EventEmitter<true>();

    subscription: Subscription;

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
        /*this.subscription = this.actionsService
            .catchActionWithData()
            .pipe(
                tap((res: MessageActionInterface) => {
                    if (res.id === 'documentToCreate') {
                        const indexDocument = res.data.resIndex;
                        delete res.data.resIndex;
                        this.signatureBookService.docsToSign[indexDocument].stamps = res.data.stamps;
                    }
                })
            )
            .subscribe();*/
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
                        { ...data, docsToSign: this.signatureBookService.docsToSign, signatureBookConfig: this.signatureBookConfig },
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

    signWithStamp(userStamp: UserStampInterface) {
        this.actionsService.emitActionWithData({
            id: 'selectedStamp',
            data: userStamp,
        });
    }
}
