export interface Action {
    id: number;
    label: string;
    categories: string[];
    component: string;
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