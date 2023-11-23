import { ComponentFixture, TestBed, fakeAsync, flush, tick } from "@angular/core/testing"
import { SendExternalSignatoryBookActionComponent } from "./send-external-signatory-book-action.component";
import { HttpClientTestingModule, HttpTestingController } from "@angular/common/http/testing";
import { TranslateLoader, TranslateModule, TranslateService, TranslateStore } from "@ngx-translate/core";
import { Observable, of } from "rxjs";
import { BrowserModule, By } from "@angular/platform-browser";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { RouterTestingModule } from "@angular/router/testing";
import { SharedModule } from "@appRoot/app-common.module";
import { DatePipe } from "@angular/common";
import { AdministrationService } from "@appRoot/administration/administration.service";
import { FoldersService } from "@appRoot/folder/folders.service";
import { PrivilegeService } from "@service/privileges.service";
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from "@angular/material/dialog";
import { ExternalSignatoryBookManagerService } from "@service/externalSignatoryBook/external-signatory-book-manager.service";
import { AuthService } from "@service/auth.service";
import { IParaphComponent } from "./i-paraph/i-paraph.component";
import { AttachmentsListComponent } from "@appRoot/attachments/attachments-list.component";
import * as langFrJson from '../../../../lang/lang-fr.json';

class FakeLoader implements TranslateLoader {
    getTranslation(): Observable<any> {
        return of({ lang: langFrJson });
    }
}

describe('SendExternalSignatoryBookActionComponent', () => {
    let component: SendExternalSignatoryBookActionComponent;
    let fixture: ComponentFixture<SendExternalSignatoryBookActionComponent>;
    let translateService: TranslateService;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [
                MatDialogModule,
                SharedModule,
                RouterTestingModule,
                BrowserAnimationsModule,
                TranslateModule,
                HttpClientTestingModule,
                BrowserModule,
                TranslateModule.forRoot({
                    loader: { provide: TranslateLoader, useClass: FakeLoader },
                }),
            ],
            providers: [
                {
                    provide: MatDialogRef,
                    useValue: {}
                },
                {
                    provide: MAT_DIALOG_DATA,
                    useValue: {}
                },
                TranslateService,
                TranslateStore,
                FoldersService,
                PrivilegeService,
                DatePipe,
                AdministrationService,
                ExternalSignatoryBookManagerService,
                AttachmentsListComponent
            ],
            declarations: [SendExternalSignatoryBookActionComponent, IParaphComponent]
        }).compileComponents();

        // Set lang
        translateService = TestBed.inject(TranslateService);
        translateService.use('fr');
    });

    beforeEach(fakeAsync(() => {
        TestBed.inject(AuthService).externalSignatoryBook = { id: 'iParapheur', from: 'pastell', integratedWorkflow: false };
        fixture = TestBed.createComponent(SendExternalSignatoryBookActionComponent); // Initialize AttachmentsListComponent
        component = fixture.componentInstance;
        expect(component).toBeTruthy();
        component.data = {
            action: {
                component: 'sendExternalSignatoryBookAction',
                id: 527,
                label: 'Envoyer au parapheur externe'
            },
            additionalInfo: {
                canGoToNextRes: false,
                inLocalStorage: false,
                showToggle: false
            },
            basketId: '26',
            groupId: '2',
            indexActionRoute: '../rest/indexing/groups/2/actions/527',
            processActionRoute: '../rest/resourcesList/users/19/groups/2/baskets/26/actions/527',
            resIds: [100],
            resource: { resId: 100, chrono: 'MAARCH/2023A/1', subject: 'Courrier de test', integrations: { inSignatureBook: true } },
            usrId: '19'
        };
    }));

    describe('Load external signatory book id and set attachments', () => {
        it('Set attachments and show integrationTarget filter', fakeAsync(() => {        
            spyOn(component.externalSignatoryBook, 'checkExternalSignatureBook').and.returnValue(Promise.resolve({ availableResources: [], additionalsInfos: { attachments: [], noAttachment: [] }, errors: [] }));
        
            component.attachmentsList = TestBed.inject(AttachmentsListComponent);

            tick(300);
            expect(component.loading).toBe(false);

            fixture.detectChanges();
            tick(300);
            flush();

            loadAttachments(component, fixture);
            
        }));
    });

    it('Hide integrationTarget filter when checkbox value if false', fakeAsync(() => {
        spyOn(component.externalSignatoryBook, 'checkExternalSignatureBook').and.returnValue(Promise.resolve({ availableResources: [], additionalsInfos: { attachments: [], noAttachment: [] }, errors: [] }));
        
        component.attachmentsList = TestBed.inject(AttachmentsListComponent);

        tick(300);
        expect(component.loading).toBe(false);

        fixture.detectChanges();
        tick(300);
        flush();

        loadAttachments(component, fixture);

        component.inSignatoryBook.setValue(false);

        fixture.detectChanges();
        tick(300);

        expect(fixture.debugElement.query(By.css('mat-radio-group'))).toEqual(null);
    }));
});

function loadAttachments(component: SendExternalSignatoryBookActionComponent, fixture: ComponentFixture<SendExternalSignatoryBookActionComponent>) {
    const filterAttachTypesMock: any[] = [
        {
            id: 'response_project',
            label: 'Projet de réponse'
        },
        {
            id: 'simple_attachment',
            label: 'Pièce jointe simple'
        }
    ]

    const attachmentsMock: any[] = [
        {
            resId: 68,
            resIdMaster: 100,
            chrono: 'Chrono-PJ-68',
            typist: 19,
            title: 'Projet de réponse',
            modifiedBy: '',
            creationDate: '2023-09-26 11:19:16.882836',
            modificationDate: null,
            relation: 1,
            status: 'A_TRA',
            type: 'response_project',
            inSignatureBook: true,
            inSendAttach: false,
            external_state: [],
            typistLabel: 'Barbara BAIN',
            typeLabel: 'Projet de réponse',
            canConvert: true,
            canUpdate: true,
            canDelete: true,
            signable: true,
            hideMainInfo: false,
            thumbnail: '../rest/attachments/' + 68 + '/thumbnail'
        },
        {
            resId: 67,
            resIdMaster: 100,
            chrono: 'Chrono-PJ-67',
            title: 'Pièce jointe de test',
            typist: 19,
            modifiedBy: '',
            creationDate: '2023-09-26 11:19:16.859478',
            modificationDate: null,
            relation: 1,
            status: 'A_TRA',
            type: 'simple_attachment',
            inSignatureBook: false,
            inSendAttach: false,
            external_state: [],
            typistLabel: 'Barbara BAIN',
            typeLabel: 'Pièce jointe simple',
            canConvert: true,
            canUpdate: true,
            canDelete: true,
            signable: false,
            hideMainInfo: false,
            thumbnail: '../rest/attachments/' + 67 + '/thumbnail'
        }
    ];
    component.attachmentsList.attachments = attachmentsMock;
    component.attachmentsList.attachmentsClone = component.attachmentsList.attachments;
    component.attachmentsList.filterAttachTypes = filterAttachTypesMock;
    component.attachmentsList.loading = false;

    fixture.detectChanges();
    tick(300);
    
    fixture.debugElement.query(By.css('mat-radio-group')).nativeNode.value = component.integrationTarget;

    fixture.detectChanges();
    tick(300);
    flush();
}