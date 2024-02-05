import { Component, OnInit } from '@angular/core';
import { ActionsService } from '@appRoot/actions/actions.service';
import { NotificationService } from '@service/notification/notification.service';
import { Subscription } from 'rxjs';

@Component({
    selector: 'app-maarch-sb-content',
    templateUrl: 'maarch-sb-content.component.html',
    styleUrls: ['maarch-sb-content.component.scss'],
})
export class MaarchSbContentComponent implements OnInit {
    
    subscription: Subscription;

    constructor(
        private actionsService: ActionsService,
        private notificationService: NotificationService
    ) {
        this.subscription = this.actionsService.catchAction().subscribe(message => {
            this.notificationService.success('apposition de la griffe!');
        });
    }

    ngOnInit(): void {}

    ngOnDestroy() {
        // unsubscribe to ensure no memory leaks
        this.subscription.unsubscribe();
    }
}
