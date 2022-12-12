import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { catchError, of, tap } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { UserWorkflow } from '@models/user-workflow.model';
import { FunctionsService } from '@service/functions.service';

@Injectable({
    providedIn: 'root'
})

export class FastParapheurService {

    autocompleteUsersRoute: string = '/rest/autocomplete/fastParapheurUsers';
    canCreateUser: boolean = false;
    canSynchronizeSignatures: boolean = false;
    canManageSignaturesPositions: boolean = false;
    canViewWorkflow: boolean = false;
    userWorkflow = new UserWorkflow();

    constructor(
        private http: HttpClient,
        private notify: NotificationService,
        private translate: TranslateService,
        private functions: FunctionsService
    ) { }

    getUserAvatar(externalId: any = null): Promise<any> {
        return new Promise((resolve) => {
            this.http.get('assets/fast.png', { responseType: 'blob' }).pipe(
                tap((response: any) => {
                    const reader = new FileReader();
                    reader.readAsDataURL(response);
                    reader.onloadend = () => {
                        resolve(reader.result as any);
                    };
                }),
                catchError(err => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getOtpConfig(): Promise<any> {
        const otpConfigUrl: string = ''; // To do : set get OTP config URL
        return new Promise((resolve) => {
            this.http.get(`../rest/${otpConfigUrl}`).pipe(
                tap((data: any) => {
                    resolve(data.otp.length ?? null);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            );
        });
    }

    loadListModel(entityId: number) {
        return new Promise((resolve) => {
            this.http.get(`../rest/listTemplates/entities/${entityId}?type=visaCircuit&fastParapheur=true`).pipe(
                tap((data: any) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }

    loadWorkflow(resId: number, type: string) {
        return new Promise((resolve) => {
            this.http.get(`../rest/documents/${resId}/fastParapheurWorkflow?type=${type}`).pipe(
                tap((data: any) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }

    getAutocompleteDatas(data: any): Promise<any> {
        return new Promise((resolve) => {
            this.http.get(`..${this.autocompleteUsersRoute}`, { params: { 'search': data.user.mail, 'excludeAlreadyConnected': 'true' } })
                .pipe(
                    tap((result: any) => {
                        resolve(result);
                    }),
                    catchError((err: any) => {
                        this.notify.handleSoftErrors(err);
                        resolve(null);
                        return of(false);
                    })
                ).subscribe();
        });
    }

    linkAccountToSignatoryBook(externalId: any, serialId: number): Promise<any> {
        return new Promise((resolve) => {
            this.http.put(`../rest/users/${serialId}/linkToFastParapheur`, { fastParapheurUserEmail: externalId.email, fastParapheurUserName: externalId.idToDisplay }).pipe(
                tap(() => {
                    this.notify.success(this.translate.instant('lang.accountLinked'));
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(this.translate.instant('lang.' + err.error.lang));
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    unlinkSignatoryBookAccount(serialId: number): Promise<any> {
        return new Promise((resolve) => {
            this.http.put(`../rest/users/${serialId}/unlinkToFastParapheur`, {}).pipe(
                tap(() => {
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        });
    }

    createExternalSignatoryBookAccount(id: number, login: string, serialId: number) {
        // STAND BY: the creation of a user in FAST PARAPHEUR is not possible
    }

    checkInfoExternalSignatoryBookAccount(serialId: number): Promise<any> {
        return new Promise((resolve) => {
            this.http.get('../rest/users/' + serialId + '/statusInFastParapheur').pipe(
                tap((data: any) => {
                    resolve(data);
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();
        });
    }

    setExternalInformation(item: any): UserWorkflow {
        const label = item.labelToDisplay;
        delete item.labelToDisplay;
        const objeToSend: any = {
            ... item,
            id: item.email ?? item?.externalId?.fastParapheur ?? null,
            labelToDisplay: !this.functions.empty(item.externalId?.fastParapheur) ? `${label} (${item.externalId.fastParapheur})` : label,
            signatureModes: item.signatureModes ?? this.userWorkflow.signatureModes,
            role: item.role ?? this.userWorkflow.signatureModes[this.userWorkflow.signatureModes.length - 1],
            isValid: true,
            hasPrivilege: true,
        };
        if (item?.id !== undefined && item?.externalId?.fastParapheur === undefined) {
            objeToSend.externalId = null;
        } else {
            objeToSend.externalId = {
                fastParapheur: item.email ?? item.externalId.fastParapheur
            };
        }
        return objeToSend;
    }

    getRessources(additionalsInfos: any): any[] {
        return additionalsInfos.attachments.map((e: any) => e.res_id);
    }

    isValidParaph(additionalsInfos: any = null, workflow: any[] = [], resourcesToSign = [], userOtps = []) {
        return (additionalsInfos.attachments.length > 0 && workflow.length > 0) && userOtps.length === 0;
    }

    synchronizeSignatures(data: any) {
        /**
         * Synchronize signatures
         */
    }
}
