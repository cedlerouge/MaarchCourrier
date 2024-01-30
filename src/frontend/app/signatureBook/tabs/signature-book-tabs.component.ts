import { Component, Input, OnInit} from '@angular/core';

@Component({
    selector: 'app-maarch-sb-tabs',
    templateUrl: 'signature-book-tabs.component.html',
    styleUrls: ['signature-book-tabs.component.scss'],
})
export class MaarchSbTabsComponent implements OnInit {

    @Input() docsToSign: string[] = [];

    constructor() {}

    ngOnInit(): void {} 

}
