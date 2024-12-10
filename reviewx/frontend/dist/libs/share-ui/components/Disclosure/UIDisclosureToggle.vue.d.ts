declare const _default: __VLS_WithTemplateSlots<import('vue').DefineComponent<import('vue').ExtractPropTypes<{
    modelValue: import('vue').PropType<boolean>;
    id: {
        type: import('vue').PropType<string>;
        required: true;
    };
    disclosureButtonClass: {
        type: import('vue').PropType<string>;
    };
    haveAccess: {
        type: import('vue').PropType<boolean>;
        default: boolean;
    };
}>, {}, {}, {}, {}, import('vue').ComponentOptionsMixin, import('vue').ComponentOptionsMixin, {}, string, import('vue').PublicProps, Readonly<import('vue').ExtractPropTypes<{
    modelValue: import('vue').PropType<boolean>;
    id: {
        type: import('vue').PropType<string>;
        required: true;
    };
    disclosureButtonClass: {
        type: import('vue').PropType<string>;
    };
    haveAccess: {
        type: import('vue').PropType<boolean>;
        default: boolean;
    };
}>> & Readonly<{}>, {
    haveAccess: boolean;
}, {}, {}, {}, string, import('vue').ComponentProvideOptions, true, {}, any>, {
    label?(_: {}): any;
    content?(_: {}): any;
}>;
export default _default;
type __VLS_WithTemplateSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
