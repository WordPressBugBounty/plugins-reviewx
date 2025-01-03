import { UIModuleSettingActions } from './types';

declare const _default: __VLS_WithTemplateSlots<import('vue').DefineComponent<import('vue').ExtractPropTypes<__VLS_WithDefaults<__VLS_TypePropsToRuntimeProps<Partial<UIModuleSettingActions>>, {
    visibility: boolean;
    disabled: boolean;
    defaultButtonDisabled: boolean;
    loader: boolean;
    defaultLoader: boolean;
    showDefaultButton: boolean;
}>>, {}, {}, {}, {}, import('vue').ComponentOptionsMixin, import('vue').ComponentOptionsMixin, {
    default: () => void;
    save: () => void;
}, string, import('vue').PublicProps, Readonly<import('vue').ExtractPropTypes<__VLS_WithDefaults<__VLS_TypePropsToRuntimeProps<Partial<UIModuleSettingActions>>, {
    visibility: boolean;
    disabled: boolean;
    defaultButtonDisabled: boolean;
    loader: boolean;
    defaultLoader: boolean;
    showDefaultButton: boolean;
}>>> & Readonly<{
    onDefault?: (() => any) | undefined;
    onSave?: (() => any) | undefined;
}>, {
    disabled: boolean;
    loader: boolean;
    visibility: boolean;
    defaultLoader: boolean;
    showDefaultButton: boolean;
    defaultButtonDisabled: boolean;
}, {}, {}, {}, string, import('vue').ComponentProvideOptions, true, {}, any>, {
    default?(_: {}): any;
    save?(_: {}): any;
}>;
export default _default;
type __VLS_NonUndefinedable<T> = T extends undefined ? never : T;
type __VLS_TypePropsToRuntimeProps<T> = {
    [K in keyof T]-?: {} extends Pick<T, K> ? {
        type: import('vue').PropType<__VLS_NonUndefinedable<T[K]>>;
    } : {
        type: import('vue').PropType<T[K]>;
        required: true;
    };
};
type __VLS_WithDefaults<P, D> = {
    [K in keyof Pick<P, keyof P>]: K extends keyof D ? __VLS_Prettify<P[K] & {
        default: D[K];
    }> : P[K];
};
type __VLS_Prettify<T> = {
    [K in keyof T]: T[K];
} & {};
type __VLS_WithTemplateSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
