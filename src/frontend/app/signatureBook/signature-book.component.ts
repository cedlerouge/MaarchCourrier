import { Component, HostListener, OnDestroy, ViewChild } from '@angular/core';
import { ActionsService } from '@appRoot/actions/actions.service';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, filter, map, of, tap } from 'rxjs';
import { Subscription } from 'rxjs';
import { MatDrawer } from '@angular/material/sidenav';
import { StampInterface } from '@models/signature-book.model';

import { Attachment } from '@models/attachment.model';
import { MessageActionInterface } from '@models/actions.model';
import { SignatureBookService } from './signature-book.service';

@Component({
    templateUrl: 'signature-book.component.html',
    styleUrls: ['signature-book.component.scss'],
})
export class SignatureBookComponent implements OnDestroy {

    @ViewChild('drawerStamps', { static: true }) stampsPanel: MatDrawer;
    @ViewChild('drawerResList', { static: true }) drawerResList: MatDrawer;

    loadingAttachments: boolean = true;
    loadingDocsToSign: boolean = true;
    loading: boolean = true;

    resId: number = 0;
    basketId: number;
    groupId: number;
    userId: number;

    attachments: Attachment[] = [];
    docsToSign: Attachment[] = [];

    subscription: Subscription;
    defaultStamp: StampInterface;

    allResources: any[] = [];

    constructor(
        public http: HttpClient,
        public signatureBookService: SignatureBookService,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService,
        private actionsService: ActionsService,
        private actionService: ActionsService
    ) {

        this.initParams();

        this.subscription = this.actionsService.catchActionWithData().pipe(
            filter((data: MessageActionInterface) => data.id === 'selectedStamp'),
            tap(() => {
                this.stampsPanel.close();
                this.notify.success('apposition de la griffe!');
            })
        ).subscribe();
    }

    @HostListener('window:unload', [ '$event' ])
    async unloadHandler(): Promise<void> {
        this.unlockResource();
    }

    initParams(): void {
        this.route.params.subscribe(async params => {
            this.resetValues();

            this.resId = params['resId'];
            this.basketId = params['basketId'];
            this.groupId = params['groupId'];
            this.userId = params['userId'];

            if (this.resId !== undefined) {
                this.actionService.lockResource(this.userId, this.groupId, this.basketId, [this.resId]);
                await this.signatureBookService.getResourcesBasket(this.userId, this.groupId, this.basketId);
                await this.initDocuments();
            } else {
                this.router.navigate(['/home']);
            }
        });
    }

    resetValues(): void {
        this.loading = true;
        this.loadingDocsToSign = true;
        this.loadingAttachments = true;

        this.attachments = [];
        this.docsToSign = [];

        this.subscription?.unsubscribe();
        this.drawerResList?.close();
    }

    initDocuments(): Promise<boolean> {
        return new Promise((resolve) => {
            this.http.get(`../rest/signatureBook/users/${this.userId}/groups/${this.groupId}/baskets/${this.basketId}/resources/${this.resId}`).pipe(
                map((data: any) => {
                    const attachments = data.attachments.map((attachment: any) => new Attachment({
                        resId: attachment.res_id,
                        resIdMaster: attachment?.isResource ? null : attachment.res_id,
                        canConvert: attachment.isConverted,
                        canDelete: attachment.canDelete,
                        canUpdate: attachment.canModify,
                        chrono: attachment.alt_identifier ?? attachment.identifier ?? null,
                        creationDate: attachment.creation_date ?? null,
                        title: attachment.title,
                        typeLabel: attachment.attachment_type,
                        sign: attachment.sign ?? false
                    }));
                    return attachments;
                }),
                tap((attachments: Attachment[]) => {
                    // Filter attachments based on the "sign" property, which is set to True and mapped to the "docsToSign" array
                    this.docsToSign = attachments.filter((attachment) => attachment.sign);

                    // Filter attachments based on the "sign" property, which is set to False and mapped to the "attachments" array
                    this.attachments = attachments.filter((attachment) => !attachment.sign);

                    this.loadingAttachments = false;
                    this.loadingDocsToSign = false;
                    this.loading = false;

                    resolve(true);
                }),

                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    this.loading = false;
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    backToBasket(): void {
        const path = '/basketList/users/' + this.userId + '/groups/' + this.groupId + '/baskets/' + this.basketId;
        this.router.navigate([path]);
    }

    ngOnDestroy(): void {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
        this.unlockResource();
    }

    async unlockResource(): Promise<void> {
        this.actionService.stopRefreshResourceLock();
        await this.actionService.unlockResource(this.userId, this.groupId, this.basketId, [this.resId]);
    }
}
