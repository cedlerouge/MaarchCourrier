import { Component, Input, OnInit } from '@angular/core';

import { Attachement } from '@models/attachement.model';

@Component({
    selector: 'app-maarch-sb-tabs',
    templateUrl: 'signature-book-tabs.component.html',
    styleUrls: ['signature-book-tabs.component.scss'],
})
export class MaarchSbTabsComponent implements OnInit {

    @Input() documents: Attachement[] | string[] = [];
    @Input() signable: boolean = false;

    selectedId: number;
    documentList: string[]=[];

    constructor() {}

    ngOnInit(): void {
        if ((Array.isArray(this.documents)) && (this.documents.length >0)) {
            if (this.documents[0] instanceof Attachement) {
                this.documentList = this.documents.map((document: any) => document.title);
            }
            else {
                this.documentList = JSON.parse(JSON.stringify(this.documents));
            }
        }
    }
}
