import { HttpClient, HttpEventType } from '@angular/common/http';
import { Component, OnInit, Sanitizer } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { ActionsService } from '@appRoot/actions/actions.service';
import { MessageActionInterface } from '@models/actions.model';
import { Attachment, AttachmentInterface } from '@models/attachment.model';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { Subscription, catchError, filter, map, of, tap } from 'rxjs';
import { Observable } from 'tinymce';

@Component({
    selector: 'app-maarch-sb-content',
    templateUrl: 'maarch-sb-content.component.html',
    styleUrls: ['maarch-sb-content.component.scss'],
})
export class MaarchSbContentComponent implements OnInit {

    subscription: Subscription;
    attachmentSubscription: Subscription;

    attachmentData: AttachmentInterface;

    attachmentContent: any = null;

    loading: boolean = true;

    constructor(
        public functionsService: FunctionsService,
        private http: HttpClient,
        private sanitizer: DomSanitizer,
        private actionsService: ActionsService,
        private notificationService: NotificationService
    ) {
        this.subscription = this.actionsService.catchAction().pipe(
            filter((data: MessageActionInterface) => data.id === 'selectedStamp'),
            tap(() => {
                this.notificationService.success('apposition de la griffe!');
            }),
            catchError((err: any) => {
                this.notificationService.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();

        this.attachmentSubscription = this.actionsService.catchAction().pipe(
            filter((data: MessageActionInterface) => data.id === 'attachmentToSign'),
            tap((res: MessageActionInterface) => {
                this.attachmentData = res.data;
                this.loadContent();
            }),
            catchError((err: any) => {
                this.notificationService.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    ngOnInit(): void {}

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
        this.attachmentSubscription.unsubscribe();
    }

    setTitle(attachment: AttachmentInterface): string {
        return !this.functionsService.empty(attachment?.chrono) ? `${attachment?.chrono}: ${attachment?.title}` : `${attachment?.title}`;
    }

    loadContent(): void {
        this.loading = true;
        this.attachmentContent = '';
        const documentType: string = !this.functionsService.empty(this.attachmentData?.resIdMaster) ? 'attachment' : 'resource';
        if (documentType === 'attachment') {
            this.requestWithLoader(`../rest/attachments/${this.attachmentData.resId}/content?mode=base64`).pipe(
                tap((data: any) => {
                    if (data.encodedDocument) {
                        this.attachmentContent = this.sanitizer.bypassSecurityTrustResourceUrl(`data:${data.mimeType};base64,${data.encodedDocument}`);
                        this.loading = false;
                    }
                }),
                catchError((err: any) => {
                    this.notificationService.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    requestWithLoader(url: string) {
        return this.http.get<any>(url, { reportProgress: true, observe: 'events' }).pipe(
            map((event) => {
                switch (event.type) {
                    case HttpEventType.DownloadProgress:
                        const downloadProgress = Math.round(100 * event.loaded / event.total);
                        return { status: 'progressDownload', message: downloadProgress };
                    case HttpEventType.Response:
                        return event.body;
                    default:
                        return `Unhandled event: ${event.type}`;
                }
            })
        );
    }
}
