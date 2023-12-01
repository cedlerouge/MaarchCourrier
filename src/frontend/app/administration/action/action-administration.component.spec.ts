import { TestBed, ComponentFixture, tick, fakeAsync, flush } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { TranslateLoader, TranslateModule, TranslateService } from '@ngx-translate/core';
import { ActionAdministrationComponent } from './action-administration.component';
import { ActivatedRoute, Router } from '@angular/router';
import { BehaviorSubject, Observable, of, throwError } from 'rxjs';
import { NotificationService } from '@service/notification/notification.service';
import { HeaderService } from '@service/header.service';
import { AppService } from '@service/app.service';
import { ActionPagesService } from '@service/actionPages.service';
import { FunctionsService } from '@service/functions.service';
import { RouterTestingModule } from '@angular/router/testing';
import { Component } from '@angular/core';
import { ReactiveFormsModule, UntypedFormControl, UntypedFormGroup, Validators } from '@angular/forms';
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
    let notificationService: NotificationService;
    const params = new BehaviorSubject({ id: 1 });

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
        notificationService = TestBed.inject(NotificationService);
        fixture = TestBed.createComponent(ActionAdministrationComponent);
        component = fixture.componentInstance;
        component.loading = false;
        fixture.detectChanges();
    });

    describe('Create component', () => {
        it('should create component', () => {
            expect(component).toBeTruthy();
        });
    });

    describe('Check validty of actionsFormUp', () => {
        it('should be valid when actionsFormUp is valid', () => {
            component.loading = false;
            fixture.detectChanges();
            const actionsForm = new UntypedFormGroup({
                label_action: new UntypedFormControl('Action de test', Validators.required),
                actionPageId: new UntypedFormControl('confirm_status', Validators.required)
            });
        
            component.actionsFormUp.form = actionsForm;
        
            expect(component.actionsFormUp.form.controls['actionPageId'].valid).toBeTruthy();
            expect(component.actionsFormUp.form.valid).toBeTruthy();
        });
        
        it('should be invalid when actionsFormUp is invalid', () => {
            component.loading = false;
            fixture.detectChanges();
            const actionsForm = new UntypedFormGroup({
                label_action: new UntypedFormControl('Action de test', Validators.required),
                actionPageId: new UntypedFormControl('', Validators.required)
            });
        
            component.actionsFormUp.form = actionsForm;
        
            expect(component.actionsFormUp.form.controls['actionPageId'].valid).toBeFalsy();
            expect(component.actionsFormUp.form.valid).toBeFalsy();
        });
    });

    describe('Create/Update action', () => {
        it('should handle form submission for new action and show success notification after submission', fakeAsync(() => {
            component.creationMode = true;
            component.loading = false;
    
            fixture.detectChanges();
            tick(300);
    
            loadValues(component, fixture);
    
            const nativeElement = fixture.nativeElement;
            const name = nativeElement.querySelector('input[name=action_name]');
            const submit = nativeElement.querySelector('button[type=submit]');
    
            expect(name).toBeDefined();
            expect(submit.disabled).toBeTrue();  
    
            name.dispatchEvent(new Event('input'));
            name.value = component.action.label_action;
    
            component.selectActionPageId.setValue('confirm_status');
            component.selectStatusId.setValue('_NOSTATUS_');
    
            fixture.detectChanges();
            tick(300);
    
            expect(name.value).toEqual('Action de test');
    
            fixture.detectChanges();
            tick(300);
    
            expect(nativeElement.querySelector(`#actionPageId[ng-reflect-value=${component.selectActionPageId.value}]`)).toBeDefined();
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
    
        it('should handle form submission for existing action modification and show success notification', fakeAsync(() => {
            component.creationMode = false;
            component.loading = false;
            
            fixture.detectChanges();
            tick(300);
    
            loadValues(component, fixture);
    
            const nativeElement = fixture.nativeElement;
            const name = nativeElement.querySelector('input[name=action_name]');
            const submit = nativeElement.querySelector('button[type=submit]');
    
            expect(name).toBeDefined();
            expect(submit.disabled).toBeTruthy()
            
            fixture.detectChanges();
            tick(300);
    
            name.dispatchEvent(new Event('input'));
            name.value = 'Action de test modifié';
    
            component.action.label_action = name.value
            component.selectActionPageId.setValue('confirm_status');
            component.selectStatusId.setValue('_NOSTATUS_');
            
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
    });

    describe('Handle error if system action doesn t exist', () => {
        it('should handle form submission failure and show error notification', fakeAsync(() => {
            component.creationMode = true; // Ou false selon le cas que vous voulez tester
            component.loading = false;
    
            fixture.detectChanges();
            tick(300);
    
            loadValues(component, fixture);
        
            fixture.detectChanges();
            tick(300);
        
            const errorMessage: string = "System action doesn't exist";
            spyOn(component['http'], 'post').and.returnValue(throwError(errorMessage));
            const errorSpy = spyOn(notificationService, 'error');
            const navigateSpy = spyOn(TestBed.inject(Router), 'navigate');
        
            component.onSubmit();
            
            tick(300);
        
            fixture.whenStable().then(() => {            
                expect(component['http'].post).toHaveBeenCalledWith('../rest/actions', component.action);
                expect(errorSpy).toHaveBeenCalledWith(errorMessage);
                expect(navigateSpy).not.toHaveBeenCalled();
            });
        
            tick();
        }));
    });
});

function loadValues(component: ActionAdministrationComponent, fixture: ComponentFixture<ActionAdministrationComponent>) {
    component.actionPages = TestBed.inject(ActionPagesService).getAllActionPages();

    component.statuses = [
        {
            id: '_NOSTATUS_',
            label: 'Inchangé'
        },
        {
            id: 'ATT',
            label: 'En attente'
        },
        {
            id: 'COU',
            label: 'En cours'
        }
    ];

    component.categoriesList = [
        {
            id: 'incoming',
            label: 'Courrier arrivée'
        },
        {
            id: 'outgoing',
            label: 'Courrier départ'
        },
        {
            id: 'internal',
            label: 'Note interne'
        }
    ];

    component.action = {
        id: 1,
        actionCategories: ['incoming', 'outgoing', 'internal'],
        actionPageGroup: 'application',
        actionPageId: 'confirm_status',
        action_page: 'confirm_status',
        component: 'confirmAction',
        label_action: 'Action de test',
        id_status: '_NOSTATUS_',
        keyword: '',
        history: true,
        parameters: { fillRequiredFields: [] }
    };

    fixture.detectChanges();
    tick(400);
}
