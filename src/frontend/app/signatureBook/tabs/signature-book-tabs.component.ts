import { Component, Input, OnInit } from '@angular/core';
import { ActionsService } from '@appRoot/actions/actions.service';

import { Attachment, AttachmentInterface } from '@models/attachment.model';
import { FunctionsService } from '@service/functions.service';
import { SignatureBookService } from "@appRoot/signatureBook/signature-book.service";

@Component({
    selector: 'app-maarch-sb-tabs',
    templateUrl: 'signature-book-tabs.component.html',
    styleUrls: ['signature-book-tabs.component.scss'],
})
export class MaarchSbTabsComponent implements OnInit {
    @Input() documents: Attachment[] = [];
    @Input() position: 'left' | 'right' = 'right';

    selectedId: number = 0;

    constructor(
        public functionsService: FunctionsService,
        private actionsService: ActionsService,
        public signatureBookService: SignatureBookService,
    ) {}

    ngOnInit(): void {
        if (this.documents.length > 0) {
            this.actionsService.emitActionWithData({
                id: 'attachmentSelected',
                data: {
                    attachment: this.documents[0],
                    position: this.position,
                    resIndex: 0
                },
            });
        }
    }

    selectDocument(i: number, attachment: AttachmentInterface): void {
        if (this.position == 'left') {
            this.signatureBookService.toolBarActive = false;
        }
        this.selectedId = i;
        this.actionsService.emitActionWithData({
            id: 'attachmentSelected',
            data: {
                attachment: attachment,
                position: this.position,
                resIndex: this.selectedId
            },
        });
    }
}
