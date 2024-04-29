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
