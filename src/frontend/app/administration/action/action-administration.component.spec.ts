import { TestBed, ComponentFixture, tick, fakeAsync, flush } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { TranslateLoader, TranslateModule, TranslateService } from '@ngx-translate/core';
import { ActionAdministrationComponent } from './action-administration.component';
import { ActivatedRoute, Router } from '@angular/router';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { AppService } from '@service/app.service';
import { ActionPagesService } from '@service/actionPages.service';
import { FunctionsService } from '@service/functions.service';
import { RouterTestingModule } from '@angular/router/testing';
import { Component } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDialogModule } from '@angular/material/dialog';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatInputModule } from '@angular/material/input';
import { MatListModule } from '@angular/material/list';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { SharedModule } from '@appRoot/app-common.module';
import { FoldersService } from '@appRoot/folder/folders.service';
import { PrivilegeService } from '@service/privileges.service';
import { DatePipe } from '@angular/common';
import { AdministrationService } from '../administration.service';
import * as langFrJson from '../../../../lang/lang-fr.json';

@Component({ template: '' })
class DummyComponent {}

class FakeLoader implements TranslateLoader {
    getTranslation(): Observable<any> {
        return of({ lang: langFrJson });
    }
}

describe('ActionAdministrationComponent', () => {
    let httpTestingController: HttpTestingController;
    let component: ActionAdministrationComponent;
    let fixture: ComponentFixture<ActionAdministrationComponent>;
    let translateService: TranslateService;
    const params = new BehaviorSubject({});

    beforeEach(() => {
        TestBed.configureTestingModule({
            imports: [
                ReactiveFormsModule,
                MatCardModule,
                MatInputModule,
                MatExpansionModule,
                MatSlideToggleModule,
                MatButtonModule,
                MatDialogModule,
                HttpClientTestingModule,
                RouterTestingModule,
                BrowserAnimationsModule,
                MatSidenavModule,
                MatListModule,
                SharedModule,
                TranslateModule.forRoot({
                    loader: { provide: TranslateLoader, useClass: FakeLoader },
                }),
                RouterTestingModule.withRoutes([{ path: 'administration/actions', component: DummyComponent }]),
            ],
            declarations: [ActionAdministrationComponent],
            providers: [
                { provide: ActivatedRoute, useValue: { params: params.asObservable() } },
                NotificationService,
                HeaderService,
                AppService,
                FunctionsService,
                ActionPagesService,
                FoldersService,
                PrivilegeService,
                DatePipe,
                AdministrationService
            ]
        }).compileComponents();

        // Set lang
        translateService = TestBed.inject(TranslateService);
        translateService.use('fr');
    });

    beforeEach(() => {
        httpTestingController = TestBed.inject(HttpTestingController);
        fixture = TestBed.createComponent(ActionAdministrationComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    describe('Create component', () => {
        it('should create component', () => {
            expect(component).toBeTruthy();
        });
    });

    describe('Check validty of actionsFormUp', () => {
        it('should be valid when actionsFormUp is valid', fakeAsync(() => {
            component.loading = false;
            fixture.detectChanges();
            component.action.label_action = 'Action de test';
            component.action.actionPageId = 'actionTest';
            fixture.detectChanges();
            expect(component.actionsFormUp.form.valid).toBeTruthy();
        }));

        it('should be invalid when actionsFormUp is invalid', fakeAsync(() => {
            component.loading = false;
            fixture.detectChanges();
            tick(100);
            component.action.label_action = null;
            fixture.detectChanges();
            expect(component.actionsFormUp.valid).toBeFalsy();
        }));
    });

    describe('Create action', () => {
        it('should handle form submission for new action and show success notification after submission', fakeAsync(() => {
            const initActionRes = httpTestingController.expectOne('../rest/initAction');
            expect(initActionRes.request.method).toBe('GET');
            initActionRes.flush(initAction());

            fixture.detectChanges();

            const customFieldReq = httpTestingController.expectOne('../rest/customFields');
            expect(customFieldReq.request.method).toBe('GET');
            customFieldReq.flush({
                customFields: [
                    {
                        "id": 2,
                        "label": "Adresse d'intervention",
                        "type": "banAutocomplete",
                        "mode": "form",
                        "values": [],
                        "SQLMode": false
                    },
                    {
                        "id": 13,
                        "label": "Contact",
                        "type": "contact",
                        "mode": "form",
                        "values": [],
                        "SQLMode": false
                    },
                ]
            });

            tick(100);
            fixture.detectChanges();
            flush();

            const nativeElement = fixture.nativeElement;
            const name = nativeElement.querySelector('input[name=action_name]');
            const submit = nativeElement.querySelector('button[type=submit]');

            expect(name).toBeDefined();

            name.dispatchEvent(new Event('input'));
            name.value = 'Action de test';

            component.selectActionPageId.setValue('confirm_status');
            component.selectStatusId.setValue('_NOSTATUS_');
            component.actionsFormUp.controls['action_name'].setValue(name.value);

            fixture.detectChanges();
            tick(300);

            expect(name.value).toEqual('Action de test');
            expect(nativeElement.querySelector('#actionPageId .mat-select-value-text').innerText).toEqual('Confirmation simple') 
            expect(submit.disabled).toBeFalse();

            fixture.detectChanges();
            tick(300);

            const navigateSpy = spyOn(TestBed.inject(Router), 'navigate');

            submit.click();

            fixture.detectChanges();
            tick(300);

            const req = httpTestingController.expectOne('../rest/actions');
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual(component.action);
            req.flush({});

            fixture.detectChanges();
            tick(300);

            const successSpy = document.querySelectorAll('.mat-snack-bar-container.success-snackbar').length;
            const notifContent = document.querySelector('.notif-container-content-msg #message-content').innerHTML;

            expect(successSpy).toEqual(1);
            expect(notifContent).toEqual(component.translate.instant('lang.actionAdded'));

            setTimeout(() => {
                expect(navigateSpy).toHaveBeenCalledWith(['/administration/actions']);
            }, 100);
            flush();
        }));
    });

    describe('Update action', () => {
        it('should handle form submission for existing action modification and show success notification', fakeAsync(() => {
            params.next({ id: 1 });

            const initActionRes = httpTestingController.expectOne('../rest/initAction');
            expect(initActionRes.request.method).toBe('GET');
            initActionRes.flush(initAction());

            params.subscribe((data: any) => {
                const actionRes = httpTestingController.expectOne('../rest/actions/' + data['id']);
                expect(actionRes.request.method).toBe('GET');
                actionRes.flush(initAction());
            });

            httpTestingController = TestBed.inject(HttpTestingController);

            const customField = httpTestingController.expectOne(req => req.method === 'GET' && req.url === '../rest/customFields');
            customField.flush({
                customFields: [
                    {
                        "id": 2,
                        "label": "Adresse d'intervention",
                        "type": "banAutocomplete",
                        "mode": "form",
                        "values": [],
                        "SQLMode": false
                    },
                    {
                        "id": 13,
                        "label": "Contact",
                        "type": "contact",
                        "mode": "form",
                        "values": [],
                        "SQLMode": false
                    },
                ]
            });

            fixture.detectChanges();
            tick(300);

            const nativeElement = fixture.nativeElement;
            const name = nativeElement.querySelector('input[name=action_name]');
            const submit = nativeElement.querySelector('button[type=submit]');

            expect(name).toBeDefined();

            fixture.detectChanges();
            tick(300);

            name.dispatchEvent(new Event('input'));
            name.value = 'Action de test modifié';

            component.action.label_action = name.value;
            component.selectActionPageId.setValue('confirm_status');
            component.selectStatusId.setValue('_NOSTATUS_');
            component.actionsFormUp.controls['action_name'].setValue(name.value);

            fixture.detectChanges();
            tick(300);


            expect(nativeElement.querySelector('#categorieslist')).toBeDefined();
            expect(submit.disabled).toBeFalse();

            fixture.detectChanges();
            tick(300);

            const navigateSpy = spyOn(TestBed.inject(Router), 'navigate');

            submit.click();

            fixture.detectChanges();
            tick(300);

            const req = httpTestingController.expectOne(req => req.method === 'PUT' && req.url === '../rest/actions/1');
            expect(req.request.body).toEqual(component.action);
            req.flush({});

            fixture.detectChanges();
            tick(300);

            const successSpy = document.querySelectorAll('.mat-snack-bar-container.success-snackbar').length;
            const notifContent = document.querySelector('.notif-container-content-msg #message-content').innerHTML;

            expect(successSpy).toEqual(1);
            expect(notifContent).toEqual(component.translate.instant('lang.actionUpdated'));

            setTimeout(() => {
                expect(navigateSpy).toHaveBeenCalledWith(['/administration/actions']);
            }, 100);
            flush();
        }));
    })
});

function initAction() {
    return {
        "action": {
            "id": 1,
            "history": true,
            "keyword": "",
            "actionPageId": "confirm_status",
            "id_status": "_NOSTATUS_",
            "parameters": {
                "lockVisaCircuit": false,
                "keepDestForRedirection": false,
                "keepCopyForRedirection": false,
                "keepOtherRoleForRedirection": false
            },
            "actionCategories": [
                "incoming",
                "outgoing",
                "internal",
                "ged_doc",
                "registeredMail"
            ]
        },
        "categoriesList": [
            {
                "id": "incoming",
                "label": "Courrier Arriv\u00e9e"
            },
            {
                "id": "outgoing",
                "label": "Courrier D\u00e9part"
            },
            {
                "id": "internal",
                "label": "Courrier Interne"
            },
            {
                "id": "ged_doc",
                "label": "Document GED"
            },
            {
                "id": "registeredMail",
                "label": "Recommand\u00e9"
            }
        ],
        "statuses": [
            {
                "id": "_NOSTATUS_",
                "label_status": "Inchang\u00e9"
            },
            {
                "identifier": 24,
                "id": "EXP_SEDA",
                "label_status": "A archiver",
                "is_system": "N",
                "img_filename": "fm-letter-status-acla",
                "maarch_module": "apps",
                "can_be_searched": "Y",
            }
        ]
    };
}
