import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { FunctionsService } from '@service/functions.service';

@Component({
    selector: 'app-profile-contacts-groups',
    templateUrl: 'profile-contacts-groups.component.html',
    styleUrls: ['profile-contacts-groups.component.scss'],
})
export class ProfileContactsGroupsComponent {

    constructor(
        public translate: TranslateService,
        public functions: FunctionsService
    ) { }
}
