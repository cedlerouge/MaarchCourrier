import { ComponentFixture, TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

import { CoreDialogComponent } from './core-dialog.component';
import { MatDialogRef } from '@angular/material/dialog';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { AppService } from '@service/app.service';
import { AuthService } from '@service/auth.service';
import { HeaderService } from '@service/header.service';
import { TranslateService, TranslateStore } from '@ngx-translate/core';
import { InternationalizationModule } from '@service/translate/internationalization.module';
import { FoldersService } from '@appRoot/folder/folders.service';
import { AppMaterialModule } from '@appRoot/app-material.module';
import { NotificationService } from '@service/notification/notification.service';
import { PrivilegeService } from '@service/privileges.service';
import { LatinisePipe } from 'ngx-pipes';
import { DatePipe } from '@angular/common';
import { AdministrationService } from '@appRoot/administration/administration.service';

describe('CoreDialogComponent', () => {
    let component: CoreDialogComponent;
    let fixture: ComponentFixture<CoreDialogComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [InternationalizationModule, AppMaterialModule, RouterTestingModule, BrowserAnimationsModule, HttpClientTestingModule],
            providers: [
                AuthService,
                HeaderService,
                TranslateService,
                TranslateStore,
                AppService,
                FoldersService,
                NotificationService,
                PrivilegeService,
                LatinisePipe,
                DatePipe,
                AdministrationService,
                { provide: MatDialogRef, useValue: {} }
            ],
            declarations: [CoreDialogComponent]
        }).compileComponents();
    });

    beforeEach(() => {
        fixture = TestBed.createComponent(CoreDialogComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    fit('should create', () => {
        expect(component).toBeTruthy();
    });
});
