export interface Action {
    id: number;
    label: string;
    categories: string[];
    component: string;
}

export interface DataToSendActionInterface {
    note: {
        content: string,
        entities: any[]
    },
    resources: any[]
    data: any;
}

export interface ContinueVisaCircuitDataToSendInterface extends DataToSendActionInterface {
    data : ContinueVisaCircuitObjectInterface;
}

export interface ContinueVisaCircuitObjectInterface {
    [key:number]: {
        resId: number;
        isAttachment: boolean;
        documentId: number;
        cookieSession: string;
        hashSignature: string;
        signatureContentLength: number;
        signatureFieldName: string;
        signature: any[];
        certificate: string;
        tmpUniqueId: string;
    }[]
}

export interface MessageActionInterface {
    id : string;
    data?: any;
}

export class MessageAction implements MessageActionInterface {
    id: string = '';
    data?: any = null;

    constructor() {}
}
