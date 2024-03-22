import { AfterViewInit, ChangeDetectorRef, Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
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
    @ViewChild('resourceElement', { static: false }) resourceElement: ElementRef;

    @Input() resId: number;
    @Input() basketId: number;
    @Input() groupId: number;
    @Input() userId: number;
    @Input() basketLabel: string = '';

    @Output() closeResListPanel = new EventEmitter<string>();

    resources: ResourcesList[] = [];
    selectedResource: ResourcesList;

    itemSize: number = 0;

    loading: boolean = true;

    constructor(
        public translate: TranslateService,
        public signatureBookService: SignatureBookService,
        private actionsService: ActionsService,
        private router: Router,
        private notifications: NotificationService,
        private actionService: ActionsService,
        private cdr: ChangeDetectorRef
    ) { }

    async ngOnInit(): Promise<void> {
        if (this.resources.length === 0) {
            this.resources = await this.signatureBookService.getResourcesBasket(this.userId, this.groupId, this.basketId);
        }
        this.loading = false;
        // Get element height
        setTimeout(() => {
            const elementHeight = this.resourceElement?.nativeElement?.getBoundingClientRect()?.height;
            this.itemSize = elementHeight;
        }, 10);
    }

    async ngAfterViewInit() {
        // Handle scrolledIndexChange event
        this.viewport.scrolledIndexChange.subscribe(async (index: number) => {
            const end: number = this.viewport.getRenderedRange().end;
            // Check if scrolled to the end of the list
            if (end === this.resources.length && this.resources.length < this.signatureBookService.resourcesListCount && this.signatureBookService.offset < 100) {
            // load data
                this.loadDatas();
            }
        });
    }

    async loadDatas(): Promise<void> {
        const array = await this.signatureBookService.getResourcesBasket(this.userId, this.groupId, this.basketId, 'infiniteScroll');
        const concatArray: ResourcesList[] = this.resources.concat(array);
        this.resources = concatArray;
    }

    goToResource(resource: ResourcesList): void {
        this.selectedResource = resource;
        this.actionsService.goToResource(this.resources, this.userId, this.groupId, this.basketId).subscribe((resourcesToProcess: number[]) => {
            // Check if the resource is locked
            if (resourcesToProcess.indexOf(resource.resId) > -1) {
                this.closeResListPanel.emit('goToResource');
                const path: string = `/signatureBook/users/${this.userId}/groups/${this.groupId}/baskets/${this.basketId}/resources/${resource.resId}`;
                this.router.navigate([path]);
                this.unlockResource();
            } else {
                this.notifications.error(this.translate.instant('lang.warnResourceLockedByUser'));
            }
        });
    }

    async unlockResource(): Promise<void> {
        this.actionService.stopRefreshResourceLock();
        await this.actionService.unlockResource(this.userId, this.groupId, this.basketId, [this.resId]);
    }

    calculateContainerHeight(): string {
        const resourcesLength = this.resources.length;
        // This should be the height of your item in pixels
        const itemHeight = this.itemSize;
        // The final number of items to keep visible
        const visibleItems = 15;
        setTimeout(() => {
            /* Makes CdkVirtualScrollViewport to refresh its internal size values after
            * changing the container height. This should be delayed with a "setTimeout"
            * because we want it to be executed after the container has effectively
            * changed its height. Another option would be a resize listener for the
            * container and call this line there but it may requires a library to detect the resize event.
            * */
            this.viewport.checkViewportSize();
        }, 50);
        // It calculates the container height for the first items in the list
        // It means the container will expand until it reaches `itemSizepx`
        // and will keep this size.
        if (resourcesLength <= visibleItems) {
            return `${itemHeight * resourcesLength}px`;
        }
        // This function is called from the template so it ensures the container will have
        // the final height if number of items are greater than the value in "visibleItems".
        return `${itemHeight * visibleItems}px`;
    }
}

