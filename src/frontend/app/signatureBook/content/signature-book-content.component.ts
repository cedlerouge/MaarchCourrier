import { HttpClient, HttpEvent, HttpEventType } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { ActionsService } from '@appRoot/actions/actions.service';
import { MessageActionInterface } from '@models/actions.model';
import { Attachment } from '@models/attachment.model';
import { FunctionsService } from '@service/functions.service';
import { NotificationService } from '@service/notification/notification.service';
import { Subscription, catchError, finalize, map, of, tap } from 'rxjs';

@Component({
    selector: 'app-maarch-sb-content',
    templateUrl: 'signature-book-content.component.html',
    styleUrls: ['signature-book-content.component.scss'],
})
export class MaarchSbContentComponent implements OnInit {

    subscription: Subscription;

    subscriptionDocument: Subscription;

    documentData = new Attachment();

    documentType: 'attachments' | 'resources';

    documentContent: SafeResourceUrl = null;

    loading: boolean = true;

    constructor(
        public functionsService: FunctionsService,
        private http: HttpClient,
        private sanitizer: DomSanitizer,
        private actionsService: ActionsService,
        private notificationService: NotificationService
    ) {
        this.subscription = this.actionsService.catchAction().pipe(
            tap((res: MessageActionInterface) => {
                if (res.id === 'selectedStamp') {
                    this.notificationService.success('apposition de la griffe!');
                } else if (res.id === 'attachmentToSign') {
                    this.subscriptionDocument?.unsubscribe();
                    this.documentData = res.data;
                    this.documentType = !this.functionsService.empty(this.documentData?.resIdMaster) ? 'attachments' : 'resources';
                    this.loading = true;
                    setTimeout(() => {
                        this.loadContent();
                    }, 1000);
                }
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
    }

    getLabel(): string {
        return !this.functionsService.empty(this.documentData?.chrono) ? `${this.documentData?.chrono}: ${this.documentData?.title}` : `${this.documentData?.title}`;
    }

    getTitle(): string {
        if (this.documentType === 'attachments') {
            return `${this.getLabel()} (${this.documentData.typeLabel})`
        } else if (this.documentType === 'resources') {
            return `${this.getLabel()}`;
        }
    }

    loadContent(): void {
        this.documentContent = null;
        return this.subscriptionDocument = this.requestWithLoader(`../rest/${this.documentType}/${this.documentData.resId}/content?mode=base64`).pipe(
            tap((data: any) => {
                if (data.encodedDocument) {
                    this.documentContent = this.sanitizer.bypassSecurityTrustResourceUrl(`data:${data.mimeType};base64,${data.encodedDocument}`);
                }
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notificationService.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    requestWithLoader(url: string): any {
        return this.http.get<any>(url, { reportProgress: true, observe: 'events' }).pipe(
            map((event: HttpEvent<any>) => {
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
