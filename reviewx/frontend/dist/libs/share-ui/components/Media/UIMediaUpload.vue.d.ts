import { Ref } from 'vue';

type UploadMedia = {
    multiple: boolean;
    accept: string;
    disabled: boolean;
};
declare const _default: __VLS_WithTemplateSlots<import('vue').DefineComponent<import('vue').ExtractPropTypes<__VLS_WithDefaults<__VLS_TypePropsToRuntimeProps<Partial<UploadMedia>>, {
    multiple: boolean;
    accept: string;
    disabled: boolean;
}>>, {
    isDraging: Ref<boolean, boolean>;
}, {}, {}, {}, import('vue').ComponentOptionsMixin, import('vue').ComponentOptionsMixin, {
    onChange: (e: Event) => void;
    filesDropped: (files: FileList) => void;
}, string, import('vue').PublicProps, Readonly<import('vue').ExtractPropTypes<__VLS_WithDefaults<__VLS_TypePropsToRuntimeProps<Partial<UploadMedia>>, {
    multiple: boolean;
    accept: string;
    disabled: boolean;
}>>> & Readonly<{
    onOnChange?: ((e: Event) => any) | undefined;
    onFilesDropped?: ((files: FileList) => any) | undefined;
}>, {
    disabled: boolean;
    multiple: boolean;
    accept: string;
}, {}, {}, {}, string, import('vue').ComponentProvideOptions, true, {}, any>, {
    content?(_: {}): any;
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
