import { Component, Input, OnInit} from '@angular/core';

@Component({
    selector: 'app-maarch-sb-tabs',
    templateUrl: 'maarchSbTabs.component.html',
    styleUrls: ['maarchSbTabs.component.scss'],
})
export class MaarchSbTabsComponent implements OnInit {

    @Input() docsToSign: string[] = [];

    constructor() {}

    ngOnInit(): void {} 

}
