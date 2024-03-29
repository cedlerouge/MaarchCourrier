import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { ResourcesList } from "@models/resources-list.model";
import { FiltersListService } from "@service/filtersList.service";
import { HeaderService } from "@service/header.service";
import { NotificationService } from "@service/notification/notification.service";
import { catchError, map, of, tap } from "rxjs";

@Injectable()
export class SignatureBookService {

    resourcesListIds: number[] = [];
    basketLabel: string = '';

    constructor(
        private http: HttpClient,
        private notifications: NotificationService,
        private filtersListService: FiltersListService,
        private headerService: HeaderService
    ) {}

    getInternalSignatureBookConfig(): Promise<SignatureBookInterface | null> {
        return new Promise((resolve) => {
            this.http.get('../rest/signatureBook/config').pipe(
                tap((data: SignatureBookInterface) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        })
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
}

// InternalSignatureBook class implementing interface

export interface SignatureBookInterface {
    isNewInternalParaph: boolean;
    url: string;
}

export class SignatureBookConfig implements SignatureBookInterface {
    isNewInternalParaph: boolean = false;
    url: string = '';
}