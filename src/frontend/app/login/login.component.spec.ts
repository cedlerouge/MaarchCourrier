import { ComponentFixture, TestBed, tick } from '@angular/core/testing';
import { TranslateModule, TranslateService, TranslateStore } from '@ngx-translate/core';
import { RouterTestingModule } from '@angular/router/testing';
import { SharedModule } from '../../app/app-common.module';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { LoginComponent } from '@appRoot/login/login.component';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing'; // Import the HttpClientTestingModule and HttpTestingController
import { FoldersService } from '@appRoot/folder/folders.service';
import { PrivilegeService } from '@service/privileges.service';
import { DatePipe } from '@angular/common';
import { AdministrationService } from '@appRoot/administration/administration.service';
import { HttpClient } from '@angular/common/http';

describe('LoginComponent', () => {
  let component: LoginComponent;
  let fixture: ComponentFixture<LoginComponent>;
  let httpTestingController: HttpTestingController; // Add HttpTestingController

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [
        SharedModule,
        RouterTestingModule,
        BrowserAnimationsModule,
        TranslateModule,
        HttpClientTestingModule // Import HttpClientTestingModule
      ],
      providers: [
        TranslateService,
        TranslateStore,
        FoldersService,
        PrivilegeService,
        DatePipe,
        AdministrationService,
        HttpClient
      ],
      declarations: [LoginComponent]
    }).compileComponents();
  });

  beforeEach(() => {
    httpTestingController = TestBed.inject(HttpTestingController); // Initialize HttpTestingController
    fixture = TestBed.createComponent(LoginComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
    expect(component).toBeTruthy();
  });

  afterEach(() => {
    httpTestingController.verify(); // Verify that there are no outstanding HTTP requests
  });

  describe('Login test', () => {
    beforeEach(() => {
      const req = httpTestingController.expectOne('../rest/languages/fr');
      expect(req.request.method).toBe('GET');
      req.flush({}); // Provide a response for the request
    });

    it('focus on login and password inputs', () => {
      const nativeElement = fixture.nativeElement;
      const login = nativeElement.querySelector('input[name=login]');
      const password = nativeElement.querySelector('input[name=password]');
      expect(login).toBeTruthy();
      expect(password).toBeTruthy();
      expect(login.getAttributeNode('autofocus')).toBeTruthy();
      expect(login.getAttributeNode('autofocus').specified).toBeTrue();
    });
  
    fit('set login and password', () => {
      const nativeElement = fixture.nativeElement;
      const login = nativeElement.querySelector('input[name=login]');
      const password = nativeElement.querySelector('input[name=password]');

      expect(login).toBeTruthy();
      expect(password).toBeTruthy();
  
      // Set the value of the login input field
      login.value = 'bbain';
      password.value = 'maarch';

      // Trigger an input event to notify Angular of the value change
      login.dispatchEvent(new Event('input'));
      password.dispatchEvent(new Event('input'));
      fixture.detectChanges();

      // Verify that the login input field now has the expected value
      expect(login.value).toBe('bbain');
      expect(password.value).toBe('maarch');
      
      component.onSubmit();

      // Handle the POST request and provide a mock response
      const req = httpTestingController.expectOne('../rest/authenticate');
      expect(req.request.method).toBe('POST');
      expect(req.request.body).toEqual({ login: login.value, password: password.value }); // Add the request body
      req.flush({}); // Provide a mock response
      
    });
  })

});
