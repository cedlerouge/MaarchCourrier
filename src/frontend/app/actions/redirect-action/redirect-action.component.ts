import { Component, OnInit, Inject, ViewChild } from '@angular/core';
import { LANG } from '../../translate.component';
import { NotificationService } from '../../notification.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { DiffusionsListComponent } from '../../diffusions/diffusions-list.component';
import { FormControl } from '@angular/forms';
import { Observable, of } from 'rxjs';
import { startWith } from 'rxjs/internal/operators/startWith';
import { map, tap, finalize, catchError } from 'rxjs/operators';
import { NoteEditorComponent } from '../../notes/note-editor.component';

declare function $j(selector: any): any;

@Component({
    templateUrl: "redirect-action.component.html",
    styleUrls: ['redirect-action.component.scss'],
    providers: [NotificationService],
})
export class RedirectActionComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    entities: any[] = [];
    injectDatasParam = {
        resId: 0,
        editable: true,
        keepDestForRedirection: false
    };
    destUser: any = null;
    oldUser: any = null;
    keepDestForRedirection: boolean = false;
    currentDiffusionListDestRedirect: any = [];
    diffusionListDestRedirect: any = [];
    currentEntity: any = {
        'serialId': 0,
        'entity_label': ''
    };
    redirectMode = '';
    userListRedirect: any[] = [];
    userRedirectCtrl = new FormControl();
    filteredUserRedirect: Observable<any[]>;
    isDestinationChanging: boolean = false;

    @ViewChild('appDiffusionsList', { static: false }) appDiffusionsList: DiffusionsListComponent;
    @ViewChild('noteEditor', { static: false }) noteEditor: NoteEditorComponent;

    constructor(public http: HttpClient, private notify: NotificationService, public dialogRef: MatDialogRef<RedirectActionComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

    ngOnInit(): void {
        let noEntity = true;
        this.loading = true;
        this.http.get("../../rest/resourcesList/users/" + this.data.userId + "/groups/" + this.data.groupId + "/baskets/" + this.data.basketId + "/actions/" + this.data.action.id + "/getRedirect")
            .subscribe((data: any) => {
                this.entities = data['entities'];
                this.userListRedirect = data.users;
                this.keepDestForRedirection = data.keepDestForRedirection;
                this.injectDatasParam.keepDestForRedirection = data.keepDestForRedirection;

                this.entities.forEach(entity => {
                    if (entity.state.selected) {
                        this.currentEntity = entity;
                    }
                    if (entity.allowed) {
                        noEntity = false;
                    }
                });

                if (this.userListRedirect.length == 0 && noEntity) {
                    this.redirectMode = 'none';
                    this.loading = false;
                } else if (this.userListRedirect.length == 0 && !noEntity) {
                    this.loadEntities();
                } else if (this.userListRedirect.length > 0 && noEntity) {
                    this.loadDestUser();
                } else {
                    this.loading = false;
                }

            }, () => {
                location.href = "index.php";
            });
    }

    loadEntities() {
        this.redirectMode = 'entity';
        if (this.data.resIds.length == 1) {
            this.injectDatasParam.resId = this.data.resIds[0];
        }
        this.loading = false;
        setTimeout(() => {
            $j('#jstree').jstree({
                "checkbox": {
                    'deselect_all': true,
                    "three_state": false //no cascade selection
                },
                'core': {
                    force_text: true,
                    'themes': {
                        'name': 'proton',
                        'responsive': true
                    },
                    'multiple': false,
                    'data': this.entities,
                },
                "plugins": ["checkbox", "search", "sort"]
            });
            var to: any = false;
            $j('#jstree_search').keyup(function () {
                if (to) { clearTimeout(to); }
                to = setTimeout(function () {
                    var v = $j('#jstree_search').val();
                    $j('#jstree').jstree(true).search(v);
                }, 250);
            });
            $j('#jstree')
                // listen for event
                .on('select_node.jstree', (e: any, data: any) => {
                    this.selectEntity(data.node.original);

                }).on('deselect_node.jstree', (e: any, data: any) => {
                    $j('#jstree').jstree('select_node', data.node.original);
                })
                // create the instance
                .jstree();
        }, 0);
        setTimeout(() => {
            $j('#jstree').jstree('select_node', this.currentEntity);
            this.selectEntity(this.currentEntity);

        }, 200);
    }

    loadDestUser() {
        this.redirectMode = 'user';
        this.filteredUserRedirect = this.userRedirectCtrl.valueChanges
            .pipe(
                startWith(''),
                map(user => user ? this._filterUserRedirect(user) : this.userListRedirect.slice())
            );

        if (this.data.resIds.length == 1) {
            this.http.get("../../rest/resources/" + this.data.resIds[0] + "/listInstance").subscribe((data: any) => {
                this.diffusionListDestRedirect = data.listInstance;
                Object.keys(data).forEach(diffusionRole => {
                    data[diffusionRole].forEach((line: any) => {
                        if (line.item_mode == 'dest') {
                            this.oldUser = line;
                        }
                    });
                });
                $j('.searchUserRedirect').click();
            }, (err: any) => {
                this.notify.handleErrors(err);
            });
        } else {
            this.keepDestForRedirection = false;
            setTimeout(() => {
                $j('.searchUserRedirect').click();
            }, 200);
        }
    }

    changeDest(event: any) {
        this.currentDiffusionListDestRedirect = this.diffusionListDestRedirect
        let user = event.option.value;


        this.destUser = {
            difflist_type: "entity_id",
            item_mode: "dest",
            item_type: "user_id",
            item_id: user.user_id,
            labelToDisplay: user.labelToDisplay,
            descriptionToDisplay: user.descriptionToDisplay
        };

        if (this.data.resIds.length == 1) {
            this.isDestinationChanging = false;
            this.http.get('../../rest/resources/' + this.data.resIds[0] + '/users/' + user.id + '/isDestinationChanging')
                .subscribe((data: any) => {
                    this.isDestinationChanging = data.isDestinationChanging;
                }, (err: any) => {
                    this.notify.handleErrors(err);
                });

            if (this.keepDestForRedirection) {
                let isInCopy = false;
                let newCopy = null;
                this.currentDiffusionListDestRedirect.forEach((element: any) => {
                    if (element.item_mode == 'cc' && element.item_id == this.oldUser.item_id) {
                        isInCopy = true;
                    }
                });

                if (!isInCopy) {
                    newCopy = this.oldUser;
                    newCopy.item_mode = 'cc';
                    this.currentDiffusionListDestRedirect.push(newCopy);
                }
            }
            this.currentDiffusionListDestRedirect.splice(this.currentDiffusionListDestRedirect.map((e: any) => { return e.item_mode; }).indexOf('dest'), 1);
        } else {
            this.isDestinationChanging = true;
        }
        this.currentDiffusionListDestRedirect.push(this.destUser);

        this.userRedirectCtrl.reset();
        $j('.searchUserRedirect').blur();
    }

    private _filterUserRedirect(value: string): any[] {
        if (typeof value === 'string') {
            const filterValue = value.toLowerCase();
            return this.userListRedirect.filter(user => user.labelToDisplay.toLowerCase().indexOf(filterValue) >= 0);
        }
    }

    selectEntity(entity: any) {
        this.currentEntity = entity;
        this.appDiffusionsList.loadListModel(entity.serialId);
    }

    onSubmit() {
        this.loading = true;
        if (this.data.resIds.length === 0) {
            //this.indexDocumentAndExecuteAction();
        } else {
            this.executeAction();
        }
    }

    /* indexDocumentAndExecuteAction() {
        
        this.http.post('../../rest/resources', this.data.resource).pipe(
            tap((data: any) => {
                this.data.resIds = [data.resId];
            }),
            exhaustMap(() => this.http.put(this.data.indexActionRoute, {resource : this.data.resIds[0], note : this.noteEditor.getNoteContent()})),
            tap(() => {
                this.dialogRef.close('success');
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe()
    } */

    executeAction() {
        if (this.redirectMode == 'user') {
            this.http.put(this.data.processActionRoute, { resources: this.data.resIds, data: { onlyRedirectDest: true, listInstances: this.currentDiffusionListDestRedirect }, note: this.noteEditor.getNoteContent() }).pipe(
                tap((data: any) => {
                    if (data && data.errors != null) {
                        this.notify.error(data.errors);
                    }
                    this.dialogRef.close('success');
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        } else {

            this.http.put(this.data.processActionRoute, { resources: this.data.resIds, data: { listInstances: this.appDiffusionsList.getCurrentListinstance() }, note: this.noteEditor.getNoteContent() }).pipe(
                tap((data: any) => {
                    if (data && data.errors != null) {
                        this.notify.error(data.errors);
                    }
                    this.dialogRef.close('success');
                }),
                finalize(() => this.loading = false),
                catchError((err: any) => {
                    this.notify.handleErrors(err);
                    return of(false);
                })
            ).subscribe();
        }
    }

    checkValidity() {
        if (this.redirectMode == 'entity' && this.appDiffusionsList && this.appDiffusionsList.getDestUser().length > 0 && this.currentEntity.serialId > 0 && !this.loading) {
            return false;
        } if (this.redirectMode == 'user' && this.currentDiffusionListDestRedirect.length > 0 && this.destUser != null && !this.loading) {
            return false;
        } else {
            return true;
        }
    }
}
