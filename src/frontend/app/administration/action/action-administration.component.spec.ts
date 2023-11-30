import { TestBed, ComponentFixture, tick, fakeAsync } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
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
        notificationService = TestBed.inject(NotificationService);
        fixture = TestBed.createComponent(ActionAdministrationComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create component', () => {
        expect(component).toBeTruthy();
    });

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

    it('should handle form submission for confirm_status action', fakeAsync(() => {
        component.creationMode = true;
        component.loading = false;
        component.action = {
            actionCategories: ['incoming', 'outgoing', 'internal', 'ged_doc', 'registeredMail'],
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


        tick(100);
        fixture.detectChanges();

        spyOn(component['http'], 'post').and.returnValue(of(true));
        const successSpy = spyOn(notificationService, 'success');
        const navigateSpy = spyOn(TestBed.inject(Router), 'navigate');

        component.onSubmit();
        
        tick(300);

        fixture.whenStable().then(() => {            
            expect(component['http'].post).toHaveBeenCalledWith('../rest/actions', component.action);
            expect(navigateSpy).toHaveBeenCalledWith(['/administration/actions']);
            expect(successSpy).toHaveBeenCalledWith(component.translate.instant('lang.actionAdded'));
        });

        tick();
    }));

    it('should handle form submission for existing action modification and show success notification', fakeAsync(() => {
        component.creationMode = false;
        component.loading = false;
        component.action = {
            id: 1,
            actionCategories: ['incoming', 'outgoing', 'internal', 'ged_doc', 'registeredMail'],
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


        tick(100);
        fixture.detectChanges();

        spyOn(component['http'], 'put').and.returnValue(of(true));
        const successSpy = spyOn(notificationService, 'success');
        const navigateSpy = spyOn(TestBed.inject(Router), 'navigate');

        component.onSubmit();
        
        tick(300);

        fixture.whenStable().then(() => {            
            expect(component['http'].put).toHaveBeenCalledWith(`../rest/actions/${component.action.id}`, component.action);
            expect(navigateSpy).toHaveBeenCalledWith(['/administration/actions']);
            expect(successSpy).toHaveBeenCalledWith(component.translate.instant('lang.actionUpdated'));
        });

        tick();
    }));

    it('should handle form submission failure and show error notification', fakeAsync(() => {
        component.creationMode = true; // Ou false selon le cas que vous voulez tester
        component.loading = false;
        component.action = {
            id: 1,
            actionCategories: ['incoming', 'outgoing', 'internal', 'ged_doc', 'registeredMail'],
            actionPageGroup: 'application',
            actionPageId: null,
            action_page: null,
            component: 'confirmAction',
            label_action: 'Action de test',
            id_status: '_NOSTATUS_',
            keyword: '',
            history: true,
            parameters: { fillRequiredFields: [] }
        };
    
        tick(100);
        fixture.detectChanges();
    
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
