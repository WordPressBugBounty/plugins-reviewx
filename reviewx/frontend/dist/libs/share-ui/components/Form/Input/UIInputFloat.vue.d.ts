declare const _default: __VLS_WithTemplateSlots<import('vue').DefineComponent<import('vue').ExtractPropTypes<{
    modelValue: import('vue').PropType<any>;
    type: {
        type: import('vue').PropType<string>;
        required: true;
        default: string;
    };
    disabled: {
        type: import('vue').PropType<boolean>;
    };
    label: {
        type: import('vue').PropType<string>;
        required: true;
    };
    id: {
        type: import('vue').PropType<string>;
        required: true;
    };
    inputClass: {
        type: import('vue').PropType<any>;
    };
    inputStyle: {
        type: import('vue').PropType<any>;
    };
    showEye: {
        type: import('vue').PropType<boolean>;
        default: boolean;
    };
}>, {}, {}, {}, {}, import('vue').ComponentOptionsMixin, import('vue').ComponentOptionsMixin, {}, string, import('vue').PublicProps, Readonly<import('vue').ExtractPropTypes<{
    modelValue: import('vue').PropType<any>;
    type: {
        type: import('vue').PropType<string>;
        required: true;
        default: string;
    };
    disabled: {
        type: import('vue').PropType<boolean>;
    };
    label: {
        type: import('vue').PropType<string>;
        required: true;
    };
    id: {
        type: import('vue').PropType<string>;
        required: true;
    };
    inputClass: {
        type: import('vue').PropType<any>;
    };
    inputStyle: {
        type: import('vue').PropType<any>;
    };
    showEye: {
        type: import('vue').PropType<boolean>;
        default: boolean;
    };
}>> & Readonly<{}>, {
    type: string;
    showEye: boolean;
}, {}, {}, {}, string, import('vue').ComponentProvideOptions, true, {}, any>, {
    eye?(_: {}): any;
}>;
export default _default;
type __VLS_WithTemplateSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
