import { AfterViewInit, Component, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { ResourcesList } from '@models/resources-list.model';
import { TranslateService } from '@ngx-translate/core';
import { SignatureBookService } from '../signature-book.service';
import { ActionsService } from '@appRoot/actions/actions.service';
import { NotificationService } from '@service/notification/notification.service';
import { Router } from '@angular/router';
import { CdkVirtualScrollViewport } from '@angular/cdk/scrolling';

@Component({
    selector: 'app-resources-list',
    templateUrl: 'resources-list.component.html',
    styleUrls: ['resources-list.component.scss'],
})

export class ResourcesListComponent implements AfterViewInit, OnInit {

    @ViewChild('viewport', { static: false }) viewport: CdkVirtualScrollViewport;

    @Input() resId: number;
    @Input() basketId: number;
    @Input() groupId: number;
    @Input() userId: number;
    @Input() basketLabel: string = '';

    @Output() closeResListPanel = new EventEmitter<any>();

    resources: ResourcesList[] = [];

    itemSize: number = 5;

    loading: boolean = true;

    constructor(
        public translate: TranslateService,
        public signatureBookService: SignatureBookService,
        private actionsService: ActionsService,
        private router: Router,
        private notifications: NotificationService
    ) { }

    async ngOnInit(): Promise<void> {
        if (this.resources.length === 0) {
            this.resources = await this.signatureBookService.getResourcesBasket(this.userId, this.groupId, this.basketId);
        }
        this.loading = false;
    }

    async ngAfterViewInit() {
        this.loadDatas();
        // Handle scrolledIndexChange event
        this.viewport.scrolledIndexChange.subscribe(async () => {
            const end: number = this.viewport.getRenderedRange().end;
            const total: number = this.viewport.getDataLength();
            // Check if scrolled to the end of the list
            if (end === total) {
                // Keep loading data until resources list count
                while (this.resources.length <= this.signatureBookService.resourcesListCount && this.signatureBookService.offset < 100) {
                    this.loadDatas();
                }
            }
        });
    }

    async loadDatas(): Promise<void> {
        const array = await this.signatureBookService.getResourcesBasket(this.userId, this.groupId, this.basketId, 'infiniteScroll');
        const concatArray: ResourcesList[] = this.resources.concat(array);
        this.resources = concatArray;
    }

    goToResource(resource: ResourcesList): void {
        this.actionsService.goToResource(this.resources, this.userId, this.groupId, this.basketId).subscribe((resourcesToProcess: number[]) => {
            // Check if the resource is locked
            if (resourcesToProcess.indexOf(resource.resId) > -1) {
                const path: string = `/signatureBook/users/${this.userId}/groups/${this.groupId}/baskets/${this.basketId}/resources/${resource.resId}`;
                this.router.navigate([path]);
            } else {
                this.notifications.error(this.translate.instant('lang.warnResourceLockedByUser'));
            }
        });
    }
}

