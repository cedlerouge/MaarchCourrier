import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';

import { AdministrationComponent } from './home/administration.component';
import { UsersAdministrationComponent } from './user/users-administration.component';
import { UserAdministrationComponent } from './user/user-administration.component';
import { GroupsAdministrationComponent } from './group/groups-administration.component';
import { GroupAdministrationComponent } from './group/group-administration.component';
import { BasketsAdministrationComponent } from './basket/baskets-administration.component';
import { BasketAdministrationComponent } from './basket/basket-administration.component';
import { DoctypesAdministrationComponent } from './doctype/doctypes-administration.component';
import { DiffusionModelsAdministrationComponent } from './diffusionModel/diffusionModels-administration.component';
import { DiffusionModelAdministrationComponent } from './diffusionModel/diffusionModel-administration.component';
import { EntitiesAdministrationComponent } from './entity/entities-administration.component';
import { StatusesAdministrationComponent } from './status/statuses-administration.component';
import { StatusAdministrationComponent } from './status/status-administration.component';
import { ActionsAdministrationComponent } from './action/actions-administration.component';
import { ActionAdministrationComponent } from './action/action-administration.component';
import { ParameterAdministrationComponent } from './parameter/parameter-administration.component';
import { ParametersAdministrationComponent } from './parameter/parameters-administration.component';
import { PrioritiesAdministrationComponent } from './priority/priorities-administration.component';
import { PriorityAdministrationComponent } from './priority/priority-administration.component';
import { NotificationsAdministrationComponent } from './notification/notifications-administration.component';
import { NotificationAdministrationComponent } from './notification/notification-administration.component';
import { HistoryAdministrationComponent } from './history/history-administration.component';
import { HistoryBatchAdministrationComponent } from './history/batch/history-batch-administration.component';
import { UpdateStatusAdministrationComponent } from './updateStatus/update-status-administration.component';
import { ContactsGroupsAdministrationComponent } from './contact/group/contacts-groups-administration.component';
import { ContactsGroupAdministrationComponent } from './contact/group/contacts-group-administration.component';
import { ContactsParametersAdministrationComponent } from './contact/parameter/contacts-parameters-administration.component';
import { VersionsUpdateAdministrationComponent } from './versionUpdate/versions-update-administration.component';
import { DocserversAdministrationComponent } from './docserver/docservers-administration.component';
import { DocserverAdministrationComponent } from './docserver/docserver-administration.component';
import { TemplatesAdministrationComponent } from './template/templates-administration.component';
import { TemplateAdministrationComponent } from './template/template-administration.component';
import { SecuritiesAdministrationComponent } from './security/securities-administration.component';
import { SendmailAdministrationComponent } from './sendmail/sendmail-administration.component';
import { ShippingsAdministrationComponent } from './shipping/shippings-administration.component';
import { ShippingAdministrationComponent } from './shipping/shipping-administration.component';
import { CustomFieldsAdministrationComponent } from './customField/custom-fields-administration.component';
import { AppGuard } from '@service/app.guard';
import { IndexingModelAdministrationComponent } from './indexingModel/indexing-model-administration.component';
import { IndexingModelsAdministrationComponent } from './indexingModel/indexing-models-administration.component';
import { ContactsListAdministrationComponent } from './contact/list/contacts-list-administration.component';
import { ContactsCustomFieldsAdministrationComponent } from './contact/customField/contacts-custom-fields-administration.component';
import { ContactsPageAdministrationComponent } from './contact/page/contacts-page-administration.component';
import { TagsAdministrationComponent } from './tag/tags-administration.component';
import { TagAdministrationComponent } from './tag/tag-administration.component';
import { AlfrescoAdministrationComponent } from './alfresco/alfresco-administration.component';
import { AlfrescoListAdministrationComponent } from './alfresco/alfresco-list-administration.component';
import { ContactDuplicateComponent } from './contact/contact-duplicate/contact-duplicate.component';
import { IssuingSiteListComponent } from './registered-mail/issuing-site/issuing-site-list.component';
import { IssuingSiteComponent } from './registered-mail/issuing-site/issuing-site.component';
import { RegisteredMailListComponent } from './registered-mail/registered-mail-list.component';
import { RegisteredMailComponent } from './registered-mail/registered-mail.component';
import { SearchAdministrationComponent } from './search/search-administration.component';
import { SsoAdministrationComponent } from './connection/sso/sso-administration.component';
import { AttachmentTypeAdministrationComponent } from './attachment/attachment-type-administration.component';
import { AttachmentTypesAdministrationComponent } from './attachment/attachment-types-administration.component';
import { OrganizationEmailSignaturesAdministrationComponent } from './organizationEmailSignatures/organization-email-signatures-administration.component';
import { MultigestListAdministrationComponent } from './multigest/multigest-list-administration.component';
import { MultigestAdministrationComponent } from './multigest/multigest-administration.component';
import { MigrationCheckGuard } from '@service/migration.guard';


@NgModule({
    imports: [
        RouterModule.forChild([
            { path: 'administration', canActivate: [MigrationCheckGuard, AppGuard], component: AdministrationComponent },
            { path: 'administration/users', canActivate: [MigrationCheckGuard, AppGuard], component: UsersAdministrationComponent },
            { path: 'administration/users/new', canActivate: [MigrationCheckGuard, AppGuard], component: UserAdministrationComponent },
            { path: 'administration/users/:id', canActivate: [MigrationCheckGuard, AppGuard], component: UserAdministrationComponent },
            { path: 'administration/groups', canActivate: [MigrationCheckGuard, AppGuard], component: GroupsAdministrationComponent },
            { path: 'administration/groups/new', canActivate: [MigrationCheckGuard, AppGuard], component: GroupAdministrationComponent },
            { path: 'administration/groups/:id', canActivate: [MigrationCheckGuard, AppGuard], component: GroupAdministrationComponent },
            { path: 'administration/baskets', canActivate: [MigrationCheckGuard, AppGuard], component: BasketsAdministrationComponent },
            { path: 'administration/baskets/new', canActivate: [MigrationCheckGuard, AppGuard], component: BasketAdministrationComponent },
            { path: 'administration/baskets/:id', canActivate: [MigrationCheckGuard, AppGuard], component: BasketAdministrationComponent },
            { path: 'administration/doctypes', canActivate: [MigrationCheckGuard, AppGuard], component: DoctypesAdministrationComponent },
            { path: 'administration/diffusionModels', canActivate: [MigrationCheckGuard, AppGuard], component: DiffusionModelsAdministrationComponent },
            { path: 'administration/diffusionModels/new', canActivate: [MigrationCheckGuard, AppGuard], component: DiffusionModelAdministrationComponent },
            { path: 'administration/diffusionModels/:id', canActivate: [MigrationCheckGuard, AppGuard], component: DiffusionModelAdministrationComponent },
            { path: 'administration/entities', canActivate: [MigrationCheckGuard, AppGuard], component: EntitiesAdministrationComponent },
            { path: 'administration/statuses', canActivate: [MigrationCheckGuard, AppGuard], component: StatusesAdministrationComponent },
            { path: 'administration/statuses/new', canActivate: [MigrationCheckGuard, AppGuard], component: StatusAdministrationComponent },
            { path: 'administration/statuses/:identifier', canActivate: [MigrationCheckGuard, AppGuard], component: StatusAdministrationComponent },
            { path: 'administration/parameters', canActivate: [MigrationCheckGuard, AppGuard], component: ParametersAdministrationComponent },
            { path: 'administration/parameters/new', canActivate: [MigrationCheckGuard, AppGuard], component: ParameterAdministrationComponent },
            { path: 'administration/parameters/:id', canActivate: [MigrationCheckGuard, AppGuard], component: ParameterAdministrationComponent },
            { path: 'administration/priorities', canActivate: [MigrationCheckGuard, AppGuard], component: PrioritiesAdministrationComponent },
            { path: 'administration/priorities/new', canActivate: [MigrationCheckGuard, AppGuard], component: PriorityAdministrationComponent },
            { path: 'administration/priorities/:id', canActivate: [MigrationCheckGuard, AppGuard], component: PriorityAdministrationComponent },
            { path: 'administration/actions', canActivate: [MigrationCheckGuard, AppGuard], component: ActionsAdministrationComponent },
            { path: 'administration/actions/new', canActivate: [MigrationCheckGuard, AppGuard], component: ActionAdministrationComponent },
            { path: 'administration/actions/:id', canActivate: [MigrationCheckGuard, AppGuard], component: ActionAdministrationComponent },
            { path: 'administration/notifications', canActivate: [MigrationCheckGuard, AppGuard], component: NotificationsAdministrationComponent },
            { path: 'administration/notifications/new', canActivate: [MigrationCheckGuard, AppGuard], component: NotificationAdministrationComponent },
            { path: 'administration/notifications/:identifier', canActivate: [MigrationCheckGuard, AppGuard], component: NotificationAdministrationComponent },
            { path: 'administration/history', canActivate: [MigrationCheckGuard, AppGuard], component: HistoryAdministrationComponent },
            { path: 'administration/history-batch', canActivate: [MigrationCheckGuard, AppGuard], component: HistoryBatchAdministrationComponent },
            { path: 'administration/update-status', canActivate: [MigrationCheckGuard, AppGuard], component: UpdateStatusAdministrationComponent },
            { path: 'administration/contacts', canActivate: [MigrationCheckGuard, AppGuard], component: ContactsListAdministrationComponent },
            { path: 'administration/contacts/duplicates', canActivate: [MigrationCheckGuard, AppGuard], component: ContactDuplicateComponent },
            { path: 'administration/contacts/list', redirectTo: 'contacts', pathMatch: 'full' },
            { path: 'administration/contacts/list/new', canActivate: [MigrationCheckGuard, AppGuard], component: ContactsPageAdministrationComponent },
            { path: 'administration/contacts/list/:id', canActivate: [MigrationCheckGuard, AppGuard], component: ContactsPageAdministrationComponent },
            { path: 'administration/contacts/contactsCustomFields', canActivate: [MigrationCheckGuard, AppGuard], component: ContactsCustomFieldsAdministrationComponent },
            { path: 'administration/contacts/contacts-groups', canActivate: [MigrationCheckGuard, AppGuard], component: ContactsGroupsAdministrationComponent },
            { path: 'administration/contacts/contacts-groups/new', canActivate: [MigrationCheckGuard, AppGuard], component: ContactsGroupAdministrationComponent },
            { path: 'administration/contacts/contacts-groups/:id', canActivate: [MigrationCheckGuard, AppGuard], component: ContactsGroupAdministrationComponent },
            { path: 'administration/contacts/contacts-parameters', canActivate: [MigrationCheckGuard, AppGuard], component: ContactsParametersAdministrationComponent },
            { path: 'administration/versions-update', canActivate: [MigrationCheckGuard, AppGuard], component: VersionsUpdateAdministrationComponent },
            { path: 'administration/docservers', canActivate: [MigrationCheckGuard, AppGuard], component: DocserversAdministrationComponent },
            { path: 'administration/docservers/new', canActivate: [MigrationCheckGuard, AppGuard], component: DocserverAdministrationComponent },
            { path: 'administration/templates', canActivate: [MigrationCheckGuard, AppGuard], component: TemplatesAdministrationComponent },
            { path: 'administration/templates/new', canActivate: [MigrationCheckGuard, AppGuard], component: TemplateAdministrationComponent },
            { path: 'administration/templates/:id', canActivate: [MigrationCheckGuard, AppGuard], component: TemplateAdministrationComponent },
            { path: 'administration/securities', canActivate: [MigrationCheckGuard, AppGuard], component: SecuritiesAdministrationComponent },
            { path: 'administration/sendmail', canActivate: [MigrationCheckGuard, AppGuard], component: SendmailAdministrationComponent },
            { path: 'administration/organizationEmailSignatures', canActivate: [MigrationCheckGuard, AppGuard], component: OrganizationEmailSignaturesAdministrationComponent },
            { path: 'administration/shippings', canActivate: [MigrationCheckGuard, AppGuard], component: ShippingsAdministrationComponent },
            { path: 'administration/shippings/new', canActivate: [MigrationCheckGuard, AppGuard], component: ShippingAdministrationComponent },
            { path: 'administration/shippings/:id', canActivate: [MigrationCheckGuard, AppGuard], component: ShippingAdministrationComponent },
            { path: 'administration/customFields', canActivate: [MigrationCheckGuard, AppGuard], component: CustomFieldsAdministrationComponent },
            { path: 'administration/indexingModels', canActivate: [MigrationCheckGuard, AppGuard], component: IndexingModelsAdministrationComponent },
            { path: 'administration/indexingModels/new', canActivate: [MigrationCheckGuard, AppGuard], component: IndexingModelAdministrationComponent },
            { path: 'administration/indexingModels/:id', canActivate: [MigrationCheckGuard, AppGuard], component: IndexingModelAdministrationComponent },
            { path: 'administration/tags', canActivate: [MigrationCheckGuard, AppGuard], component: TagsAdministrationComponent },
            { path: 'administration/tags/new', canActivate: [MigrationCheckGuard, AppGuard], component: TagAdministrationComponent },
            { path: 'administration/tags/:id', canActivate: [MigrationCheckGuard, AppGuard], component: TagAdministrationComponent },
            { path: 'administration/alfresco', canActivate: [MigrationCheckGuard, AppGuard], component: AlfrescoListAdministrationComponent },
            { path: 'administration/alfresco/new', canActivate: [MigrationCheckGuard, AppGuard], component: AlfrescoAdministrationComponent },
            { path: 'administration/alfresco/:id', canActivate: [MigrationCheckGuard, AppGuard], component: AlfrescoAdministrationComponent },
            { path: 'administration/multigest', canActivate: [MigrationCheckGuard, AppGuard], component: MultigestListAdministrationComponent },
            { path: 'administration/multigest/new', canActivate: [MigrationCheckGuard, AppGuard], component: MultigestAdministrationComponent },
            { path: 'administration/multigest/:id', canActivate: [MigrationCheckGuard, AppGuard], component: MultigestAdministrationComponent },
            { path: 'administration/registeredMails', canActivate: [MigrationCheckGuard, AppGuard], component: RegisteredMailListComponent },
            { path: 'administration/registeredMails/new', canActivate: [MigrationCheckGuard, AppGuard], component: RegisteredMailComponent },
            { path: 'administration/registeredMails/:id', canActivate: [MigrationCheckGuard, AppGuard], component: RegisteredMailComponent },
            { path: 'administration/issuingSites', canActivate: [MigrationCheckGuard, AppGuard], component: IssuingSiteListComponent },
            { path: 'administration/issuingSites/new', canActivate: [MigrationCheckGuard, AppGuard], component: IssuingSiteComponent },
            { path: 'administration/issuingSites/:id', canActivate: [MigrationCheckGuard, AppGuard], component: IssuingSiteComponent },
            { path: 'administration/search', canActivate: [MigrationCheckGuard, AppGuard], component: SearchAdministrationComponent },
            { path: 'administration/connections/sso', canActivate: [MigrationCheckGuard, AppGuard], component: SsoAdministrationComponent },
            { path: 'administration/attachments/types', canActivate: [MigrationCheckGuard, AppGuard], component: AttachmentTypesAdministrationComponent },
            { path: 'administration/attachments/types/new', canActivate: [MigrationCheckGuard, AppGuard], component: AttachmentTypeAdministrationComponent },
            { path: 'administration/attachments/types/:id', canActivate: [MigrationCheckGuard, AppGuard], component: AttachmentTypeAdministrationComponent },
            { path: 'administration/attachments', redirectTo: 'attachments/types', pathMatch: 'full' },
        ]),
    ],
    exports: [
        RouterModule
    ]
})
export class AdministrationRoutingModule { }
