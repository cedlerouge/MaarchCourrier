import { Component, EventEmitter, Input, OnInit, Output} from '@angular/core';

@Component({
    selector: 'app-maarch-sb-tabs',
    templateUrl: 'maarch-sb-tabs.component.html',
    styleUrls: ['maarch-sb-tabs.component.scss'],
})
export class MaarchSbTabsComponent implements OnInit {

    @Input() documents: string[] = [];
    @Input() signable: boolean = false;

    @Input() selectedId: number;
    @Output() selectedIdChange = new EventEmitter<number>();

    constructor() {}

    ngOnInit(): void {}

    selectDocument(id: number): void {
        this.selectedIdChange.emit(id);
    }
}
