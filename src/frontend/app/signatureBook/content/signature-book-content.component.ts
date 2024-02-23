import { HttpClient } from '@angular/common/http';
import { Component, OnDestroy, ViewChild, ViewContainerRef } from '@angular/core';
import { SafeResourceUrl } from '@angular/platform-browser';
import { ActionsService } from '@appRoot/actions/actions.service';
import { MessageActionInterface } from '@models/actions.model';
import { Attachment } from '@models/attachment.model';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '@service/functions.service';
import { HeaderService } from '@service/header.service';
import { NotificationService } from '@service/notification/notification.service';
import { PluginManagerService } from '@service/plugin-manager.service';
import { Subscription, catchError, finalize, of, tap } from 'rxjs';

@Component({
    selector: 'app-maarch-sb-content',
    templateUrl: 'signature-book-content.component.html',
    styleUrls: ['signature-book-content.component.scss'],
})
export class MaarchSbContentComponent implements OnDestroy {
    @ViewChild('myPlugin', { read: ViewContainerRef, static: true }) myPlugin: ViewContainerRef;

    subscription: Subscription;

    subscriptionDocument: Subscription;

    documentData: Attachment;

    documentType: 'attachments' | 'resources';

    documentContent: SafeResourceUrl = null;

    loading: boolean = false;

    pluginInstance: any = false;

    constructor(
        public functionsService: FunctionsService,
        private http: HttpClient,
        private actionsService: ActionsService,
        private notificationService: NotificationService,
        private pluginManagerService: PluginManagerService,
        private translateService: TranslateService,
        private headerService: HeaderService
    ) {
        this.subscription = this.actionsService
            .catchActionWithData()
            .pipe(
                tap(async (res: MessageActionInterface) => {
                    if (res.id === 'selectedStamp') {
                        if (this.pluginInstance) {
                            const signContent = await this.getSignatureContent(res.data.contentUrl);
                            this.pluginInstance.addStamp(signContent);
                        }
                    } else if (res.id === 'attachmentToSign') {
                        this.loading = true;
                        this.subscriptionDocument?.unsubscribe();
                        this.documentData = res.data;
                        this.documentType = !this.functionsService.empty(this.documentData?.resIdMaster)
                            ? 'attachments'
                            : 'resources';
                        setTimeout(async () => {
                            await this.loadContent();
                            this.initPlugin();
                        }, 1000);
                    }
                }),
                catchError((err: any) => {
                    this.notificationService.handleSoftErrors(err);
                    return of(false);
                })
            )
            .subscribe();
    }

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
    }

    async initPlugin() {
        const data: any = {
            file: {
                filename: this.documentData.title,
                content: this.documentContent,
            },
            email: this.headerService.user.mail,
            currentLang: this.translateService.instant('lang.language'),
        };
        this.pluginInstance = await this.pluginManagerService.initPlugin('maarch-plugins-pdftron', this.myPlugin, data);
    }

    getLabel(): string {
        return !this.functionsService.empty(this.documentData?.chrono)
            ? `${this.documentData?.chrono}: ${this.documentData?.title}`
            : `${this.documentData?.title}`;
    }

    getTitle(): string {
        if (this.documentType === 'attachments') {
            return `${this.getLabel()} (${this.documentData.typeLabel})`;
        } else if (this.documentType === 'resources') {
            return `${this.getLabel()}`;
        }
    }

    loadContent(): Promise<boolean> {
        this.documentContent = null;
        return new Promise((resolve) => {
            this.subscriptionDocument = this.http
                .get(`../rest/${this.documentType}/${this.documentData.resId}/content`, { responseType: 'blob' })
                .pipe(
                    tap((data: Blob) => {
                        this.documentContent = data;
                        const { resId, title } = this.documentData;
                        this.actionsService.emitActionWithData({
                            id: 'documentToCreate',
                            data: { resId, title, encodedDocument: data },
                        });
                    }),
                    finalize(() => {
                        this.loading = false;
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notificationService.handleSoftErrors(err);
                        return of(false);
                    })
                )
                .subscribe();
        });
    }

    getSignatureContent(contentUrl: string) {
        return new Promise((resolve) => {
            this.http
                .get(contentUrl, { responseType: 'blob' })
                .pipe(
                    tap(async (res: Blob) => {
                        resolve(await this.blobToBase64(res));
                    }),
                    catchError((err: any) => {
                        this.notificationService.handleSoftErrors(err.error.errors);
                        resolve(false);
                        return of(false);
                    })
                )
                .subscribe();
        });
    }

    blobToBase64(blob: Blob) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.readAsDataURL(blob);
        });
    }
}
