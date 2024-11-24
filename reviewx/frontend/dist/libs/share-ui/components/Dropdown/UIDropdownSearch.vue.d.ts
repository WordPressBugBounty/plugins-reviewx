import { ISearchProductOptions } from './types/index';

declare const _default: import('vue').DefineComponent<import('vue').ExtractPropTypes<{
    modelValue: import('vue').PropType<object>;
    options: {
        type: import('vue').PropType<ISearchProductOptions[]>;
        required: true;
    };
    placeholder: {
        type: import('vue').PropType<string>;
        default: string;
    };
    inputClass: {
        type: import('vue').PropType<string>;
    };
    loading: {
        type: import('vue').PropType<boolean>;
        default: boolean;
    };
}>, {}, {}, {}, {}, import('vue').ComponentOptionsMixin, import('vue').ComponentOptionsMixin, {
    searchValue: (value: string) => void;
}, string, import('vue').PublicProps, Readonly<import('vue').ExtractPropTypes<{
    modelValue: import('vue').PropType<object>;
    options: {
        type: import('vue').PropType<ISearchProductOptions[]>;
        required: true;
    };
    placeholder: {
        type: import('vue').PropType<string>;
        default: string;
    };
    inputClass: {
        type: import('vue').PropType<string>;
    };
    loading: {
        type: import('vue').PropType<boolean>;
        default: boolean;
    };
}>> & Readonly<{
    onSearchValue?: ((value: string) => any) | undefined;
}>, {
    placeholder: string;
    loading: boolean;
}, {}, {}, {}, string, import('vue').ComponentProvideOptions, true, {}, any>;
export default _default;
