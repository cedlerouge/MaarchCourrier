import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { ListProperties } from "@models/list-properties.model";
import { FiltersListService } from "@service/filtersList.service";
import { NotificationService } from "@service/notification/notification.service";
import { catchError, of, tap } from "rxjs";

@Injectable({
    providedIn: 'root',
})

export class SignatureBookService {
    config = new SignatureBookConfig();

    constructor(
        private http: HttpClient,
        private notifications: NotificationService,
        private filtersListService: FiltersListService,
    ) {}

    getInternalSignatureBookConfig(): Promise<SignatureBookInterface | null> {
        return new Promise((resolve) => {
            this.http.get('../rest/signatureBook/config').pipe(
                tap((data: SignatureBookInterface) => {
                    this.config = data;
                    resolve(this.config);
                }),
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        })
    }

    getResourcesBasket(userId: number, groupId: number, basketId: number): Promise<number[] | null> {
        return new Promise((resolve) => {
            const listProperties: ListProperties = this.filtersListService.initListsProperties(userId, groupId, basketId, 'basket');
            const offset: number =  parseInt(listProperties.page) * listProperties.pageSize;
            const limit: number = listProperties.pageSize;
            const filters: string = this.filtersListService.getUrlFilters();

            this.http.get(`../rest/resourcesList/users/${userId}/groups/${groupId}/baskets/${basketId}?limit=${limit}&offset=${offset}${filters}`).pipe(
                tap((data: any) => {
                    resolve(data.allResources);
                }),
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
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