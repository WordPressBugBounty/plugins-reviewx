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
}>> & Readonly<{}>, {
    placeholder: string;
}, {}, {}, {}, string, import('vue').ComponentProvideOptions, true, {}, any>;
export default _default;
