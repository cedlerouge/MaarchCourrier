import { AfterViewInit, Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
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
    scrolledIndex: number = 0;

    loading: boolean = true;

    constructor(
        public translate: TranslateService,
        public signatureBookService: SignatureBookService,
        private actionsService: ActionsService,
        private router: Router,
        private notifications: NotificationService,
        private actionService: ActionsService,
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
        this.selectedResource = this.resources.find((resource: ResourcesList) => resource.resId === this.resId);
        this.scrollToSelectedResource();
        // Handle scrolledIndexChange event
        this.viewport.scrolledIndexChange.subscribe(async (index: number) => {
            this.scrolledIndex = index;
            const end: number = this.viewport.getRenderedRange().end;
            // Check if scrolled to the end of the list
            if (index > 0 && end === this.resources.length && this.resources.length < this.signatureBookService.resourcesListCount && this.signatureBookService.offset < 100) {
            // load data
                this.loadDatas();
            }
        });
    }

    /**
     * Asynchronously loads data from the backend.
     * @param isPrevious Indicates whether to load previous data.
    */
    async loadDatas(isPrevious: boolean = false): Promise<void> {
        // Fetch data from the backend
        const array: any = await this.signatureBookService.getResourcesBasket(
            this.userId,
            this.groupId,
            this.basketId,
            'infiniteScroll',
            isPrevious
        );

        // Concatenate fetched data with existing data
        if (!isPrevious) {
            const concatArray: ResourcesList[] = this.resources.concat(array);
            this.resources = concatArray;
        } else {
            // If loading previous data, prepend fetched data to existing data
            this.resources = [...array, ...this.resources];
        }
    }

    /**
     * Navigates to the selected resource.
     * @param resource The resource to navigate to.
    */
    goToResource(resource: ResourcesList): void {
        // Set the selected resource
        this.selectedResource = resource;

        // Call the actions service to navigate to the resource
        this.actionsService.goToResource(this.resources, this.userId, this.groupId, this.basketId).subscribe((resourcesToProcess: number[]) => {
            // Check if the resource is locked
            if (resourcesToProcess.indexOf(resource.resId) > -1) {
                // Emit event to close the resource list panel
                this.closeResListPanel.emit('goToResource');

                // Construct the path to navigate to
                const path: string = `/signatureBook/users/${this.userId}/groups/${this.groupId}/baskets/${this.basketId}/resources/${resource.resId}`;

                // Navigate to the resource
                this.router.navigate([path]);

                // scroll to the selected resource
                this.scrollToSelectedResource();

                // Unlock the resource
                this.unlockResource();
            } else {
                // Notify user that the resource is locked
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

    /**
     * Handles the scroll event triggered by mouse wheel.
     * @param event The wheel event object.
    */
    handleScrollEvent(event: WheelEvent) {
        // Check if scrolling upwards
        if (event.deltaY < 0) {
            // If scrolled to the top and more data is available and offset is greater than 0
            if (this.scrolledIndex === 0 &&
            this.resources.length < this.signatureBookService.resourcesListCount &&
            this.signatureBookService.offset > 0) {
                // Load previous data
                this.loadDatas(true);
            }
        }
    }

    /**
     * Scrolls to the selected resource.
    */
    scrollToSelectedResource(): void {
        // Get the index of the selected resource
        const index: number = this.resources.indexOf(this.selectedResource);
        // If the selected resource exists in the list
        if (index !== -1) {
            // Calculate the position of the element
            const position = index * this.itemSize;
            // Scroll to the element
            this.viewport.scrollToIndex(position);
        }
    }
}

