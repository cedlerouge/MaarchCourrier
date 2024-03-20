import { ComponentFixture, TestBed, fakeAsync, flush, tick } from '@angular/core/testing';
import { TranslateLoader, TranslateModule, TranslateService, TranslateStore } from '@ngx-translate/core';
import { Observable, of } from 'rxjs';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { RouterTestingModule } from '@angular/router/testing';
import { SharedModule } from '@appRoot/app-common.module';
import { HttpClient } from '@angular/common/http';
import { ActionsService } from '@appRoot/actions/actions.service';
import { FoldersService } from '@appRoot/folder/folders.service';
import { DatePipe } from '@angular/common';
import { FiltersListService } from '@service/filtersList.service';
import { PrivilegeService } from '@service/privileges.service';
import { AdministrationService } from '@appRoot/administration/administration.service';
import { ResourcesListComponent } from '@appRoot/signatureBook/resourcesList/resources-list.component';
import { ResourcesList } from '@models/resources-list.model';
import * as langFrJson from '@langs/lang-fr.json';
import { Router } from '@angular/router';

class FakeLoader implements TranslateLoader {
    getTranslation(): Observable<any> {
        return of({ lang: langFrJson });
    }
}

describe('ResourcesListComponent', () => {
    let component: ResourcesListComponent;
    let fixture: ComponentFixture<ResourcesListComponent>;
    let translateService: TranslateService;
    let httpTestingController: HttpTestingController;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [
                SharedModule,
                RouterTestingModule,
                BrowserAnimationsModule,
                TranslateModule,
                HttpClientTestingModule,
                BrowserModule,
                TranslateModule.forRoot({
                    loader: { provide: TranslateLoader, useClass: FakeLoader },
                }),
            ],
            providers: [
                TranslateService,
                ActionsService,
                FoldersService,
                FiltersListService,
                PrivilegeService,
                AdministrationService,
                DatePipe,
                TranslateStore,
                HttpClient,
            ],
            declarations: [ResourcesListComponent],
        }).compileComponents();

        // Set lang
        translateService = TestBed.inject(TranslateService);
        translateService.use('fr');
    });

    beforeEach(fakeAsync(() => {
        httpTestingController = TestBed.inject(HttpTestingController);
        fixture = TestBed.createComponent(ResourcesListComponent);
        component = fixture.componentInstance;

        fixture.detectChanges();
    }));

    describe('Create component', () => {
        it('should create', () => {
            expect(component).toBeTruthy();
        });
    });

    describe('Load resources', () => {
        it('check whether the resourcesList division contains the resources', fakeAsync(() => {
            component.resources = getResources();

            fixture.detectChanges();
            tick();

            expect(fixture.nativeElement.querySelectorAll('.resourcesList').length).toEqual(4);
        }));

        it('Check if the resource line is highlighted when its resId matches the one passed as input and contains the selectedRes class', fakeAsync(() => {
            component.resId = 103;
            component.resources = getResources();

            fixture.detectChanges();
            tick();

            const firstResource = fixture.nativeElement.querySelectorAll('.resourcesList')[0];
            expect(firstResource.classList.contains('selectedRes')).toBeTrue();
        }));
    });

    describe('Navigate to resource', () => {
        it('navigate to resource and check if is not locked', fakeAsync(() => {
            component.resId = 103;
            component.userId = 19;
            component.groupId = 2;
            component.basketId= 16;
            component.resources = getResources();

            fixture.detectChanges();
            tick(100);

            const secondResource = fixture.nativeElement.querySelectorAll('.resourcesList')[1];
            secondResource.click();

            fixture.detectChanges();
            tick(100);

            const router = TestBed.inject(Router);
            const navigateSpy = spyOn(router, 'navigate');
            const req = httpTestingController.expectOne(`../rest/resourcesList/users/${component.userId}/groups/${component.groupId}/baskets/${component.basketId}/locked`);
            expect(req.request.method).toBe('PUT');
            expect(req.request.body).toEqual({ resources: component.resources.map((resource: ResourcesList) => resource.resId) });
            req.flush({
                countLockedResources: 0,
                lockers: [],
                resourcesToProcess: [103, 101, 100, 102]
            });

            setTimeout(() => {
                // Check if navigation is called with the correct route
                const path: string = `/signatureBook/users/${component.userId}/groups/${component.groupId}/baskets/${component.basketId}/resources/${component.selectedResource.resId}`
                expect(navigateSpy).toHaveBeenCalledWith([path]);
            }, 100);

            tick(300);
            fixture.detectChanges();
            flush();
        }));
    })
});

function getResources(): ResourcesList[] {
    const resources: ResourcesList[] = [
        {
            "resId": 103,
            "subject": "Courrier départ",
            "chrono": "MAARCH/2024D/35",
            "statusImage": "fm-letter-status-aval",
            "statusLabel": "A e-viser",
            "priorityColor": "#009dc5",
            "creationDate": "2024-03-15 13:28:00.706412",
            "processLimitDate": "2024-06-12 23:59:59",
            "mailTracking": false
        },
        {
            "resId": 102,
            "subject": "Lorem ipsum dolor sit amet consectetur adipisicing elit. Est quisquam recusandae rerum perferendis illo eum magnam hic tempore error asperiores! Cupiditate doloribus ipsum dolor. Quaerat cum commodi voluptatem nam repudianda",
            "chrono": "MAARCH/2024D/34",
            "statusImage": "fm-letter-status-aval",
            "statusLabel": "A e-viser",
            "priorityColor": "#009dc5",
            "creationDate": "2024-03-14 11:54:41.258402",
            "processLimitDate": null,
            "mailTracking": false
        },
        {
            "resId": 101,
            "subject": "Courrier de test 2",
            "chrono": "MAARCH/2024A/2",
            "statusImage": "fm-letter-status-aval",
            "statusLabel": "A e-viser",
            "priorityColor": "#ffa500",
            "creationDate": "2024-03-14 10:53:02.737637",
            "processLimitDate": "2024-03-26 23:59:59",
            "mailTracking": false
        },
        {
            "resId": 100,
            "subject": "Courrier de test",
            "chrono": "MAARCH/2024A/1",
            "statusImage": "fm-letter-status-aval",
            "statusLabel": "A e-viser",
            "priorityColor": "#009dc5",
            "creationDate": "2024-03-06 17:24:03.75654",
            "processLimitDate": "2024-06-03 23:59:59",
            "mailTracking": false
        }
    ];

    return resources;
}