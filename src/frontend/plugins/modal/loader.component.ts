import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { TranslateService } from '@ngx-translate/core';
import { HeaderService } from '@service/header.service';

@Component({
    templateUrl: 'loader.component.html',
    styleUrls: ['loader.component.scss']
})
export class LoaderComponent {

    constructor(
        public translate: TranslateService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<LoaderComponent>,
        public headerService: HeaderService
    ) {
        if (this.data.msg === null) {
            this.data.msg = '';
        }
    }
}
