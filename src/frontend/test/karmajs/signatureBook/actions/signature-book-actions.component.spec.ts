import { ComponentFixture, TestBed, fakeAsync } from '@angular/core/testing';
import { TranslateLoader, TranslateModule, TranslateService, TranslateStore } from '@ngx-translate/core';
import { Observable, of } from 'rxjs';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { RouterTestingModule } from '@angular/router/testing';
import { SharedModule } from '@appRoot/app-common.module';
import { HttpClient } from '@angular/common/http';
import * as langFrJson from '@langs/lang-fr.json';
import { SignatureBookActionsComponent } from '@appRoot/signatureBook/actions/signature-book-actions.component';
import { ActionsService } from '@appRoot/actions/actions.service';
import { FoldersService } from '@appRoot/folder/folders.service';
import { DatePipe } from '@angular/common';
import { FiltersListService } from '@service/filtersList.service';
import { PrivilegeService } from '@service/privileges.service';
import { AdministrationService } from '@appRoot/administration/administration.service';
import { SignatureBookService } from '@appRoot/signatureBook/signature-book.service';

class FakeLoader implements TranslateLoader {
    getTranslation(): Observable<any> {
        return of({ lang: langFrJson });
    }
}

describe('SignatureBookActionsComponent', () => {
    let component: SignatureBookActionsComponent;
    let fixture: ComponentFixture<SignatureBookActionsComponent>;
    let httpTestingController: HttpTestingController;
    let translateService: TranslateService;
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
                SignatureBookService,
                DatePipe,
                TranslateStore,
                HttpClient,
            ],
            declarations: [SignatureBookActionsComponent],
        }).compileComponents();

        // Set lang
        translateService = TestBed.inject(TranslateService);
        translateService.use('fr');
    });

    beforeEach(fakeAsync(() => {
        httpTestingController = TestBed.inject(HttpTestingController);
        fixture = TestBed.createComponent(SignatureBookActionsComponent);
        component = fixture.componentInstance;
        component.resId = 100;
        component.userId = 1;
        component.basketId = 1;
        component.groupId = 1;
        fixture.detectChanges();
    }));

    describe('Create component', () => {
        it('should create', () => {
            expect(component).toBeTruthy();
        });
    });

    describe('Stamp block', () => {
        it('Stamp is empty', fakeAsync(() => {
            const req = httpTestingController.expectOne(
                '../rest/resourcesList/users/1/groups/1/baskets/1/actions?resId=100'
            );
            component.userStamp = null;
            req.flush({
                actions: [
                    {
                        id: 100,
                        label: 'test',
                        categories: [],
                        component: 'testComponent',
                    },
                    {
                        id: 101,
                        label: 'test2',
                        categories: [],
                        component: 'test2Component',
                    },
                ],
            });
            fixture.detectChanges();
            expect(fixture.debugElement.nativeElement.querySelector('.no-stamp')).toBeTruthy();
            expect(fixture.debugElement.nativeElement.querySelector('.sign-button')).toBeFalsy();
        }));
    });
});
