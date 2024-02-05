import { Component, OnInit, ViewChild } from '@angular/core';
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
export class SignatureBookComponent implements OnInit {

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
    ) {
        this.subscription = this.actionsService.catchActionWithData().pipe(
            filter((data: MessageActionInterface) => data.id === 'selectedStamp'),
            tap(() => {
                this.stampsPanel.close();
                this.notify.success('apposition de la griffe!');
            })
        ).subscribe();
    }

    async ngOnInit(): Promise<void> {
        await this.initParams();

        if (this.resId !== undefined) {
            this.initAttachments();
            this.initDocsToSign();
        } else {
            this.router.navigate(['/home']);
        }
    }

    initParams() {
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

    initAttachments() {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${this.resId}/attachments`).pipe(
                map((data: any) => data.attachments.filter((attachment: AttachmentInterface) => attachment.inSignatureBook && attachment.status === 'A_TRA')),
                tap((attachments: AttachmentInterface[]) => {
                    this.attachments = attachments;
                    this.loadingAttachments = false;
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    initDocsToSign() {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${this.resId}/attachments`).pipe(
                map((data: any) => data.attachments.filter((attachment: AttachmentInterface) => attachment.inSignatureBook && attachment.status === 'A_TRA')),
                tap((docsToSign: AttachmentInterface[]) => {
                    this.docsToSign = docsToSign;
                    this.loadingDocsToSign = false;
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
    }
}
