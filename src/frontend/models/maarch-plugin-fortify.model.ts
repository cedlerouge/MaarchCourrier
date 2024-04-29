import { SignatureBookInterface } from "@appRoot/signatureBook/signature-book.service";
import { TranslateService } from "@ngx-translate/core";
import { FunctionsService } from "@service/functions.service";
import { NotificationService } from "@service/notification/notification.service";

export interface MaarchPluginFortifyInterface {
    functions: FunctionsService;
    notification: NotificationService;
    translate: TranslateService;
    pluginUrl: string;
    additionalInfo: {
        resource: object;
        sender: string;
        externalUserId: number;
        signatureBookConfig: SignatureBookInterface,
        digitalCertificate: boolean
    };
}