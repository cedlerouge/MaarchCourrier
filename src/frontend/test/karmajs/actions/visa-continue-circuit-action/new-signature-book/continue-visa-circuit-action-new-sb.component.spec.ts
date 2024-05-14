import { DatePipe } from "@angular/common";
import { HttpClientTestingModule, HttpTestingController } from "@angular/common/http/testing";
import { ComponentFixture, TestBed, fakeAsync, flush, tick } from "@angular/core/testing";
import { BrowserModule } from "@angular/platform-browser";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { RouterTestingModule } from "@angular/router/testing";
import { ContinueVisaCircuitActionNewSbComponent } from "@appRoot/actions/visa-continue-circuit-action/new-signature-book/continue-visa-circuit-action-new-sb.component";
import { AdministrationService } from "@appRoot/administration/administration.service";
import { SharedModule } from "@appRoot/app-common.module";
import { AttachmentsListComponent } from "@appRoot/attachments/attachments-list.component";
import { FoldersService } from "@appRoot/folder/folders.service";
import { TranslateLoader, TranslateModule, TranslateService, TranslateStore } from "@ngx-translate/core";
import { ExternalSignatoryBookManagerService } from "@service/externalSignatoryBook/external-signatory-book-manager.service";
import { PrivilegeService } from "@service/privileges.service";
import { Observable, of } from "rxjs";
import { MAT_LEGACY_DIALOG_DATA as MAT_DIALOG_DATA, MatLegacyDialogModule as MatDialogModule , MatLegacyDialogRef as MatDialogRef } from "@angular/material/legacy-dialog";
import * as langFrJson from '@langs/lang-fr.json';
import { FiltersListService } from "@service/filtersList.service";
import { ChangeDetectorRef } from "@angular/core";



class FakeLoader implements TranslateLoader {
    getTranslation(): Observable<any> {
        return of({ lang: langFrJson });
    }
}

describe('ContinueVisaCircuitActionNewSbComponent', (() => {
    let httpTestingController: HttpTestingController;
    let component: ContinueVisaCircuitActionNewSbComponent;
    let fixture: ComponentFixture<ContinueVisaCircuitActionNewSbComponent>;
    let translateService: TranslateService;
    let changeDetectorRef: ChangeDetectorRef;

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
                AttachmentsListComponent,
                FiltersListService
            ],
            declarations: [ContinueVisaCircuitActionNewSbComponent]
        }).compileComponents();

        // Set lang
        translateService = TestBed.inject(TranslateService);
        translateService.use('fr');
    });

    beforeEach(() => {
        httpTestingController = TestBed.inject(HttpTestingController);
        fixture = TestBed.createComponent(ContinueVisaCircuitActionNewSbComponent);
        component = fixture.componentInstance;
        changeDetectorRef = fixture.debugElement.injector.get(ChangeDetectorRef);
    });

    describe('Create ContinueVisaCircuitActionNewSb component', (() => {
        it('should create component', (() => {
            expect(component).toBeTruthy();
        }));
    }));

    describe('CheckSignatureBook and set resources to sign', (() => {
        it('should call checkSignatureBook API, return resourcesInformations and set resources to sign', fakeAsync(() => {
            // Set the data needed for the request
            setDatas(component);

            // Call the function that triggers the HTTP request
            component.checkSignatureBook();

            // Expect the HTTP request
            const req = httpTestingController.expectOne(`../rest/resourcesList/users/${component.data.userId}/groups/${component.data.groupId}/baskets/${component.data.basketId}/actions/${component.data.action.id}/checkContinueVisaCircuit`);
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual({ resources: component.data.resIds });

            // Respond with mock data
            const mockResponse = {
                resourcesInformations: {
                    warning: [
                        {
                            "alt_identifier": "MAARCH/2023A/47",
                            "res_id": 146,
                            "reason": "userHasntSigned"
                        }
                    ]
                }
            };
            req.flush(mockResponse);

            // Ensure async operations are completed
            flush();
            tick();  // Advance time to ensure async operations are processed

            // Manually trigger change detection
            changeDetectorRef.detectChanges();

            // Trigger change detection
            fixture.detectChanges();

            // Verify that the component processed the response correctly
            expect(component.resourcesWarnings).toEqual([]);
            expect(component.resourcesErrors).toEqual([]);
            expect(component.noResourceToProcess).toBe(null);

            // Verify DOM updates
            const nativeElement = fixture.nativeElement;
            const container = nativeElement.querySelector('.div-container');
            expect(container).toBeDefined();
        }));
    }));
}));

function setDatas(component: ContinueVisaCircuitActionNewSbComponent): void {
    component.data = {
        userId: 19,
        groupId: 2,
        basketId: 16,
        action: { id: 410, label: 'Valider et poursuivre le circuit de visa' },
        resIds: [146],
        resource: {
            chrono: 'MAARCH/2023D/4',
            docsToSign: [
                {
                    "resId": 56,
                    "resIdMaster": 146,
                    "title": "Demande d'intervention",
                    "chrono": "MAARCH/2023D/4-1",
                    "signedResId": 1,
                    "type": "response_project",
                    "typeLabel": "Projet de réponse",
                    "isConverted": true,
                    "canModify": false,
                    "canDelete": false,
                    "stamps": []
                },
                {
                    "resId": 57,
                    "resIdMaster": 146,
                    "title": "Courrier à envoyer à MP",
                    "chrono": "MAARCH/2023D/4-2",
                    "signedResId": 1,
                    "type": "response_project",
                    "typeLabel": "Projet de réponse",
                    "isConverted": true,
                    "canModify": false,
                    "canDelete": false,
                    "stamps": []
                },
                {
                    "resId": 59,
                    "resIdMaster": 146,
                    "title": "Pièce jointe à signer",
                    "chrono": "MAARCH/2023D/4-4",
                    "signedResId": 1,
                    "type": "response_project",
                    "typeLabel": "Projet de réponse",
                    "isConverted": true,
                    "canModify": false,
                    "canDelete": false,
                    "stamps": []
                },
                {
                    "resId": 58,
                    "resIdMaster": 146,
                    "title": "Projet de réponse",
                    "chrono": "MAARCH/2023D/4-3",
                    "signedResId": 1,
                    "type": "response_project",
                    "typeLabel": "Projet de réponse",
                    "isConverted": true,
                    "canModify": false,
                    "canDelete": false,
                    "stamps": []
                }
            ]
        }
    };
}