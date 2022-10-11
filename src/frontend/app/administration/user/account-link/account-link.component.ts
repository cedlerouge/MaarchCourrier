import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from '@service/notification/notification.service';
import { ExternalSignatoryBookGeneratorService } from '@service/externalSignatoryBook/external-signatory-book-generator.service';
import { FunctionsService } from '@service/functions.service';

@Component({
    templateUrl: 'account-link.component.html',
    styleUrls: ['account-link.component.scss'],
    providers: [ExternalSignatoryBookGeneratorService]
})
export class AccountLinkComponent implements OnInit {

    externalUser: any = {
        inMaarchParapheur: false,
        inFastParapheur: false,
        login: '',
        firstname: '',
        lastname: '',
        email: '',
        picture: ''
    };

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        public externalSignatoryBokkGenerator: ExternalSignatoryBookGeneratorService,
        public functions: FunctionsService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<AccountLinkComponent>,
        private notify: NotificationService
    ) {
    }

    async ngOnInit(): Promise<void> {
        const dataUsers: any = await this.externalSignatoryBokkGenerator.getAutocompleteUsersDatas(this.data);
        if (!this.functions.empty(dataUsers)) {
            if (dataUsers.length > 0) {
                this.externalUser = dataUsers[0];
                this.externalUser.inMaarchParapheur = true;
                this.externalUser.picture = await this.externalSignatoryBokkGenerator.getUserAvatar(this.externalUser.id);
            } else {
                this.externalUser.inMaarchParapheur = false;
                this.externalUser = this.data.user;
                this.externalUser.login = this.data.user.user_id;
                this.externalUser.email = this.data.user.mail;
            }
        }
    }

    async selectUser(user: any) {
        this.externalUser = user;
        this.externalUser.inMaarchParapheur = true;
        this.externalUser.picture = await this.externalSignatoryBokkGenerator.getUserAvatar(this.externalUser.id);
    }

    unlinkMaarchParapheurAccount() {
        this.externalUser.inMaarchParapheur = false;
        this.externalUser = this.data.user;
        this.externalUser.login = this.data.user.user_id;
        this.externalUser.email = this.data.user.mail;
    }

    getRouteDatas(): string[] {
        return [`${this.externalSignatoryBokkGenerator.getAutocompleteUsersRoute()}?exludeAlreadyConnected=true`];
    }
}
