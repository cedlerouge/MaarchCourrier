import { ComponentFixture, TestBed, fakeAsync, flush } from '@angular/core/testing';
import { TranslateService, TranslateStore } from '@ngx-translate/core';
import { RouterTestingModule } from '@angular/router/testing';
import { AppSharedModule } from 'src/app/shared/app-shared.module';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import { CoreDialogComponent } from './core-dialog.component';
import { EntityService } from 'src/services/entity.service';
import { MatDialogRef } from '@angular/material/dialog';
import { AppService } from 'src/services/app.service';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { ParameterService } from '../shared/services/parameter.service';

describe('CoreDialogComponent', () => {
    let component: CoreDialogComponent;
    let fixture: ComponentFixture<CoreDialogComponent>;
    let appService: AppService;
    let httpCtl: HttpTestingController;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [AppSharedModule, RouterTestingModule, BrowserAnimationsModule, HttpClientTestingModule],
            providers: [
                TranslateService,
                TranslateStore,
                AppService,
                EntityService,
                { provide: ParameterService, useValue: {} },
                { provide: MatDialogRef, useValue: {} }
            ],
            declarations: [CoreDialogComponent]
        }).compileComponents();
        httpCtl = TestBed.inject(HttpTestingController);
    });

    beforeEach(() => {
        appService = TestBed.inject(AppService);
        appService.apiURL = 'http://localhost';
        fixture = TestBed.createComponent(CoreDialogComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    describe('init config', () => {
        it('get config', fakeAsync(() => {
            httpCtl.expectOne('config/config.json');
            flush();
        }));
        it('get custom lang', fakeAsync(() => {
            httpCtl.expectOne('config/fr_custom.json');
            flush();
        }));
    });
});
