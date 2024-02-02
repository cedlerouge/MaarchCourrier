import { HttpClient } from '@angular/common/http';
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { Router } from '@angular/router';
import { ActionsService } from '@appRoot/actions/actions.service';
import { Action } from '@models/actions.model';
import { StampInterface } from '@models/signature-book.model';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, of, tap } from 'rxjs';

@Component({
    selector: 'app-maarch-sb-actions',
    templateUrl: 'signature-book-actions.component.html',
    styleUrls: ['signature-book-actions.component.scss'],
})
export class SignatureBookActionsComponent implements OnInit {
    @Input() resId: number;
    @Input() basketId: number;
    @Input() groupId: number;
    @Input() userId: number;
    @Input() stamp: StampInterface;

    @Output() openPanelSignatures = new EventEmitter<true>();

    loading: boolean = true;

    leftActions: Action[] = [];
    rightActions: Action[] = [];

    constructor(public http: HttpClient, private notify: NotificationService, private actionsService: ActionsService, private router: Router) {

    }

    async ngOnInit(): Promise<void> {
        await this.loadActions();
        this.loading = false; 
    }

    openSignaturesList() {
        this.openPanelSignatures.emit(true);
    }

    loadActions() {
        return new Promise((resolve) => {
            this.actionsService.getActions(this.userId, this.groupId, this.basketId, this.resId)
                .pipe(
                    tap((actions: Action[]) => {
                        this.leftActions = [actions[1]];
                        this.rightActions = actions.filter((action: Action, key: number) => key !== 1);
                        resolve(true);
                    }),
                    catchError((err: any) => {
                        this.notify.handleSoftErrors(err.error.errors);
                        return of(false);
                    })
                )
                .subscribe();
        });
    }

    processAction(action: any) {
        this.http
            .get(`../rest/resources/${this.resId}?light=true`)
            .pipe(
                tap((data: any) => {
                    this.actionsService.launchAction(
                        action,
                        this.userId,
                        this.groupId,
                        this.basketId,
                        [this.resId],
                        data,
                        false
                    );
                }),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            )
            .subscribe();
    }

    processAfterAction() {
        this.backToBasket();
    }

    backToBasket() {
        const path = '/basketList/users/' + this.userId + '/groups/' + this.groupId + '/baskets/' + this.basketId;
        this.router.navigate([path]);
    }

    signWithStamp(stamp: StampInterface) {
        this.actionsService.emitActionWithData(stamp);
    }

}
