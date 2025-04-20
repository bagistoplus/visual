import { defineComponent } from '../../utils/define-component';
import { useId } from '../../utils/use-id';

interface ModalAPI {
  name: string;
  open: boolean;
  show(): void;
  hide(): void;
}

export default defineComponent<ModalAPI>({
  name: 'modal',

  setup: (props) => {
    const name = props.name ?? useId('modal');

    return {
      name,
      open: false,

      show() {
        this.open = true;
        // (document.activeElement as HTMLElement)?.blur();
      },

      hide() {
        this.open = false;
      },
    };
  },

  parts: {
    root(api) {
      return {
        'x-on:keydown.escape.window': () => api.hide(),
        'x-on:modal:open.window'(e: CustomEvent<string>) {
          if (api.name === e.detail) {
            api.show();
          }
        },
        'x-on:modal:close.window'(e: CustomEvent<string>) {
          if (api.name === e.detail) {
            api.hide();
          }
        },
      };
    },

    trigger(api, el) {
      return {
        'x-on:click': () => {
          api.show();
        },
      };
    },

    backdrop(api) {
      return {
        'x-show': () => api.open,
        'x-bind:data-state': () => (api.open ? 'open' : 'closed'),
      };
    },

    positioner(api, el) {
      return {
        'x-show': () => api.open,
        'x-bind:data-state': () => (api.open ? 'open' : 'closed'),
        'x-trap.noscroll': () => api.open,
      };
    },

    content(api, el, { generateId }) {
      const hasTitle = !!el.querySelector('[x-modal\\:title]');
      const hasDescription = !!el.querySelector('[x-modal\\:description]');

      return {
        role: 'dialog',
        'aria-modal': 'true',
        'x-bind:data-state': () => (api.open ? 'open' : 'closed'),
        'x-on:click.outside': () => api.hide(),

        ...(hasTitle && { 'aria-labelledby': generateId('title') }),
        ...(hasDescription && {
          'aria-describedby': generateId('description'),
        }),
      };
    },

    title(_, __, { generateId }) {
      return {
        id: generateId('title'),
      };
    },

    description(_, __, { generateId }) {
      return {
        id: generateId('description'),
      };
    },

    closeTrigger(api) {
      return {
        'x-on:click': () => api.hide(),
      };
    },
  },
});
