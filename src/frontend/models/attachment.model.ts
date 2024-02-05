export interface AttachmentInterface {
    resId: number;
    resIdMaster: number;
    canConvert: boolean;
    canDelete: boolean;
    canUpdate: boolean;
    chrono: string;
    creationDate: string;
    external_state: Object;
    inSendAttach: boolean;
    inSignatureBook: boolean;
    modificationDate: any;
    modifiedBy: string;
    relation: number;
    status: string;
    title: string;
    type: string;
    typeLabel: string;
    typist: number;
    typistLabel: string;
}

export class Attachment implements AttachmentInterface {

    resId: number = null;
    resIdMaster: number = null;
    canConvert: boolean = false;
    canDelete: boolean = false;
    canUpdate = false;
    chrono: string = null;
    creationDate: string = null;
    external_state: Object = {};
    inSendAttach: boolean = false;
    inSignatureBook: boolean = false;
    modificationDate: string = null;
    modifiedBy: string = null;
    relation: number = null;
    status: string = null;
    title: string = '';
    type: string = null;
    typeLabel: string = '';
    typist: number = null;
    typistLabel: string = '';

    constructor(json: any = null) {
        if (json) {
            Object.assign(this, json);
        }
    }

}