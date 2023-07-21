import { ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { TranslateLoader, TranslateModule, TranslateService, TranslateStore } from '@ngx-translate/core';
import { RouterTestingModule } from '@angular/router/testing';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { FoldersService } from '@appRoot/folder/folders.service';
import { PrivilegeService } from '@service/privileges.service';
import { DatePipe } from '@angular/common';
import { AdministrationService } from '@appRoot/administration/administration.service';
import { Observable, of } from 'rxjs';
import { BrowserModule } from '@angular/platform-browser';
import * as langFrJson from '../../../../lang/lang-fr.json';
import { SharedModule } from '@appRoot/app-common.module';
import { ForgotPasswordComponent } from './forgotPassword.component';
import { Router } from '@angular/router';

class FakeLoader implements TranslateLoader {
  getTranslation(lang: string): Observable<any> {
      return of({ lang: langFrJson });
  }
}

describe('Forgot password component', () => {
    let translateService: TranslateService;
    let component: ForgotPasswordComponent;
    let fixture: ComponentFixture<ForgotPasswordComponent>;
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
                })
            ],
            providers: [
                TranslateService,
                TranslateStore,
                FoldersService,
                PrivilegeService,
                DatePipe,
                AdministrationService
            ],
            declarations: [ForgotPasswordComponent]
        }).compileComponents();

        // Set lang
        translateService = TestBed.inject(TranslateService);
        translateService.use('fr');
    });

    beforeEach(fakeAsync(() => {
        httpTestingController = TestBed.inject(HttpTestingController); // Initialize HttpTestingController
        fixture = TestBed.createComponent(ForgotPasswordComponent); // Initialize ForgotPasswordComponent
        component = fixture.componentInstance;
        fixture.detectChanges();
        expect(component).toBeTruthy();
    }));

    afterEach(() => {
        httpTestingController.verify(); // Verify that there are no outstanding HTTP requests
    });

    describe('Set login', () => {
        it('focus on login', () => {
            const nativeElement = fixture.nativeElement;
            const login = nativeElement.querySelector('input[name=login]');
            expect(login).toBeTruthy();
            expect(login.getAttributeNode('autofocus')).toBeTruthy();
            expect(login.getAttributeNode('autofocus').specified).toBeTrue();
        });

        fit('set login', fakeAsync(() => {
            const nativeElement = fixture.nativeElement;
            const login = nativeElement.querySelector('input[name=login]');

            fixture.detectChanges();

            expect(login).toBeTruthy();

            login.value = 'bbain';

            // Trigger an input event to notify Angular of the value change
            login.dispatchEvent(new Event('input'));

            fixture.detectChanges();

            // Verify that the login input field now has the expected value
            expect(login.value).toBe('bbain');

            component.generateLink();

            tick(300);

            // Now, check that the navigation was triggered
            const router = TestBed.inject(Router);
            const navigateSpy = spyOn(router, 'navigate');

            // Check if navigation is called with the correct route
            expect(navigateSpy).toHaveBeenCalledWith(['/login']);

            // Handle the POST request and provide a mock response
            httpTestingController = TestBed.inject(HttpTestingController);
            const req = httpTestingController.expectOne('../rest/password');
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual({ login: login.value }); // Add the request body
            req.flush({}); // Provide a mock response
        }));
    });
});