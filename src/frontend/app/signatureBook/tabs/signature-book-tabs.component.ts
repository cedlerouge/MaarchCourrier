import { Component, Input, OnInit } from '@angular/core';
import { ActionsService } from '@appRoot/actions/actions.service';

import { Attachment, AttachmentInterface } from '@models/attachment.model';
import { FunctionsService } from '@service/functions.service';

@Component({
    selector: 'app-maarch-sb-tabs',
    templateUrl: 'signature-book-tabs.component.html',
    styleUrls: ['signature-book-tabs.component.scss'],
})
export class MaarchSbTabsComponent implements OnInit {

    @Input() documents: Attachment[];
    @Input() signable: boolean = false;

    selectedId: number = 0;

    constructor(
        public functionsService: FunctionsService,
        private actionsService: ActionsService
    ) {}

    ngOnInit(): void {
        this.actionsService.emitActionWithData({ id: 'attachmentToSign', data: this.documents[0] })
    }

    selectDocument(i: number, attachment: AttachmentInterface): void {
        this.selectedId = i;
        this.actionsService.emitActionWithData({
            id: 'attachmentToSign',
            data: attachment
        })
    }
}
