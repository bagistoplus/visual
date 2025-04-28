import type { Toast } from './components/ui/toasts';
import { TriggerOptions } from './plugins/confirm';
import { RequestFn } from './plugins/helpers';

declare module 'alpinejs' {
  interface Magics<T> {
    $toaster: {
      create: (toast: Partial<Toast>) => void;
      info: (toast: string | Partial<Toast>) => void;
      success: (toast: string | Partial<Toast>) => void;
      warning: (toast: string | Partial<Toast>) => void;
      error: (toast: string | Partial<Toast>) => void;
    };

    $confirm: (cb: (event: Event) => void) => (event: Event) => void;
    $triggerConfirm: (options: Partial<TriggerOptions>) => void;

    $request: RequestFn;
    $formatPrice: (price: number) => string;

    $wire: Record<string, any>;
  }
}
