import { ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { TranslateLoader, TranslateModule, TranslateService, TranslateStore } from '@ngx-translate/core';
import { RouterTestingModule } from '@angular/router/testing';
import { SharedModule } from '../../app/app-common.module';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { LoginComponent } from '@appRoot/login/login.component';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { FoldersService } from '@appRoot/folder/folders.service';
import { PrivilegeService } from '@service/privileges.service';
import { DatePipe } from '@angular/common';
import { AdministrationService } from '@appRoot/administration/administration.service';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { MatIconRegistry } from '@angular/material/icon';
import { BrowserModule, DomSanitizer } from '@angular/platform-browser';
import * as langFrJson from '../../../lang/lang-fr.json';

class FakeLoader implements TranslateLoader {
  getTranslation(lang: string): Observable<any> {
      return of({ lang: langFrJson });
  }
}

describe('LoginComponent', () => {
  let component: LoginComponent;
  let fixture: ComponentFixture<LoginComponent>;
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
      })
      ],
      providers: [
        TranslateService,
        TranslateStore,
        FoldersService,
        PrivilegeService,
        DatePipe,
        AdministrationService,
        HttpClient,
        MatIconRegistry,
        // DomSanitizer
        {
          provide: DomSanitizer,
          useValue: {
            bypassSecurityTrustResourceUrl: (url: string) => url,
            bypassSecurityTrustHtml: (html: string) => html,
            sanitize: (ctx: any, val: string) => val,
          },
        },
      ],
      declarations: [LoginComponent]
    }).compileComponents();

    // Set lang
    translateService = TestBed.inject(TranslateService);
    translateService.use('fr');
  });

  beforeEach(fakeAsync(() => {
    // TO DO : Set maarchLogoFull SVG
    const iconRegistry = TestBed.inject(MatIconRegistry);
    const sanitizer = TestBed.inject(DomSanitizer);
    const url: string = '../rest/images?image=logo';
    tick(300);
    iconRegistry.addSvgIcon('maarchLogoFull', sanitizer.bypassSecurityTrustResourceUrl(url));

    httpTestingController = TestBed.inject(HttpTestingController); // Initialize HttpTestingController

    fixture = TestBed.createComponent(LoginComponent); // Initialize LoginComponent
    component = fixture.componentInstance;
    fixture.detectChanges();
    expect(component).toBeTruthy();
  }));

  afterEach(() => {
    httpTestingController.verify(); // Verify that there are no outstanding HTTP requests
  });

  describe('Login test', () => {
    it('focus on login and password inputs', () => {
      const nativeElement = fixture.nativeElement;
      const login = nativeElement.querySelector('input[name=login]');
      const password = nativeElement.querySelector('input[name=password]');
      expect(login).toBeTruthy();
      expect(password).toBeTruthy();
      expect(login.getAttributeNode('autofocus')).toBeTruthy();
      expect(login.getAttributeNode('autofocus').specified).toBeTrue();
    });
  
    it('set login and password', fakeAsync(() => {
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

      tick(300);

      // Handle the POST request and provide a mock response
      httpTestingController = TestBed.inject(HttpTestingController);
      const req = httpTestingController.expectOne('../rest/authenticate');
      expect(req.request.method).toBe('POST');
      expect(req.request.body).toEqual({ login: login.value, password: password.value }); // Add the request body
      req.flush({}); // Provide a mock response
    }));
  });
});
