export interface IActionDropdown {
    id: number;
    label: string;
    customClass?: string;
    eventName: string;
    haveAccess?: boolean;
    [key: string]: any;
}
export type ISearchProductOptions = {
    id: number | string;
    name: string;
    image?: string;
    wp_id?: string | number;
    [key: string]: any;
};
export interface IDropdownOption {
    id: number;
    label: string;
    value: string;
}
