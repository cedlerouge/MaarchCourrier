import { Component, Input, OnInit} from '@angular/core';

@Component({
    selector: 'app-maarch-sb-tabs',
    templateUrl: 'maarch-sb-tabs.component.html',
    styleUrls: ['maarch-sb-tabs.component.scss'],
})
export class MaarchSbTabsComponent implements OnInit {

    @Input() docsToSign: string[] = [];

    constructor() {}

    ngOnInit(): void {} 

}
