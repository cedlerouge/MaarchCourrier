export interface UserWorkflow {
    itemId: number,
    itemType: string,
    itemEntity: string,
    labelToDisplay: string,
    difflistType: string,
    role: string,
    signatory: boolean,
    hasPrivilege: boolean,
    isValid: boolean,
    externalId: {},
    availableRoles : string[],
}

export class UserWorkflow implements UserWorkflow {
    constructor() {
        this.itemId = null;
        this.itemType = 'user',
        this.itemEntity = '',
        this.labelToDisplay = '',
        this.role = ''
        this.difflistType = 'VISA_CIRCUIT',
        this.signatory = false,
        this.hasPrivilege = false,
        this.isValid = false,
        this.externalId = {},
        this.availableRoles = []
    }
}