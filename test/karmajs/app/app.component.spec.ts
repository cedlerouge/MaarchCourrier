import { TestBed, fakeAsync, flush } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { AppComponent } from '../../../src/frontend/app/app.component';
import { MissingTranslationHandler, TranslateCompiler, TranslateLoader, TranslateModule, TranslateParser, TranslateService, TranslateStore } from "@ngx-translate/core";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { SharedModule } from '../../../src/frontend/app/app-common.module';
import { HeaderService } from '../../../src/frontend/service/header.service';
import { AuthService } from '../../../src/frontend/service/auth.service';
import { FoldersService } from '../../../src/frontend/app/folder/folders.service';
import { PrivilegeService } from '../../../src/frontend/service/privileges.service';
import { DatePipe } from '@angular/common';
import { AdministrationService } from '../../../src/frontend/app/administration/administration.service';
import { Observable, of } from 'rxjs';
import { AppService } from '../../../src/frontend/service/app.service';
import { CoreDialogComponent } from '../../../src/frontend/app/core-dialog/core-dialog.component';
import * as langFrJson from '../../../src/lang/lang-fr.json';


class FakeLoader implements TranslateLoader {
    getTranslation(): Observable<any> {
        return of({ lang: langFrJson });
    }
}

describe('AppComponent', () => {
    let httpCtl: HttpTestingController;
    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [
                SharedModule,
                RouterTestingModule,
                SharedModule,
                BrowserAnimationsModule,
                HttpClientTestingModule,
                TranslateModule.forRoot({
                    loader: { provide: TranslateLoader, useClass: FakeLoader },
                }),
            ],
            providers: [
                TranslateService,
                TranslateStore,
                HeaderService,
                AuthService,
                FoldersService,
                PrivilegeService,
                DatePipe,
                AdministrationService,
                TranslateLoader,
                TranslateCompiler,
                TranslateParser,
                MissingTranslationHandler
            ],
            declarations: [
                AppComponent, CoreDialogComponent
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
        TestBed.inject(AppService).coreLoaded = true;
        const fixture = TestBed.createComponent(AppComponent);
        const compiled = fixture.nativeElement;
        fixture.detectChanges();
        expect(compiled.querySelector('.maarch-container')).toBeTruthy();
    });
});
