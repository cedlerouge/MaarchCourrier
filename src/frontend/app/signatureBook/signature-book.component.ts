import { Component, HostListener, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { ActionsService } from '@appRoot/actions/actions.service';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, filter, map, of, tap } from 'rxjs';
import { Subscription } from 'rxjs';
import { MatDrawer } from '@angular/material/sidenav';
import { StampInterface } from '@models/signature-book.model';

import { Attachment, AttachmentInterface } from '@models/attachment.model';
import { MessageActionInterface } from '@models/actions.model';

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

    initDocuments(): Promise<boolean> {
        return new Promise((resolve) => {
            this.http.get(`../rest/signatureBook/users/${this.userId}/groups/${this.groupId}/baskets/${this.basketId}/resources/${this.resId}`).pipe(
                map((data: any) => data.attachments),
                tap((attachments: any[]) => {
                    // Function to map attachment properties using AttachmentInterface attributes
                    const mapAttachment = (attachment: any) => ({
                        resId: attachment.res_id,
                        resIdMaster: attachment?.isResource ? null : attachment.res_id,
                        canConvert: attachment.isConverted,
                        canDelete: attachment.canDelete,
                        canUpdate: attachment.canModify,
                        chrono: attachment.alt_identifier ?? attachment.identifier ?? null,
                        creationDate: attachment.creation_date ?? null,
                        title: attachment.title,
                        typeLabel: attachment.attachment_type
                    }) as AttachmentInterface;

                    // Filter attachments based on the "sign" property, which is set to True and mapped to the "docsToSign" array
                    this.docsToSign = attachments.filter((attachment: any) => attachment.sign).map(mapAttachment);

                    // Filter attachments based on the "sign" property, which is set to False and mapped to the "attachments" array
                    this.attachments = attachments.filter((attachment: any) => !attachment.sign).map(mapAttachment);

                    this.loadingAttachments = false;
                    this.loadingDocsToSign = false;

                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
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
