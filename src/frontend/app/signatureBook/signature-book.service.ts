import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { Attachment } from "@models/attachment.model";
import { ResourcesList } from "@models/resources-list.model";
import { FiltersListService } from "@service/filtersList.service";
import { HeaderService } from "@service/header.service";
import { NotificationService } from "@service/notification/notification.service";
import { catchError, map, of, tap } from "rxjs";
import { mapAttachment } from "./signature-book.utils";
import { SignatureBookConfig, SignatureBookConfigInterface } from "@models/signature-book.model";
import { SelectedAttachment } from "@models/signature-book.model";
import { DatePipe } from "@angular/common";

@Injectable({
    providedIn: 'root'
})
export class SignatureBookService {

    toolBarActive: boolean = false;
    resourcesListIds: number[] = [];
    docsToSign: Attachment[] = [];
    basketLabel: string = '';
    config = new SignatureBookConfig();

    selectedAttachment: SelectedAttachment = new SelectedAttachment();

    selectedDocToSign: SelectedAttachment = new SelectedAttachment();

    constructor(
        private http: HttpClient,
        private notifications: NotificationService,
        private filtersListService: FiltersListService,
        private headerService: HeaderService,
        private datePipe: DatePipe
    ) {}

    getInternalSignatureBookConfig(): Promise<SignatureBookConfigInterface | null> {
        return new Promise((resolve) => {
            this.http.get('../rest/signatureBook/config').pipe(
                tap((config: SignatureBookConfigInterface) => {
                    this.config = new SignatureBookConfig(config);
                    resolve(config);
                }),
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        })
    }

    initDocuments(userId: number, groupId: number, basketId:number, resId: number): Promise<{ resourcesToSign: Attachment[], resourcesAttached: Attachment[] } | null> {
        return new Promise((resolve) => {
            this.http.get(`../rest/signatureBook/users/${userId}/groups/${groupId}/baskets/${basketId}/resources/${resId}`).pipe(
                map((data: any) => {
                    // Mapping resources to sign
                    const resourcesToSign: Attachment[] = data?.resourcesToSign?.map((resource: any) => mapAttachment(resource)) ?? [];

                    // Mapping resources attached as annex
                    const resourcesAttached: Attachment[] = data?.resourcesAttached?.map((attachment: any) => mapAttachment(attachment)) ?? [];

                    return { resourcesToSign: resourcesToSign, resourcesAttached: resourcesAttached };
                }),
                tap((data: { resourcesToSign: Attachment[], resourcesAttached: Attachment[] }) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notifications.handleErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getResourcesBasket(userId: number, groupId: number, basketId: number, limit: number,  page: number): Promise<ResourcesList[] | []> {
        return new Promise((resolve) => {
            const offset = page * limit;
            const filters: string = this.filtersListService.getUrlFilters();

            this.http.get(`../rest/resourcesList/users/${userId}/groups/${groupId}/baskets/${basketId}?limit=${limit}&offset=${offset}${filters}`).pipe(
                map((data: any) => {
                    this.resourcesListIds = data.allResources;
                    this.basketLabel = data.basketLabel;
                    const resourcesList: ResourcesList[] = data.resources.map((resource: any) => new ResourcesList({
                        resId: resource.resId,
                        subject: resource.subject,
                        chrono: resource.chrono,
                        statusImage: resource.statusImage,
                        statusLabel: resource.statusLabel,
                        priorityColor: resource.priorityColor,
                        mailTracking: resource.mailTracking,
                        creationDate: resource.creationDate,
                        processLimitDate: resource.processLimitDate,
                        isLocked: resource.isLocked,
                        locker: resource.locker
                    }));
                    return resourcesList;
                }),
                tap((data: any) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    resolve([]);
                    return of(false);
                })
            ).subscribe();
        });
    }

    toggleMailTracking(resource: ResourcesList) {
        if (!resource.mailTracking) {
            this.followResources(resource);
        } else {
            this.unFollowResources(resource);
        }
    }

    followResources(resource: ResourcesList): void {
        this.http.post('../rest/resources/follow', { resources: [resource.resId] }).pipe(
            tap(() => {
                this.headerService.nbResourcesFollowed++;
                resource.mailTracking = !resource.mailTracking;
            }),
            catchError((err: any) => {
                this.notifications.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    unFollowResources(resource: ResourcesList): void {
        this.http.delete('../rest/resources/unfollow', { body: { resources: [resource.resId] } }).pipe(
            tap(() => {
                this.headerService.nbResourcesFollowed--;
                resource.mailTracking = !resource.mailTracking;
            }),
            catchError((err: any) => {
                this.notifications.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    downloadProof(resId: number): Promise<boolean> {
        return new Promise((resolve) => {
            this.http.get(`../rest/documents/${resId}/proof?mode=stream`, { responseType: 'blob' as 'json' })
                .pipe(
                    tap((data: any) => {
                        const today = new Date();
                        const filename = 'proof_' + resId + '_' + this.datePipe.transform(today, 'dd-MM-y') + '.' + data.type.replace('application/', '');
                        const downloadLink = document.createElement('a');
                        downloadLink.href = window.URL.createObjectURL(data);
                        downloadLink.setAttribute('download', filename);
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notifications.handleErrors(err);
                        resolve(false);
                        return of(false);
                    })
                ).subscribe();
        });
    }
}
