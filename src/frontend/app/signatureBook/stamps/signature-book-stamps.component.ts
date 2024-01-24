import { HttpClient } from '@angular/common/http';
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { ActionsService } from '@appRoot/actions/actions.service';
import { Stamp } from '@models/signature-book.model';
import { NotificationService } from '@service/notification/notification.service';
import { catchError, map, of, tap } from 'rxjs';

@Component({
    selector: 'app-maarch-sb-stamps',
    templateUrl: 'signature-book-stamps.component.html',
    styleUrls: ['signature-book-stamps.component.scss'],
})
export class SignatureBookStampsComponent implements OnInit {

    @Input() userId: number;

    @Output() stampsLoaded: EventEmitter<Stamp> = new EventEmitter();


    loading: boolean = true;

    stamps: Stamp[] = [];

    constructor(
        public http: HttpClient,
        private notificationService: NotificationService,
        private actionsService: ActionsService
    ) {}

    async ngOnInit(): Promise<void> {
        await this.getUserSignatures();
    }

    getUserSignatures() {
        return new Promise<boolean>((resolve) => {
            // this.http.get(`../rest/${this.userId}/stamps`).pipe(
            this.http.get<Stamp[]>(`../rest/currentUser/profile`).pipe(
                map((data: any) => {
                    let stamps : Stamp[] = data.signatures.map((sign: any) => {
                        return {
                            id: sign.id,
                            userId: sign.user_serial_id,
                            title: sign.signature_label,
                            contentUrl : `../rest/users/${this.userId}/signatures/${sign.id}/content`
                        }
                    });
                    return stamps;
                     
                }),
                tap((stamps: Stamp[]) => {
                    this.stamps = stamps;
                    this.stampsLoaded.emit(this.stamps[0] ?? null);
                    resolve(true);
                }),
                catchError((err: any) => {
                    this.notificationService.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
        })
    }

    signWithStamp(stamp: Stamp) {
        this.actionsService.emitActionWithData(stamp);
    }
}
