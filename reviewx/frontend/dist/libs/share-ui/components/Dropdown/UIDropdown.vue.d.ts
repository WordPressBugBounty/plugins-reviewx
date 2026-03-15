import { IDropdownOption } from './types';

declare const _default: import('vue').DefineComponent<import('vue').ExtractPropTypes<{
    modelValue: import('vue').PropType<IDropdownOption>;
    options: {
        type: import('vue').PropType<IDropdownOption[]>;
        required: true;
    };
    placeholder: {
        type: import('vue').PropType<string>;
        default: string;
    };
    buttonClass: {
        type: import('vue').PropType<string>;
    };
    placement: {
        type: import('vue').PropType<"bottom" | "top">;
        default: string;
    };
}>, {}, {}, {}, {}, import('vue').ComponentOptionsMixin, import('vue').ComponentOptionsMixin, {}, string, import('vue').PublicProps, Readonly<import('vue').ExtractPropTypes<{
    modelValue: import('vue').PropType<IDropdownOption>;
    options: {
        type: import('vue').PropType<IDropdownOption[]>;
        required: true;
    };
    placeholder: {
        type: import('vue').PropType<string>;
        default: string;
    };
    buttonClass: {
        type: import('vue').PropType<string>;
    };
    placement: {
        type: import('vue').PropType<"bottom" | "top">;
        default: string;
    };
}>> & Readonly<{}>, {
    placeholder: string;
    placement: "bottom" | "top";
}, {}, {}, {}, string, import('vue').ComponentProvideOptions, true, {}, any>;
export default _default;
