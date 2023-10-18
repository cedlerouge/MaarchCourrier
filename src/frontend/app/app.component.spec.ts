import { TestBed, fakeAsync, flush } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { AppComponent } from './app.component';
import { TranslateService, TranslateStore } from "@ngx-translate/core";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { SharedModule } from './app-common.module';
import { HeaderService } from '@service/header.service';
import { AuthService } from '@service/auth.service';
import { FoldersService } from './folder/folders.service';
import { PrivilegeService } from '@service/privileges.service';
import { DatePipe } from '@angular/common';
import { AdministrationService } from './administration/administration.service';

describe('AppComponent', () => {
    let httpCtl: HttpTestingController;
    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [
                RouterTestingModule, 
                SharedModule, 
                BrowserAnimationsModule,
                HttpClientTestingModule
            ],
            providers: [
                TranslateService,
                TranslateStore,
                HeaderService,
                AuthService,
                FoldersService,
                PrivilegeService,
                DatePipe,
                AdministrationService
            ],
            declarations: [
                AppComponent,
            ]
        }).compileComponents();
        httpCtl = TestBed.inject(HttpTestingController);
    });

    it('should create the app', fakeAsync(() => {
        const component = TestBed.createComponent(AppComponent);
        const app = component.componentInstance;
        httpCtl.match('*');
        flush();
        expect(app).toBeTruthy();
    }));

    it('should render title', () => {
        const fixture = TestBed.createComponent(AppComponent);
        fixture.detectChanges();
        const compiled = fixture.nativeElement;
        expect(compiled.querySelector('.maarch-container')).toBeTruthy();
    });
});
