import { HttpClient } from '@angular/common/http';
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Router } from '@angular/router';
import { ActionsService } from '@appRoot/actions/actions.service';
import { Action } from '@models/actions.model';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { Subscription, catchError, of, tap } from 'rxjs';
import { SignatureBookService } from '../signature-book.service';
import { UserStampInterface } from '@models/user-stamp.model';
import { Attachment } from "@models/attachment.model";

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
    @Output() docsToSignUpdated = new EventEmitter<Attachment[]>();

    subscription: Subscription;

    loading: boolean = true;

    leftActions: Action[] = [];
    rightActions: Action[] = [];

    constructor(
        public http: HttpClient,
        public functions: FunctionsService,
        public signatureBookService: SignatureBookService,
        private notify: NotificationService,
        private actionsService: ActionsService,
        private router: Router
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
        let resIds: number[] = [this.resId];
        resIds = resIds.concat(this.signatureBookService.selectedResources.map((resource: Attachment) => resource.resIdMaster));
        // Get docs to sign attached to the current resource by default if the selection is empty
        const docsToSign: Attachment[] = this.signatureBookService.selectedResourceCount === 0 ? this.signatureBookService.docsToSign : this.signatureBookService.getAllDocsToSign();
        this.http
            .get(`../rest/resources/${this.resId}?light=true`)
            .pipe(
                tap((data: any) => {
                    this.actionsService.launchAction(
                        action,
                        this.userId,
                        this.groupId,
                        this.basketId,
                        [... new Set(resIds)],
                        { ...data, docsToSign: [... new Set(docsToSign)] },
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
