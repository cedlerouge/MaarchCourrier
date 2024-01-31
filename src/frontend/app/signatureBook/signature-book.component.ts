import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, of, tap } from 'rxjs';

import { Attachement } from '@models/attachement.model';

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

    selectedAttachment: number;
    selectedDocToSign: number = 0;

    attachments: Attachement[] = [];
    docsToSign: string[] = [];

    constructor(
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService
    ) {}

    async ngOnInit(): Promise<void> {
        await this.initParams();
        console.log('oké');
  
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
                tap((data: any) => {
                    console.log('data', data);
                    this.attachments = data.attachments.map(item => {
                        let attachement = new Attachement();
                        Object.keys(item).forEach(key => {
                            attachement[key] = item[key];
                        });
                        return attachement;
                    });

                    this.attachments = this.attachments
                    .filter((attachment) => attachment.inSignatureBook && attachment.status === 'A_TRA');

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
        this.docsToSign = [
            'Doc à signer',
            'Doc à signer',
            'Doc à signer',
            'Doc à signer',
            'Doc à signer',
            'Doc à signer',
        ];
        this.loadingDocsToSign = false;
    }
}
