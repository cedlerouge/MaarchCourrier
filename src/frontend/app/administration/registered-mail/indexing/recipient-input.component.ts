import { Component, OnInit, Input } from '@angular/core';
import { FormControl, FormGroup, Validators, FormBuilder } from '@angular/forms';
import { Observable } from 'rxjs/internal/Observable';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { NotificationService } from '../../../../service/notification/notification.service';
import { tap, catchError, debounceTime, filter, distinctUntilChanged, switchMap } from 'rxjs/operators';
import { of } from 'rxjs/internal/observable/of';
import { LatinisePipe } from 'ngx-pipes';

@Component({
    selector: 'app-registered-mail-recipient-input',
    templateUrl: './recipient-input.component.html',
    styleUrls: ['./recipient-input.component.scss']
})
export class RegisteredMailRecipientInputComponent implements OnInit {

    /**
     * FormControl used when autocomplete is used in form and must be catched in a form control.
     */
    @Input() control: FormControl;

    manualAddress: boolean = false;
    civilities: any[] = [];
    addressBANInfo: string = '';
    addressBANMode: boolean = true;
    addressBANControl = new FormControl();
    addressBANLoading: boolean = false;
    addressBANResult: any[] = [];
    addressBANFilteredResult: Observable<string[]>;
    addressBANCurrentDepartment: string = '75';
    departmentList: any[] = [];

    constructor(
        public translate: TranslateService,
        public http: HttpClient,
        private notify: NotificationService,
        private latinisePipe: LatinisePipe,
    ) { }

    ngOnInit(): void {

        this.getCivilities();
        this.initBanSearch();
        this.initAutocompleteAddressBan();
        if (this.control.value === null) {
            this.control.setValue({});
        }
    }

    getCivilities() {
        this.http.get('../rest/civilities').pipe(
            tap((data: any) => {
                Object.keys(data.civilities).forEach(element => {
                    this.civilities.push({
                        id: element,
                        label: data.civilities[element].label.toUpperCase()
                    });
                });
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initBanSearch() {
        this.http.get('../rest/ban/availableDepartments').pipe(
            tap((data: any) => {
                if (data.default !== null && data.departments.indexOf(data.default.toString()) !== - 1) {
                    this.addressBANCurrentDepartment = data.default;
                }
                this.departmentList = data.departments;
            }),
            catchError((err: any) => {
                this.notify.handleSoftErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    initAutocompleteAddressBan() {
        this.addressBANInfo = this.translate.instant('lang.autocompleteInfo');
        this.addressBANResult = [];
        this.addressBANControl.valueChanges
            .pipe(
                debounceTime(300),
                filter(value => value.length > 2),
                distinctUntilChanged(),
                tap(() => this.addressBANLoading = true),
                switchMap((data: any) => this.http.get('../rest/autocomplete/banAddresses', { params: { 'address': data, 'department': this.addressBANCurrentDepartment } })),
                tap((data: any) => {
                    if (data.length === 0) {
                        this.addressBANInfo = this.translate.instant('lang.noAvailableValue');
                    } else {
                        this.addressBANInfo = '';
                    }
                    this.addressBANResult = data;
                    this.addressBANFilteredResult = of(this.addressBANResult);
                    this.addressBANLoading = false;
                })
            ).subscribe();
    }

    resetAutocompleteAddressBan() {
        this.addressBANResult = [];
        this.addressBANInfo = this.translate.instant('lang.autocompleteInfo');
    }

    selectAddressBan(ev: any) {
        this.control.value.addressNumber = ev.option.value.number;
        this.control.value.addressStreet = ev.option.value.afnorName;
        this.control.value.addressPostcode = ev.option.value.postalCode;
        this.control.value.addressTown = ev.option.value.city;
        this.control.value.addressCountry = 'FRANCE';

        this.addressBANControl.setValue('');
    }

    getFormatedAdress() {
        const formatedAddress = {};
        Object.keys(this.control.value).forEach(key => {
            formatedAddress[key] = this.control.value[key];
        });
        return formatedAddress;
    }

    emptyAddress() {
        let state: boolean = true;
        Object.keys(this.control.value).forEach(key => {
            if (this.control.value[key] !== '') {
                state = false;
            }
        });
        return state;
    }

    toUpperCase(target: string, ev: any) {
        setTimeout(() => {
            const test = this.latinisePipe.transform(this.control.value[target].toUpperCase());
            this.control.value[target] = test;
        }, 100);
    }

    goTo() {
        window.open(`https://www.google.com/maps/search/${this.control.value.addressNumber}+${this.control.value.addressStreet},+${this.control.value.addressPostcode}+${this.control.value.addressTown},+${this.control.value.addressCountry}`, '_blank');
    }
}