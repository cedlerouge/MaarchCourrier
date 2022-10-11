import { Injectable, Injector } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { catchError, of, tap } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';
import { MaarchParapheurService } from './maarch-parapheur.service';
import { FastParapheurService } from './fast-parapheur.service';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '@service/auth.service';
import { FunctionsService } from '@service/functions.service';
@Injectable()

export class ExternalSignatoryBookManagerService {

    allowedSignatoryBook: string[] = ['maarchParapheur', 'fastParapheur'];
    serviceInjected: MaarchParapheurService | FastParapheurService;
    signatoryBookEnabled: string = '';
    integratedWorkflow: boolean = false; // allows when FAST PARAPHEUR is activated to know which method to use

    constructor(
        private injector: Injector,
        private http: HttpClient,
        private notifications: NotificationService,
        private translate: TranslateService,
        private authService: AuthService,
        private functions: FunctionsService
    ) {
        this.integratedWorkflow = this.authService.externalSignatoryBook.integratedWorkflow;
        if (this.allowedSignatoryBook.indexOf(this.authService.externalSignatoryBook.id) > -1) {
            if (this.authService.externalSignatoryBook.id === 'maarchParapheur') {
                this.signatoryBookEnabled = this.authService.externalSignatoryBook.id;
                this.serviceInjected = this.injector.get<MaarchParapheurService>(MaarchParapheurService);
            } else if (this.authService.externalSignatoryBook.id === 'fastParapheur' && this.integratedWorkflow) {
                this.signatoryBookEnabled = this.authService.externalSignatoryBook.id;
                this.serviceInjected = this.injector.get<FastParapheurService>(FastParapheurService);
            }
        } else if (this.functions.empty(this.authService.externalSignatoryBook.id)) {
            this.notifications.handleSoftErrors(this.translate.instant('lang.externalSignoryBookNotEnabled'));
        }
    }

    checkExternalSignatureBook(data: any): Promise<any> {
        return new Promise((resolve) => {
            this.http.post(`../rest/resourcesList/users/${data.userId}/groups/${data.groupId}/baskets/${data.basketId}/checkExternalSignatoryBook`, { resources: data.resIds }).pipe(
                tap((result: any) => {
                    resolve(result);
                }),
                catchError((err: any) => {
                    this.notifications.handleSoftErrors(err);
                    resolve(null);
                    return of(false);
                })
            ).subscribe();

        });
    }

    loadListModel(entityId: number) {
        return this.serviceInjected.loadListModel(entityId);
    }

    loadWorkflow(attachmentId: number, type: string) {
        return this.serviceInjected.loadWorkflow(attachmentId, type);
    }

    getUserAvatar(externalId: number) {
        return this.serviceInjected.getUserAvatar(externalId);
    }

    getOtpConfig() {
        return this.serviceInjected.getOtpConfig();
    }

    getAutocompleteUsersRoute(): string {
        return this.serviceInjected.autocompleteUsersRoute;
    }

    isValidExtWorkflow(workflow: any[]): boolean {
        let res: boolean = true;
        workflow.forEach((item: any, indexUserRgs: number) => {
            if (['visa', 'stamp'].indexOf(item.role) === -1) {
                if (workflow.filter((itemUserStamp: any, indexUserStamp: number) => indexUserStamp > indexUserRgs && itemUserStamp.role === 'stamp').length > 0) {
                    res = false;
                }
            } else {
                return true;
            }
        });
        return res;
    }

    getAutocompleteUsersDatas(data: any) {
        return this.serviceInjected.getAutocompleteDatas(data);
    }

    linkAccountToSignatoryBook(externalId: number, serialId: number) {
        return this.serviceInjected.linkAccountToSignatoryBook(externalId, serialId);
    }

    unlinkSignatoryBookAccount(serialId: number) {
        return this.serviceInjected.unlinkSignatoryBookAccount(serialId);
    }

    createExternalSignatoryBookAccount(id: number, login: string, serialId: number) {
        return this.serviceInjected.createExternalSignatoryBookAccount(id, login, serialId);
    }

    checkInfoExternalSignatoryBookAccount(serialId: number) {
        return this.serviceInjected.checkInfoExternalSignatoryBookAccount(serialId);
    }
}
