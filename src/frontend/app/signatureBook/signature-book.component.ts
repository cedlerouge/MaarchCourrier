import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';

@Component({
    templateUrl: 'signature-book.component.html',
    styleUrls: ['signature-book.component.scss'],
})
export class SignatureBookComponent implements OnInit {

    resId: number;
    basketId: number;
    groupId: number;
    userId: number;

    selectedAttachment: number = 0;
    selectedDocToSign: number = 0;

    attachments: string[] = [
        'Annexe',
        'Annexe',
        'Annexe'
    ];

    docsToSign: string[] = [
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
    ];

    constructor(
        private route: ActivatedRoute,
    ) {}

    async ngOnInit(): Promise<void> {
        await this.initParams();
        console.log('oké');
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
