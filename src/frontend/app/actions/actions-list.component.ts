import { Component, OnInit, Input, ViewChild, Output, EventEmitter } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { MatMenuTrigger } from '@angular/material/menu';
import { Router } from '@angular/router';
import { ActionsService } from './actions.service';
import { Subscription } from 'rxjs';

@Component({
    selector: 'app-actions-list',
    templateUrl: "actions-list.component.html",
    styleUrls: ['actions-list.component.scss'],
    providers: [NotificationService, ActionsService],
})
export class ActionsListComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    @ViewChild(MatMenuTrigger, { static: false }) contextMenu: MatMenuTrigger;
    @Output() triggerEvent = new EventEmitter<string>();

    contextMenuPosition = { x: '0px', y: '0px' };
    contextMenuTitle = '';
    currentAction: any = {};
    currentResource: any = null;
    basketInfo: any = {};
    contextResId = 0;
    currentLock: any = null;
    arrRes: any[] = [];
    folderList: any[] = [];

    actionsList: any[] = [];

    @Input('selectedRes') selectedRes: any;
    @Input('totalRes') totalRes: number;
    @Input('contextMode') contextMode: boolean;
    @Input('currentBasketInfo') currentBasketInfo: any;

    @Output('refreshEvent') refreshEvent = new EventEmitter<string>();
    @Output('refreshPanelFolders') refreshPanelFolders = new EventEmitter<string>();

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public dialog: MatDialog,
        private router: Router,
        private actionService: ActionsService
    ) { }

    dialogRef: MatDialogRef<any>;
    subscription: Subscription;

    ngOnInit(): void {
        // Event after process action 
        this.subscription = this.actionService.catchAction().subscribe(message => {
            console.log('TOTO!');
            this.refreshEvent.emit();
            this.refreshPanelFolders.emit();
        });
    }

    open(x: number, y: number, row: any) {

        this.loadActionList();
        // Adjust the menu anchor position
        this.contextMenuPosition.x = x + 'px';
        this.contextMenuPosition.y = y + 'px';

        this.currentResource = row;

        this.contextMenuTitle = row.alt_identifier;
        this.contextResId = row.res_id;

        this.folderList = row.folders !== undefined ? row.folders : [];

        // Opens the menu
        this.contextMenu.openMenu();

        // prevents default
        return false;
    }

    launchEvent(action: any, row: any) {
        this.arrRes = [];
        this.currentAction = action;

        this.arrRes = this.selectedRes;


        if (this.contextMode && this.selectedRes.length > 1) {
            this.contextMenuTitle = '';
            this.contextResId = 0;
        }

        if (row !== undefined) {
            this.contextMenuTitle = row.alt_identifier;
        }

        this.actionService.launchAction(action, this.currentBasketInfo.ownerId, this.currentBasketInfo.groupId, this.currentBasketInfo.basketId, this.selectedRes, this.currentResource);

    }

    loadActionList() {

        if (JSON.stringify(this.basketInfo) != JSON.stringify(this.currentBasketInfo)) {

            this.basketInfo = JSON.parse(JSON.stringify(this.currentBasketInfo));

            this.http.get('../../rest/resourcesList/users/' + this.currentBasketInfo.ownerId + '/groups/' + this.currentBasketInfo.groupId + '/baskets/' + this.currentBasketInfo.basketId + '/actions')
                .subscribe((data: any) => {
                    if (data.actions.length > 0) {
                        this.actionsList = data.actions;

                        // TO DO TO REMOVE AFTER BACK CHANGE : label_action => label
                        this.actionsList = data.actions.map((action: any) => {
                            return {
                                id: action.id,
                                label: action.label_action,
                                component: action.component
                            }
                        });
                        
                    } else {
                        this.actionsList = [{
                            id: 0,
                            label_action: this.lang.noAction,
                            component: ''
                        }];
                    }
                    this.loading = false;
                }, (err: any) => {
                    this.notify.handleErrors(err);
                });
        }
    }

    refreshList() {
        this.refreshEvent.emit();
    }

    refreshFolders() {
        this.refreshPanelFolders.emit();
    }

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
    }
}
