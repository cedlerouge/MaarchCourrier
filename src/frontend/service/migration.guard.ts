import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { MigrationService } from './migration.service';
import { Observable, of } from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class MigrationCheckGuard implements CanActivate {
    constructor(
        private migrationService: MigrationService
    ) {}

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<any> {
        if (this.migrationService.migrating) {
            this.migrationService.logoutAndShowAlert();
        }
        return of(true);
    }
}
