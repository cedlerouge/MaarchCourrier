import { Component, OnInit } from '@angular/core';

@Component({
    templateUrl: 'signature-book.component.html',
    styleUrls: ['signature-book.component.scss'],
})
export class SignatureBookComponent implements OnInit {

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

    constructor() {}

    ngOnInit(): void {}
}
