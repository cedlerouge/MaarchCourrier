import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { catchError, of, tap } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';
import { TranslateService } from '@ngx-translate/core';
import { UserWorkflow } from '@models/user-workflow.model';

@Injectable({
    providedIn: 'root'
})

export class FastParapheurService {

    autocompleteUsersRoute: string = '/rest/autocomplete/fastParapheurUsers';

    userWorkflow = new UserWorkflow();

    constructor(
        private http: HttpClient,
        private notify: NotificationService,
        private translate: TranslateService
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

    loadListModel() {
        /**
         * Load list model from Fast Parapheur API
         */
    }

    loadWorkflow() {
        /**
         * Load worfklow from Fast Parapheur API
         */
    }

    getAutocompleteDatas() {
        /**
         * Get datas from autocomplete users url
         */
    }

    linkAccountToSignatoryBook(externalId: any, serialId: number): Promise<any> {
        return new Promise((resolve) => {
            this.http.put(`../rest/users/${serialId}/linkToFastParapheur`, { fastParapheurUserEmail: externalId.email }).pipe(
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
        return {
            ... item,
            signatureModes: this.userWorkflow.signatureModes,
            externalId: {
                fastParapheur: item.email
            }
        };
    }

    isValidParaph(additionalsInfos: any = null, workflow: any[] = [], userOtpWorkflow: any[] = [], resourcesToSign: any[] = []): boolean {
        return additionalsInfos.attachments.length > 0 && workflow.length > 0;
    }

    getRessources(additionalsInfos: any): any[] {
        return additionalsInfos.attachments.map((e: any) => e.res_id);
    }

    getDatas(externalSignatoryBookDatas: any = null, workflow: any[] = [], resourcesToSign: any[] = []): any {
        return externalSignatoryBookDatas;
    }
}
