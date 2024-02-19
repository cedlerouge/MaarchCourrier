export interface AttachmentInterface {
    resId: number;
    resIdMaster: number;
    canConvert: boolean;
    canDelete: boolean;
    canUpdate: boolean;
    chrono: string;
    creationDate: string;
    title: string;
    typeLabel: string;
    sign: boolean;
}

export class Attachment implements AttachmentInterface {

    resId: number = null;
    resIdMaster: number = null;
    canConvert: boolean = false;
    canDelete: boolean = false;
    canUpdate = false;
    chrono: string = null;
    creationDate: string = null;
    title: string = '';
    typeLabel: string = null;
    sign: boolean = false;

    constructor(json: any = null) {
        if (json) {
            Object.assign(this, json);
        }
    }
}