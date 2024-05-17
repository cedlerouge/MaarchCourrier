import { AttachmentInterface } from "@models/attachment.model";
import { ResourcesListInterface } from "@models/resources-list.model";

export interface StampInterface {
    /**
     * base64 image
     */
    "base64Image": string,

    /**
     * stamp width (percentage of page width)
     */
    "width": number,
    /**
     * stamp height (percentage of page height)
     */
    "height": number,
    /**
     * X position (percentage relative of page width)
     */
    "positionX": number,
    /**
     * Y position (percentage relative of page height)
     */
    "positionY": number,
    /**
     * page of stamp located
     */
    "page": number
}

export interface SelectedAttachmentInterface {
    index: number;
    attachment: AttachmentInterface;
}

export class SelectedAttachment implements SelectedAttachmentInterface {
    index: number = null;
    attachment: AttachmentInterface = null;

    constructor(json: any = null) {
        if (json) {
            Object.assign(this, json);
        }
    }
}
