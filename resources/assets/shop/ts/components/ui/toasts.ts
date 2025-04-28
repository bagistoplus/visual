import type { Alpine as AlpineType } from 'alpinejs';
import { defineComponent, defineScope } from '../../utils/define-component';

declare namespace Alpine {
  interface Magics<T> {
    toaster: {
      create: (toast: Partial<Toast>) => void;
      info: (toast: string | Partial<Toast>) => void;
      success: (toast: string | Partial<Toast>) => void;
      warning: (toast: string | Partial<Toast>) => void;
      error: (toast: string | Partial<Toast>) => void;
    };
  }
}

type Placement =
  | 'top-start'
  | 'top-center'
  | 'top-end'
  | 'bottom-start'
  | 'bottom-center'
  | 'bottom-end';

export interface Toast {
  id: string;
  title: string;
  description?: string;
  placement?: Placement;
  type?: 'info' | 'success' | 'warning' | 'error';
  duration?: number;
}

interface GroupScope {
  placement: string;
  toasts(): Toast[];
}

interface ToastScope extends Toast {
  dismissed?: boolean;
  dismiss(): void;
}

interface ToasterAPI {
  placement: Placement;
  placements: Placement[];
  store: Toast[];
  create(toast: Partial<Toast>): void;
  dismiss(id: string): void;

  $group: GroupScope;
  $toast: ToastScope;
}

export default defineComponent<ToasterAPI>({
  name: 'toasts',

  setup: (props) => ({
    placement: props.placement || 'top-end',
    store: [],

    get placements() {
      return Array.from(
        new Set(this.store.map((t: Toast) => t.placement))
      ) as Placement[];
    },

    init() {
      window.addEventListener('toasts:create', (e) => {
        this.create((e as CustomEvent).detail);
      });
    },

    create(toast) {
      const newToast: Toast = {
        id: String(Date.now()),
        type: toast.type || 'info',
        title: toast.title || '',
        description: toast.description || '',
        placement: toast.placement || this.placement,
        duration: toast.duration || 3000,
      };

      this.store.push(newToast);

      if (newToast.duration && newToast.duration > 0) {
        setTimeout(() => {
          this.dismiss(newToast.id);
        }, newToast.duration);
      }
    },

    dismiss(id) {
      setTimeout(() => {
        this.store = this.store.filter((t) => t.id !== id);
      }, 300);
    },
  }),

  parts: {
    group: defineScope<ToasterAPI, 'group', GroupScope>({
      name: 'group',

      setup: (api, _, { value: placement }) => ({
        placement,
        toasts: () => api.store.filter((t) => t.placement === placement),
      }),

      bindings: (_, scope) => {
        const [side, align] = scope.placement?.split('-') as string[];
        return {
          role: 'region',
          'data-placement': scope.placement,
          'data-side': side,
          'data-align': align,
        };
      },
    }),

    toast: defineScope<ToasterAPI, 'toast', ToastScope>({
      name: 'toast',

      setup: (api, _, { value: toast }) => ({
        ...toast,
        dismissed: false,
        dismiss() {
          this.dismissed = true;
          api.dismiss(this.id);
        },
      }),

      bindings: (_, scope) => {
        const [side, align] = scope.placement?.split('-') as string[];
        return {
          'data-type': scope.type,
          'data-placement': scope.placement,
          'data-side': side,
          'data-align': align,
          'x-bind:data-state': () => (scope.dismissed ? 'closed' : 'open'),
        };
      },
    }),

    toastCloseTrigger(api) {
      return {
        type: 'button',
        'x-on:click': () => api.$toast.dismiss(),
      };
    },
  },
});

export function toaster(Alpine: AlpineType) {
  const create = (toast: Partial<Toast>) => {
    window.dispatchEvent(new CustomEvent('toasts:create', { detail: toast }));
  };

  const createToster =
    (type: Toast['type']) => (toast: string | Partial<Toast>) => {
      const detail = typeof toast === 'string' ? { title: toast } : toast;
      create({ ...detail, type });
    };

  Alpine.magic('toaster', () => ({
    create,
    info: createToster('info'),
    warning: createToster('warning'),
    success: createToster('success'),
    error: createToster('error'),
  }));
}
