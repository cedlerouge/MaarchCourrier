import { Injectable, Injector } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { catchError, of, tap } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';
import { MaarchParapheurService } from './maarch-parapheur.service';
import { FastParapheurService } from './fast-parapheur.service';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from '@service/auth.service';
@Injectable()

export class ExternalSignatoryBookManagerService {

    allowedSignatoryBook: string[] = ['maarchParapheur', 'fastParapheur'];
    serviceInjected: MaarchParapheurService | FastParapheurService;
    signatoryBookEnabled: string = '';
    workflowMode: string = '';

    constructor(
        private injector: Injector,
        private http: HttpClient,
        private notifications: NotificationService,
        private translate: TranslateService,
        private authService: AuthService
    ) {
        this.workflowMode = this.authService.workflowMode;
        if (this.allowedSignatoryBook.indexOf(this.authService.enabledSignatureBook) > -1) {
            if (this.authService.enabledSignatureBook === 'maarchParapheur') {
                this.signatoryBookEnabled = this.authService.enabledSignatureBook;
                this.serviceInjected = this.injector.get<MaarchParapheurService>(MaarchParapheurService);
            } else if (this.authService.enabledSignatureBook === 'fastParapheur' && this.workflowMode === 'linkedAccounts') {
                this.signatoryBookEnabled = this.authService.enabledSignatureBook;
                this.serviceInjected = this.injector.get<FastParapheurService>(FastParapheurService);
            }
        } else {
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
}
