import { ComponentFixture, TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { CoreDialogComponent } from '../../../src/frontend/app/core-dialog/core-dialog.component';
import { MatDialogRef } from '@angular/material/dialog';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { AppService } from '../../../src/frontend/service/app.service';
import { AuthService } from '../../../src/frontend/service/auth.service';
import { HeaderService } from '../../../src/frontend/service/header.service';
import { TranslateService, TranslateStore } from '@ngx-translate/core';
import { InternationalizationModule } from '../../../src/frontend/service/translate/internationalization.module';
import { FoldersService } from '../../../src/frontend/app/folder/folders.service';
import { AppMaterialModule } from '../../../src/frontend/app/app-material.module';
import { NotificationService } from '../../../src/frontend/service/notification/notification.service';
import { PrivilegeService } from '../../../src/frontend/service/privileges.service';
import { LatinisePipe } from 'ngx-pipes';
import { DatePipe } from '@angular/common';
import { AdministrationService } from '../../../src/frontend/app/administration/administration.service';
import { SharedModule } from '../../../src/frontend/app/app-common.module';

describe('CoreDialogComponent', () => {
    let component: CoreDialogComponent;
    let fixture: ComponentFixture<CoreDialogComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [
                SharedModule,
                InternationalizationModule,
                AppMaterialModule,
                RouterTestingModule,
                BrowserAnimationsModule,
                HttpClientTestingModule
            ],
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

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
