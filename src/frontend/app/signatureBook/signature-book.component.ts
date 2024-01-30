import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, of, tap } from 'rxjs';

@Component({
    templateUrl: 'signature-book.component.html',
    styleUrls: ['signature-book.component.scss'],
})
export class SignatureBookComponent implements OnInit {

    resId: number = 0;
    basketId: number;
    groupId: number;
    userId: number;

    selectedAttachment: number = 0;
    selectedDocToSign: number = 0;

    attachments: string[] = [];

    docsToSign: string[] = [
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
    ];

    constructor(
        public http: HttpClient,
        private route: ActivatedRoute,
        private router: Router,
        private notify: NotificationService
    ) {}

    async ngOnInit(): Promise<void> {
        const res:any = await this.initParams();
        console.log('oké');

        this.route.params.subscribe(params => {
            if (typeof params['resId'] !== 'undefined') {
                this.resId = params['resId'];
                this.http.get(`../rest/resources/${this.resId}/attachments`).pipe(
                    tap((data: any) => {
                        this.attachments = data.attachments
                        .filter((attachment: any) => attachment.inSignatureBook && attachment.status === 'A_TRA')
                        .map((attachment: any) => (
                            attachment.title
                        ));
                    }),
                    catchError((err: any) => {
                        this.notify.handleSoftErrors(err);
                        this.router.navigate(['/home']);
                        return of(false);
                    })
                ).subscribe();
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
}
