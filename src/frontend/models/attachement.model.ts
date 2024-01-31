export interface Attachement {
    resId: number;
    resIdMaster: number;
    canConvert: boolean;
    canDelete: boolean;
    canUpdate: boolean;
    chrono: string;
    creationDate: string;
    external_state: Object;
    inSendAttach: boolean;
    inSignatureBook: true;
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

export class Attachement implements Attachement {
    constructor() {
        this.resId = 0;
        this.resIdMaster = 0;
        this.canConvert = true;
        this.canDelete = true;
        this.canUpdate = true;
        this.chrono = '';
        this.creationDate = '';
        this.external_state = {};
        this.inSendAttach = true;
        this.inSignatureBook = true;
        this.modificationDate= '';
        this.modifiedBy = '';
        this.relation = 0;
        this.status = '';
        this.title = '';
        this.type = ''
        this.typeLabel = ''
        this.typist = 0;
        this.typistLabel = '';
    }
}