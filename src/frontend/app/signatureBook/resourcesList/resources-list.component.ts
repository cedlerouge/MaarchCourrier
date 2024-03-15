import { HttpClient } from '@angular/common/http';
import { Component, EventEmitter, Input, Output } from '@angular/core';
import { Router } from '@angular/router';
import { ActionsService } from '@appRoot/actions/actions.service';
import { ResourcesList } from '@models/resources-list.model';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, map, of, tap } from 'rxjs';

@Component({
    selector: 'app-resources-list',
    templateUrl: 'resources-list.component.html',
    styleUrls: ['resources-list.component.scss'],
})

export class ResourcesListComponent {

    @Input() resources: ResourcesList[] = [];
    @Input() resId: number;
    @Input() basketId: number;
    @Input() groupId: number;
    @Input() userId: number;
    @Input() basketLabel: string = '';

    @Output() closeResListPanel = new EventEmitter<any>();

    constructor(
        public translate: TranslateService,
        private router: Router,
        private http: HttpClient,
        private notifications: NotificationService,
        private actionService: ActionsService
    ) { }

    goToResource(resource: ResourcesList): void {
        const resIds: number[] = this.resources.map((resource: ResourcesList) => resource.resId);
        // Check if the resource is locked
        this.http.put(`../rest/resourcesList/users/${this.userId}/groups/${this.groupId}/baskets/${this.basketId}/locked`, { resources: resIds }).pipe(
            map((data: any) => data.resourcesToProcess),
            tap((resourcesToProcess: number[]) => {
                if (resourcesToProcess.indexOf(resource.resId) > -1) {
                    const path: string = `/signatureBook/users/${this.userId}/groups/${this.groupId}/baskets/${this.basketId}/resources/${resource.resId}`;
                    this.router.navigate([path]);
                } else {
                    this.notifications.error(this.translate.instant('lang.warnResourceLockedByUser'));
                }
            }),
            catchError((err: any) => {
                this.notifications.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    toggleMailTracking(resource: ResourcesList): void {
        if (!resource.mailTracking) {
            this.http.post('../rest/resources/follow', { resources: [resource.resId] }).pipe(
                tap(() => {}),
                catchError((err: any) => {
                    this.notifications.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {
            this.http.request('DELETE', '../rest/resources/unfollow', { body: { resources: [resource.resId] } }).pipe(
                tap(() => {}),
                catchError((err: any) => {
                    this.notifications.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
        resource.mailTracking = !resource.mailTracking;
    }
}