import { ComponentFixture, TestBed, fakeAsync, flush, tick } from '@angular/core/testing';
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
import { Router } from '@angular/router';
import { ResetPasswordComponent } from './reset-password.component';


class FakeLoader implements TranslateLoader {
  getTranslation(lang: string): Observable<any> {
      return of({ lang: langFrJson });
  }
}

describe('Reset password component', () => {
    let translateService: TranslateService;
    let component: ResetPasswordComponent;
    let fixture: ComponentFixture<ResetPasswordComponent>;
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
                loader: { provide: TranslateLoader, useClass: FakeLoader }})
            ],
            providers: [
                TranslateService,
                TranslateStore,
                FoldersService,
                PrivilegeService,
                DatePipe,
                AdministrationService
            ],
            declarations: [ResetPasswordComponent]
        }).compileComponents();

        // Set lang
        translateService = TestBed.inject(TranslateService);
        translateService.use('fr');
    });

    beforeEach(fakeAsync(() => {
        httpTestingController = TestBed.inject(HttpTestingController); // Initialize HttpTestingController
        fixture = TestBed.createComponent(ResetPasswordComponent); // Initialize ResetPasswordComponent
        component = fixture.componentInstance;
        fixture.detectChanges();
        expect(component).toBeTruthy();

        fixture.whenStable().then(() => {
            // Handle the POST request and provide a mock response
            httpTestingController = TestBed.inject(HttpTestingController);
            const req = httpTestingController.expectOne('../rest/passwordRules');
            expect(req.request.method).toBe('GET');
            req.flush({}); // Provide a mock response
            // Advance the fakeAsync timer to complete the HTTP request
            tick(300);
          });
    }));

    afterEach(() => {
        httpTestingController.verify(); // Verify that there are no outstanding HTTP requests
    });

    it('focus on password', () => {
        const nativeElement = fixture.nativeElement;
        const newPassword = nativeElement.querySelector('input[name=newPassword]');
        const passwordConfirmation = nativeElement.querySelector('input[name=passwordConfirmation]');
        expect(newPassword).toBeTruthy();
        expect(passwordConfirmation).toBeTruthy();
        expect(newPassword.getAttributeNode('autofocus')).toBeTruthy();
        expect(newPassword.getAttributeNode('autofocus').specified).toBeTrue();
    });

    it('set password', fakeAsync(() => {
        const nativeElement = fixture.nativeElement;
        const newPassword = nativeElement.querySelector('input[name=newPassword]');
        const passwordConfirmation = nativeElement.querySelector('input[name=passwordConfirmation]');

        fixture.detectChanges();
        
        expect(newPassword).toBeTruthy();
        expect(passwordConfirmation).toBeTruthy();

        // Set the value of the password input fields
        newPassword.value = 'maarch';
        passwordConfirmation.value = 'maarch';

        // Trigger an input event to notify Angular of the value change
        newPassword.dispatchEvent(new Event('input'));
        passwordConfirmation.dispatchEvent(new Event('input'));

        fixture.detectChanges();

        // Verify that the login input field now has the expected value
        expect(newPassword.value).toBe('maarch');
        expect(passwordConfirmation.value).toBe('maarch');

        component.updatePassword();

      // Use whenStable() to wait for all pending asynchronous activities to complete
      fixture.whenStable().then(() => {
        // Check that the navigation was triggered
        const router = TestBed.inject(Router);
        const navigateSpy = spyOn(router, 'navigate');

        // Handle the POST request and provide a mock response
        httpTestingController = TestBed.inject(HttpTestingController);
        const req = httpTestingController.expectOne('../rest/password');

        expect(req.request.method).toBe('PUT');
        expect(req.request.body).toEqual({ token: component.token, password: newPassword.value }); // Add the request body
        req.flush({}); // Provide a mock response

        setTimeout(() => {
          // Check if navigation is called with the correct route
          expect(navigateSpy).toHaveBeenCalledWith(['/login']);
        }, 500);
        // Advance the fakeAsync timer to complete the HTTP request
        tick(300);
        // Flush any pending asynchronous tasks
        flush();
      });
    }));
});