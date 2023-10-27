import { Injectable, ViewContainerRef } from '@angular/core';
import { loadRemoteModule } from '@angular-architects/module-federation';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from './notification/notification.service';

@Injectable(
    {
        providedIn: 'root'
    }
)
export class PluginManagerService {
    plugins: any = {};
    constructor(
        private httpClient: HttpClient,
        private notificationService: NotificationService
    ) {}

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
                console.debug(`PLUGIN ${pluginName} LOADED`);

            } catch (err) {
                console.warn(`PLUGIN ${pluginName} FAILED :`);
                console.warn(err);
            }
        }
    }

    async initPlugin(pluginName: string, containerRef: ViewContainerRef) {
        if (!this.plugins[pluginName]) {
            return false;
        }
        const remoteComponent: any = containerRef.createComponent(this.plugins[pluginName][Object.keys(this.plugins[pluginName])[0]]);
        remoteComponent.instance.init(this);
        return remoteComponent.instance;
    }

    loadRemotePlugin(pluginName: string): Promise<any> {
        return loadRemoteModule({
            type: 'module',
            remoteEntry: '../plugins/maarch-plugins/remoteEntry.js',
            exposedModule: `./${pluginName}`,
        });
    }
}
