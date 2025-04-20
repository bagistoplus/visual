import type { Alpine as AlpineType } from 'alpinejs';

export interface TriggerOptions {
  title: string;
  description: string;
  confirmText: string;
  cancelText: string;
  onConfirm: () => void;
  onCancel: () => void;
}

export default function (Alpine: AlpineType): void {
  const confirmStore = {
    title: '',
    description: '',
    confirmText: '',
    cancelText: '',

    defaults: {
      title: 'Are you sure?',
      description: 'Are you sure you want to do this?',
      confirmText: 'Yes',
      cancelText: 'No',
    },

    onConfirm: () => {},
    onCancel: () => {},

    setDefaults(defaults: Omit<TriggerOptions, 'onConfirm' | 'onCancel'>) {
      this.defaults = defaults;
    },

    trigger(options: Partial<TriggerOptions>) {
      this.title = options.title || this.defaults.title;
      this.description = options.description || this.defaults.description;
      this.confirmText = options.confirmText || this.defaults.confirmText;
      this.cancelText = options.cancelText || this.defaults.cancelText;
      this.onConfirm = options.onConfirm || (() => {});
      this.onCancel = options.onCancel || (() => {});

      window.dispatchEvent(new CustomEvent('modal:open', { detail: 'confirm' }));
    },

    confirm() {
      this.onConfirm();
      window.dispatchEvent(new CustomEvent('modal:close', { detail: 'confirm' }));
    },
  };

  Alpine.store('confirm', confirmStore);

  Alpine.magic('confirm', (el: HTMLElement) => (userCallback: (event: Event) => void) => {
    return (event: Event) => {
      (Alpine.store('confirm') as typeof confirmStore).trigger({
        title: el.dataset.confirmTitle as string,
        description: el.dataset.confirmDescription as string,
        confirmText: el.dataset.confirmConfirmText as string,
        cancelText: el.dataset.confirmCancelText as string,
        onConfirm: () => {
          if (typeof userCallback === 'function') {
            userCallback(event);
          }
        },
      });
    };
  });

  Alpine.magic('triggerConfirm', () => (option: TriggerOptions) => {
    (Alpine.store('confirm') as typeof confirmStore).trigger(option);
  });
}
