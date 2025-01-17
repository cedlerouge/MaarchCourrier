import { Component, OnInit, Input, ViewChild, Output, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '@service/notification/notification.service';
import { MatLegacyDialog as MatDialog, MatLegacyDialogRef as MatDialogRef } from '@angular/material/legacy-dialog';
import { MatLegacyMenuTrigger as MatMenuTrigger } from '@angular/material/legacy-menu';

import { Router } from '@angular/router';
import { ConfirmComponent } from '../../../plugins/modal/confirm.component';
import { filter, exhaustMap, tap, map, catchError } from 'rxjs/operators';
import { HeaderService } from '@service/header.service';
import { FoldersService } from '../folders.service';
import { of } from 'rxjs';
import { PrivilegeService } from '@service/privileges.service';

@Component({
    selector: 'app-folder-action-list',
    templateUrl: 'folder-action-list.component.html',
    styleUrls: ['folder-action-list.component.scss'],
})
export class FolderActionListComponent implements OnInit {

    @ViewChild(MatMenuTrigger, { static: false }) contextMenu: MatMenuTrigger;

    @Input() selectedRes: any;
    @Input() totalRes: number;
    @Input() contextMode: boolean;
    @Input() currentFolderInfo: any;
    @Input() currentResource: any = {};

    @Output() refreshEvent = new EventEmitter<string>();
    @Output() refreshPanelFolders = new EventEmitter<string>();
    @Output() triggerEvent = new EventEmitter<string>();

    dialogRef: MatDialogRef<any>;

    loading: boolean = false;

    contextMenuPosition = { x: '0px', y: '0px' };
    contextMenuTitle = '';
    currentAction: any = {};
    basketInfo: any = {};
    contextResId = 0;
    currentLock: any = null;
    arrRes: any[] = [];

    isSelectedFreeze: any;
    isSelectedBinding: any;

    actionsList: any[] = [];
    basketList: any = {
        groups: [],
        list: []
    };

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog,
        private router: Router,
        private headerService: HeaderService,
        private foldersService: FoldersService,
        public privilegeService: PrivilegeService,
    ) { }

    ngOnInit(): void { }

    open(x: number, y: number, row: any) {

        // Adjust the menu anchor position
        this.contextMenuPosition.x = x + 'px';
        this.contextMenuPosition.y = y + 'px';

        this.contextMenuTitle = row.chrono;
        this.contextResId = row.resId;
        this.currentResource = row;

        this.getFreezeBindingValue();

        // Opens the menu
        this.contextMenu.openMenu();

        // prevents default
        return false;
    }
    refreshList() {
        this.refreshEvent.emit();
    }

    refreshFolders() {
        this.refreshPanelFolders.emit();
    }

    refreshDaoAfterAction() {
        this.refreshEvent.emit();
    }

    unclassify() {
        this.dialogRef = this.dialog.open(ConfirmComponent, { panelClass: 'maarch-modal', autoFocus: false, disableClose: true, data: { title: this.translate.instant('lang.delete'), msg: 'Voulez-vous enlever <b>' + this.selectedRes.length + '</b> document(s) du classement ?' } });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.request('DELETE', '../rest/folders/' + this.currentFolderInfo.id + '/resources', { body: { resources: this.selectedRes } })),
            tap((data: any) => {
                this.notify.success(this.translate.instant('lang.removedFromFolder'));
                this.refreshFolders();
                this.foldersService.getPinnedFolders();
                this.refreshDaoAfterAction();
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    getBaskets() {
        this.http.get('../rest/resources/' + this.selectedRes + '/baskets').pipe(
            tap((data: any) => {
                this.basketList.groups = data.groupsBaskets.filter((x: any, i: any, a: any) => x && a.map((info: any) => info.groupId).indexOf(x.groupId) === i);
                this.basketList.list = data.groupsBaskets;
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }


    goTo(basket: any) {
        if (this.contextMenuTitle !== this.translate.instant('lang.undefined')) {
            this.router.navigate(['/basketList/users/' + this.headerService.user.id + '/groups/' + basket.groupId + '/baskets/' + basket.basketId], { queryParams: { chrono: '"' + this.contextMenuTitle + '"' } });
        } else {
            this.router.navigate(['/basketList/users/' + this.headerService.user.id + '/groups/' + basket.groupId + '/baskets/' + basket.basketId]);
        }
    }

    unFollow() {
        this.dialogRef = this.dialog.open(ConfirmComponent,
            {
                panelClass: 'maarch-modal',
                autoFocus: false,
                disableClose: true,
                data: {
                    title: this.translate.instant('lang.untrackThisMail'),
                    msg: this.translate.instant('lang.stopFollowingAlert')
                }
            });

        this.dialogRef.afterClosed().pipe(
            filter((data: string) => data === 'ok'),
            exhaustMap(() => this.http.request('DELETE', '../rest/resources/unfollow', { body: { resources: this.selectedRes } })),
            tap((data: any) => {
                this.notify.success(this.translate.instant('lang.removedFromFolder'));
                this.headerService.nbResourcesFollowed -= data.unFollowed;
                this.refreshDaoAfterAction();
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    toggleFreezing(value) {
        this.http.put('../rest/archival/freezeRetentionRule', { resources: this.selectedRes, freeze : value }).pipe(
            tap(() => {
                if (value) {
                    this.notify.success(this.translate.instant('lang.retentionRuleFrozen'));
                } else {
                    this.notify.success(this.translate.instant('lang.retentionRuleUnfrozen'));

                }
                this.refreshList();
            }
            ),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    toogleBinding(value) {
        this.http.put('../rest/archival/binding', { resources: this.selectedRes, binding : value }).pipe(
            tap(() => {
                if (value) {
                    this.notify.success(this.translate.instant('lang.bindingMail'));
                } else if (value === false) {
                    this.notify.success(this.translate.instant('lang.noBindingMail'));
                } else {
                    this.notify.success(this.translate.instant('lang.bindingUndefined'));
                }
                this.refreshList();
            }
            ),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    getFreezeBindingValue() {
        this.isSelectedFreeze = this.currentResource.retentionFrozen;
        this.isSelectedBinding = this.currentResource.binding;
    }
}
