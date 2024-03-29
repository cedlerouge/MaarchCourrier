import { Component, HostListener, OnDestroy, ViewChild } from '@angular/core';
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
import { ResourcesListComponent } from './resourcesList/resources-list.component';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '@service/functions.service';

@Component({
    templateUrl: 'signature-book.component.html',
    styleUrls: ['signature-book.component.scss'],
    providers: [SignatureBookService]
})
export class SignatureBookComponent implements OnDestroy {

    @ViewChild('drawerStamps', { static: true }) stampsPanel: MatDrawer;
    @ViewChild('drawerResList', { static: false }) drawerResList: MatDrawer;
    @ViewChild('resourcesList', { static: false }) resourcesList: ResourcesListComponent;

    loadingAttachments: boolean = true;
    loadingDocsToSign: boolean = true;
    loading: boolean = true;
    loadResList: boolean = false;

    resId: number = 0;
    basketId: number;
    groupId: number;
    userId: number;

    attachments: Attachment[] = [];
    docsToSign: Attachment[] = [];

    subscription: Subscription;
    defaultStamp: StampInterface;

    canGoToNext: boolean = false;
    canGoToPrevious: boolean = false;
    hidePanel: boolean = true;

    constructor(
        public http: HttpClient,
        public signatureBookService: SignatureBookService,
        public translate: TranslateService,
        public functions: FunctionsService,
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

            this.resId = parseInt(params['resId']);
            this.basketId = parseInt(params['basketId']);
            this.groupId = parseInt(params['groupId']);
            this.userId = parseInt(params['userId']);

            if (this.resId !== undefined) {
                this.actionService.lockResource(this.userId, this.groupId, this.basketId, [this.resId]);
                this.setNextPrev();
                await this.initDocuments();
            } else {
                this.router.navigate(['/home']);
            }
        });
    }

    setNextPrev() {
        const index: number = this.signatureBookService.resourcesListIds.indexOf(this.resId);
        this.canGoToNext = this.signatureBookService.resourcesListIds[index + 1] !== undefined;
        this.canGoToPrevious = this.signatureBookService.resourcesListIds[index - 1] !== undefined;
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

    async initDocuments(): Promise<void>{
        await this.signatureBookService.initDocuments(this.userId, this.groupId, this.basketId, this.resId).then((data: any) => {
            this.docsToSign = data.resourcesToSign;
            this.attachments = data.resourcesAttached;
            this.loadingAttachments = false;
            this.loadingDocsToSign = false;
            this.loading = false;
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

    toggleResList(): void {
        this.drawerResList?.toggle();
    }

    openResListPanel() {
        setTimeout(() => {
            this.drawerResList.open();
        }, 300);
    }

    showPanelContent() {
        this.resourcesList.initViewPort();
    }

    goToResource(event: string = 'next' || 'previous') {
        this.actionService.goToResource(this.signatureBookService.resourcesListIds, this.userId, this.groupId, this.basketId).subscribe(((resourcesToProcess: number[]) => {
            const allResourcesUnlock: number[] = resourcesToProcess;
            const index: number = this.signatureBookService.resourcesListIds.indexOf(parseInt(this.resId.toString(), 10));
            const nextLoop = (event === 'next') ? 1 : (event === 'previous') ? -1 : 1;
            let indexLoop: number = index;

            do {
                indexLoop = indexLoop + nextLoop;
                if ((indexLoop < 0) || (indexLoop === this.signatureBookService.resourcesListIds.length)) {
                    indexLoop = -1;
                    break;
                }

            } while (!allResourcesUnlock.includes(this.signatureBookService.resourcesListIds[indexLoop]));

            if (indexLoop === -1) {
                this.notify.error(this.translate.instant('lang.warnResourceLockedByUser'));
            } else {
                const path: string = '/signatureBook/users/' + this.userId + '/groups/' + this.groupId + '/baskets/' + this.basketId + '/resources/' + this.signatureBookService.resourcesListIds[indexLoop];
                this.router.navigate([path]);
                this.unlockResource();
                this.setNextPrev();
            }
        }));
    }
}
