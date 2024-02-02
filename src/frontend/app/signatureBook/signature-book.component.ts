import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, map, of, tap } from 'rxjs';

import { Attachment, AttachmentInterface } from '@models/attachment.model';

@Component({
    templateUrl: 'signature-book.component.html',
    styleUrls: ['signature-book.component.scss'],
})
export class SignatureBookComponent implements OnInit {

    loadingAttachments: boolean = true;
    loadingDocsToSign: boolean = true;

    resId: number = 0;
    basketId: number;
    groupId: number;
    userId: number;

    attachments: Attachment[] = [];
    docsToSign: Attachment[] = [];

    constructor(
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService
    ) {}

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
                this.resId = +params['resId'];
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
}
