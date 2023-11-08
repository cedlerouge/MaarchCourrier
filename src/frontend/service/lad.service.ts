import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { catchError, filter, of, tap } from 'rxjs';
import { BinaryFile } from '@models/binary-file.model';
import { NotificationService } from './notification/notification.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { LoaderComponent } from '@plugins/modal/loader.component';

@Injectable({
    providedIn: 'root'
})
export class LadService {

    dialogRef: MatDialogRef<LoaderComponent>;

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notificationService: NotificationService,
        public dialog: MatDialog,
    ) { }

    initLad() {
        this.dialogRef = this.dialog.open(LoaderComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { msg: `${this.translate.instant('lang.mercureLadProcessingDocument')}...` } });
    }

    endLad() {
        this.dialogRef.close();
    }

    launchLadProcess(file: BinaryFile) {

        return new Promise((resolve) => {
            if (file.format !== 'pdf') {
                resolve(false);
                console.warn(`Unsupported format for lad : ${file.format}`);
            }
            const content = file.base64src ?? file.content;
            const filename = (file.name === 'pdf') ? file.name : file.name + '.pdf';

            this.http.post('../rest/mercure/lad', { encodedResource: content, extension: 'pdf', filename: filename }).pipe(
                filter((data: any) => data.message === null || typeof data.message === 'undefined'),
                tap((data: any) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notificationService.handleSoftErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }
}
