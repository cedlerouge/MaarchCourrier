import { Component, EventEmitter, OnInit, Output } from '@angular/core';

@Component({
    selector: 'app-maarch-sb-actions',
    templateUrl: 'signature-book-actions.component.html',
    styleUrls: ['signature-book-actions.component.scss'],
})
export class SignatureBookActionsComponent implements OnInit {

    @Output() openPanelSignatures = new EventEmitter<true>();

    constructor() {}

    ngOnInit(): void {}


    openSignaturesList() {
        this.openPanelSignatures.emit(true);
    }
}
