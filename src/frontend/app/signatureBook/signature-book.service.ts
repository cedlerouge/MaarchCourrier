import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { Attachment } from "@models/attachment.model";
import { NotificationService } from "@service/notification/notification.service";
import { catchError, map, of, tap } from "rxjs";

@Injectable({
    providedIn: 'root',
})

export class SignatureBookService {
    config = new SignatureBookConfig();

    constructor(
        private http: HttpClient,
        private notifications: NotificationService

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

    initDocuments(userId: number, groupId: number, basketId:number, resId: number): Promise<{ resourcesToSign: Attachment[], resourcesAttached: Attachment[] } | null> {
        return new Promise((resolve) => {
            this.http.get(`../rest/signatureBook/users/${userId}/groups/${groupId}/baskets/${basketId}/resources/${resId}`).pipe(
                map((data: any) => {
                    // Mapping resources to sign
                    const resourcesToSign = data?.resourcesToSign?.map((resource: any) => this._mapAttachment(resource)) ?? [];

                    // Mapping resources attached as annex
                    const resourcesAttached = data?.resourcesAttached?.map((attachment: any) => this._mapAttachment(attachment)) ?? [];

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

    // Helper function to map attachment data
    private _mapAttachment(data: any): Attachment {
        return new Attachment({
            resId: data.resId,
            resIdMaster: data.resIdMaster === null ? null : data.resId,
            signedResId: data.signedResId,
            chrono: data.chrono,
            title: data.title,
            type: data.type,
            typeLabel: data.typeLabel,
            canConvert: data.isConverted,
            canDelete: data.canDelete,
            canUpdate: data.canModify
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