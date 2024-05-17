import { Component, Input, OnInit } from '@angular/core';
import { TranslateService } from "@ngx-translate/core";
import { PrivilegeService } from "@service/privileges.service";
import { catchError, tap } from "rxjs/operators";
import { of } from "rxjs";
import { HttpClient } from "@angular/common/http";
import { NotificationService } from "@service/notification/notification.service";

@Component({
    selector: 'app-resource-toolbar',
    templateUrl: 'resource-toolbar.component.html',
    styleUrls: ['resource-toolbar.component.scss'],
})
export class ResourceToolbarComponent implements OnInit{
    @Input() resId: number;
    @Input() groupId: number;

    currentTool: string = 'visaCircuit';
    modelId: number;

    processTool: any[] = [
        {
            id: 'dashboard',
            icon: 'fas fa-columns',
            label: this.translate.instant('lang.newsFeed'),
            disabled: true,
            count: 0
        },
        {
            id: 'history',
            icon: 'fas fa-history',
            label: this.translate.instant('lang.history'),
            disabled: true,
            count: 0
        },
        {
            id: 'notes',
            icon: 'fas fa-pen-square',
            label: this.translate.instant('lang.notesAlt'),
            disabled: false,
            count: 0
        },
        {
            id: 'attachments',
            icon: 'fas fa-paperclip',
            label: this.translate.instant('lang.attachments'),
            disabled: true,
            count: 0
        },
        {
            id: 'linkedResources',
            icon: 'fas fa-link',
            label: this.translate.instant('lang.links'),
            disabled: true,
            count: 0
        },
        {
            id: 'emails',
            icon: 'fas fa-envelope',
            label: this.translate.instant('lang.mailsSentAlt'),
            disabled: true,
            count: 0
        },
        {
            id: 'diffusionList',
            icon: 'fas fa-share-alt',
            label: this.translate.instant('lang.diffusionList'),
            disabled: true,
            editMode: false,
            count: 0
        },
        {
            id: 'visaCircuit',
            icon: 'fas fa-list-ol',
            label: this.translate.instant('lang.visaWorkflow'),
            disabled: false,
            count: 0
        },
        {
            id: 'opinionCircuit',
            icon: 'fas fa-comment-alt',
            label: this.translate.instant('lang.avis'),
            disabled: true,
            count: 0
        },
        {
            id: 'info',
            icon: 'fas fa-info-circle',
            label: this.translate.instant('lang.informations'),
            disabled: false,
            count: 0
        }
    ];

    constructor(
        public http: HttpClient,
        public translate: TranslateService,
        public privilegeService: PrivilegeService,
        private notify: NotificationService,
    ) { }

    ngOnInit() {
        this.loadBadges();
    }

    async changeTab(tabId: string) {
        if (!this.modelId && tabId === 'info') {
            const res = await this.getResourceInformation();
            if (res) {
                this.modelId = res;
            }
        }
        this.currentTool = tabId;
    }

    getResourceInformation() : Promise<false | number> {
        return new Promise((resolve) => {
            this.http.get(`../rest/resources/${this.resId}?light=true`).pipe(
                tap((data: any) => {
                    resolve(data.modelId);
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    resolve(false);
                    return of(false);
                })
            ).subscribe();
        })
    }

    loadBadges() {
        this.http.get(`../rest/resources/${this.resId}/items`).pipe(
            tap((data: any) => {
                this.processTool.forEach(element => {
                    element.count = data[element.id] !== undefined ? data[element.id] : 0;
                });
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

}
