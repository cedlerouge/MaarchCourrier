import { Injectable, ViewContainerRef } from '@angular/core';
import { loadRemoteModule } from '@angular-architects/module-federation';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from './notification/notification.service';
import { AuthService } from './auth.service';

@Injectable({
    providedIn: 'root',
})
export class PluginManagerService {
    plugins: any = {};
    constructor(
        private httpClient: HttpClient,
        private authService : AuthService,
        private notificationService: NotificationService) {}

    get http(): HttpClient {
        return this.httpClient;
    }

    get notification(): NotificationService {
        return this.notificationService;
    }

    async storePlugins(pluginNames: string[]) {
        for (let index = 0; index < pluginNames.length; index++) {
            const pluginName = pluginNames[index];
            try {
                const plugin = await this.loadRemotePlugin(pluginName);
                this.plugins[pluginName] = plugin;
                console.info(`PLUGIN ${pluginName} LOADED`);
            } catch (err) {
                console.error(`PLUGIN ${pluginName} FAILED: ${err}`);
            }
        }
    }

    async initPlugin(pluginName: string, containerRef: ViewContainerRef, extraData: any = {}) {
        if (!this.plugins[pluginName]) {
            return false;
        }
        try {
            containerRef.detach();
            const remoteComponent: any = containerRef.createComponent(
                this.plugins[pluginName][Object.keys(this.plugins[pluginName])[0]]
            );
            extraData = { ...extraData, pluginUrl: this.authService.maarchUrl.replace(/\/$/, '') + '/plugins/maarch-plugins' }
            remoteComponent.instance.init({ ...this, ...extraData });
            return remoteComponent.instance;
        } catch (error) {
            this.notificationService.error(`Init plugin ${pluginName} failed !`);
            console.error(error);
            return false;
        }
    }

    loadRemotePlugin(pluginName: string): Promise<any> {
        return loadRemoteModule({
            type: 'module',
            remoteEntry: '../plugins/maarch-plugins/remoteEntry.js',
            exposedModule: `./${pluginName}`,
        });
    }
}

