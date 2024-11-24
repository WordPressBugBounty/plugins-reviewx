export interface Avatar {
    src: string;
    alt: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    imageClass?: string;
    fallbackClass?: string;
}
