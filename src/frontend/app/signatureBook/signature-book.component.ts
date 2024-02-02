import { Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ActionsService } from '@appRoot/actions/actions.service';
import { Subscription } from 'rxjs';
import { MatDrawer } from '@angular/material/sidenav';
import { StampInterface } from '@models/signature-book.model';

@Component({
    templateUrl: 'signature-book.component.html',
    styleUrls: ['signature-book.component.scss'],
})
export class SignatureBookComponent implements OnInit {

    @ViewChild('drawerStamps', { static: true }) stampsPanel: MatDrawer;

    resId: number;
    basketId: number;
    groupId: number;
    userId: number;

    selectedAttachment: number = 0;
    selectedDocToSign: number = 0;

    defaultStamp: StampInterface;

    attachments: string[] = [
        'Annexe',
        'Annexe',
        'Annexe'
    ];

    docsToSign: string[] = [
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
        'Doc à signer',
    ];

    subscription: Subscription;

    constructor(
        private route: ActivatedRoute,
        private actionsService: ActionsService,
    ) {
        this.subscription = this.actionsService.catchAction().subscribe(message => {
            this.stampsPanel.close();
        });
    }

    async ngOnInit(): Promise<void> {
        await this.initParams();
    }

    initParams() {
        return new Promise((resolve) => {
            this.route.params.subscribe(params => {
                this.resId = params['resId'];
                this.basketId = params['basketId'];
                this.groupId = params['groupId'];
                this.userId = params['userId'];
                resolve(true);
            });
        });
    }

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
    }
}
