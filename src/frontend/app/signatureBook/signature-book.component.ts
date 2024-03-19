import { Component, HostListener, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { ActionsService } from '@appRoot/actions/actions.service';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { NotificationService } from '@service/notification/notification.service';
import { filter, tap } from 'rxjs';
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
export class SignatureBookComponent implements OnInit, OnDestroy {

    @ViewChild('drawerStamps', { static: true }) stampsPanel: MatDrawer;

    loadingAttachments: boolean = true;
    loadingDocsToSign: boolean = true;

    resId: number = 0;
    basketId: number;
    groupId: number;
    userId: number;

    attachments: Attachment[] = [];
    docsToSign: Attachment[] = [];

    subscription: Subscription;
    defaultStamp: StampInterface;

    constructor(
        public http: HttpClient,
        public signatureBookService: SignatureBookService,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService,
        private actionsService: ActionsService,
        private actionService: ActionsService
    ) {
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

    async ngOnInit(): Promise<void> {
        await this.initParams();

        if (this.resId !== undefined) {
            this.actionService.lockResource(this.userId, this.groupId, this.basketId, [this.resId]);
            await this.initDocuments();
        } else {
            this.router.navigate(['/home']);
        }
    }

    initParams(): Promise<boolean> {
        return new Promise((resolve) => {
            this.route.params.subscribe(params => {
                this.resId = params['resId'];
                this.basketId = params['basketId'];
                this.groupId = params['groupId'];
                this.userId = params['userId'];
                resolve(true);
            });
        });
    }

    async initDocuments(): Promise<void>{
        await this.signatureBookService.initDocuments(this.userId, this.groupId, this.basketId, this.resId).then((data: any) => {
            this.docsToSign = data.resourcesToSign;
            this.attachments = data.resourcesAttached;
            this.loadingAttachments = false;
            this.loadingDocsToSign = false;
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

    // Helper function to map attachment data
    private mapAttachment(data: any): Attachment {
        return new Attachment({
            resId: data.resId,
            resIdMaster: data.resIdMaster === null ? null : data.resId,
            signedResId: data.signedResId,
            chrono: data.chrono,
            title: data.title,
            type: data.type,
            typeLabel: data.typeLabel,
            canConvert: data.isConverted,
            canDelete: data.canDelete,
            canUpdate: data.canModify
        });
    }
}
