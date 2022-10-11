export interface UserWorkflow {
    /**
     * User identifier in the external signatory book
     */
    id?: number;

    /**
     * User identifier in Maarch Courrier
     */
    item_id?: number;

    /**
     * Object identifier
     */
    listinstance_id?: number;

    /**
     * Identifier of the delegating user
     */
    delegatedBy?: number;

    /**
     * Type of item: 'user', 'entity', ...
     */
    item_type: string;


    /**
     * Entity of the item: can be the processing entity or the email address
     */
    item_entity?: string;


    /**
     * Label to display : firstname + last name
     */
    labelToDisplay: string;


    /**
     * Role of item : 'visa', 'stamp', ...
     */
    role?: string;


    /**
     * Date the user made the visa/sign action
     */
    process_date?: string;

    /**
     * User avatar
     */
    picture?: string;

    /**
     * User status
     */
    status?: string;

    /**
     *
     */
    difflist_type?: string;

    /**
     * Diffusion list type: 'VISA_CIRCUIT', 'AVIS_CIRCUIT', ...
     */
    externalId?: {};

    /**
     * External identifier
     */
    externalInformations?: {};

    /**
     * other external information
     */
    availableRoles?: string[];

    /**
     * Available roles: 'visa', 'sign', 'inca_card', 'rgs_2stars', ...
     */
    requested_signature?: boolean;

    /**
     * Indicates whether the user must sign a mail or not
     */
    signatory?: boolean;

    /**
     * Indicates whether the user has signed or not
     */
    hasPrivilege: boolean;

    /**
     * Indicates whether the user has the privilege
     */

    /**
     * Indicates if the user is valid
     */
    isValid: boolean;
}

export class UserWorkflow implements UserWorkflow {
    constructor() {
        this.id = null;
        this.item_id = null;
        this.listinstance_id = null;
        this.delegatedBy = null;
        this.item_type = 'user';
        this.item_entity = '';
        this.labelToDisplay = '';
        this.role = '';
        this.process_date = '';
        this.picture = '';
        this.status = '';
        this.difflist_type = 'VISA_CIRCUIT';
        this.signatory = false;
        this.hasPrivilege = false;
        this.isValid = false;
        this.requested_signature = false;
        this.externalId = {};
        this.externalInformations = {};
        this.availableRoles = [];
    }
}
