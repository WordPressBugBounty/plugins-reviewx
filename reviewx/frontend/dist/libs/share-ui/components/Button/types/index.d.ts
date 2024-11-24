import { ISpinner } from '../../Spinner/types';
import { IIcon } from '../../Icon/types';

export interface IButton extends Partial<IIcon>, Partial<ISpinner> {
    haveIcon?: boolean;
    iconPosition?: 'before' | 'after';
    iconClass?: string;
    variant?: 'gray' | 'primary' | 'danger';
    loader?: boolean;
    loaderPosition?: 'before' | 'after';
    type?: 'button' | 'reset' | 'submit' | undefined;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    disabled?: boolean;
}
