export interface ResourcesListInterface {
    resId: number;
    subject: string;
    chrono: string;
    statusImage: string;
    statusLabel: string;
    creationDate: string;
    processLimitDate: string
    priorityColor: string;
    mailTracking: boolean;
}

export class ResourcesList implements ResourcesListInterface {
    resId: number = null;
    subject: string = '';
    chrono: string = '';
    statusImage: string = '';
    statusLabel: string = '';
    priorityColor: string = '';
    creationDate: string = '';
    processLimitDate: string = '';
    mailTracking: boolean = false;

    constructor(json: any = null) {
        if (json) {
            Object.assign(this, json);
        }
    }
}