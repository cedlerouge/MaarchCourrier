import { Component, Input, OnInit } from '@angular/core';

import { Attachment } from '@models/attachment.model';

@Component({
    selector: 'app-maarch-sb-tabs',
    templateUrl: 'signature-book-tabs.component.html',
    styleUrls: ['signature-book-tabs.component.scss'],
})
export class MaarchSbTabsComponent implements OnInit {

    @Input() documents: Attachment[];
    @Input() signable: boolean = false;

    selectedId: number = 0;

    constructor() {}

    ngOnInit(): void {}
}
