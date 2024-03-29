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
import { ResourcesListComponent } from './resourcesList/resources-list.component';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '@service/functions.service';
import { ResourcesList } from '@models/resources-list.model';

@Component({
    templateUrl: 'signature-book.component.html',
    styleUrls: ['signature-book.component.scss'],
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

    processedIdSubscription: Subscription;
    allResourcesIds: any[] = [];

    canGoToNext: boolean = false;
    canGoToPrevious: boolean = false;

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

        // Event after process action
        this.processedIdSubscription = this.actionService.catchAction().subscribe(() => {
            this.processAfterAction();
        });
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
                const resources: ResourcesList[] = await this.signatureBookService.getResourcesBasket(this.userId, this.groupId, this.basketId);
                this.allResourcesIds = resources.map((resource: ResourcesList) => resource.resId);
                const index: number = this.allResourcesIds.indexOf(parseInt(this.resId.toString(), 10));
                this.canGoToNext = !this.functions.empty(this.allResourcesIds[index + 1]);
                this.canGoToPrevious = !this.functions.empty(this.allResourcesIds[index - 1]);
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

    processAfterAction() {
        this.backToBasket();
    }

    backToBasket(): void {
        const path = '/basketList/users/' + this.userId + '/groups/' + this.groupId + '/baskets/' + this.basketId;
        this.router.navigate([path]);
    }

    ngOnDestroy(): void {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
        this.processedIdSubscription.unsubscribe();
        this.unlockResource();
    }

    async unlockResource(): Promise<void> {
        const path = '/basketList/users/' + this.userId + '/groups/' + this.groupId + '/baskets/' + this.basketId;
        this.actionService.stopRefreshResourceLock();
        await this.actionService.unlockResource(this.userId, this.groupId, this.basketId, [this.resId], path);
    }

    toggleResList(): void {
        this.loadResList = true;
        setTimeout(() => {
            this.drawerResList?.toggle();
            this.resourcesList?.scrollToSelectedResource();
        }, 0);
    }

    closeResListPanel(value: string) {
        this.loading = value === 'goToResource';
        this.drawerResList.close();
    }

    goToResource(event: string = 'next' || 'previous') {
        this.actionService.goToResource(this.allResourcesIds, this.userId, this.groupId, this.basketId).subscribe(((resourcesToProcess: number[]) => {
            const allResourcesUnlock: number[] = resourcesToProcess;
            const index: number = this.allResourcesIds.indexOf(parseInt(this.resId.toString(), 10));
            const nextLoop = (event === 'next') ? 1 : (event === 'previous') ? -1 : 1;
            let indexLoop: number = index;

            do {
                indexLoop = indexLoop + nextLoop;
                if ((indexLoop < 0) || (indexLoop === this.allResourcesIds.length)) {
                    indexLoop = -1;
                    break;
                }

            } while (!allResourcesUnlock.includes(this.allResourcesIds[indexLoop]));

            if (indexLoop === -1) {
                this.notify.error(this.translate.instant('lang.warnResourceLockedByUser'));
            } else {
                const path: string = '/signatureBook/users/' + this.userId + '/groups/' + this.groupId + '/baskets/' + this.basketId + '/resources/' + this.allResourcesIds[indexLoop];
                this.router.navigate([path]);
            }
        }));
    }
}
