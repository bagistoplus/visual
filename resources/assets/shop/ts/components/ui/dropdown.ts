import { defineComponent } from '../../utils/define-component';

/**
 * Example Usage:
 *
 * <div x-dropdown>
 *     <button x-dropdown:trigger>Apple</button>
 *     <div x-dropdown:content>Apples are crisp and juicy.</div>
 * </div>
 */

interface DropdownAPI {
  open: boolean;
  placement: 'start' | 'end';
  triggerId: string;
  contentId: string;
  toggle(): void;
  close(): void;
}

export default defineComponent<DropdownAPI>({
  name: 'dropdown',

  setup: (props, { generateId }) => ({
    open: props.open ?? false,
    placement: props.placement === 'start' ? 'start' : 'end',

    triggerId: generateId('trigger'),
    contentId: generateId('content'),

    toggle() {
      this.open = !this.open;
    },

    close() {
      this.open = false;
    },
  }),

  parts: {
    trigger: (api) => ({
      id: api.triggerId,
      'aria-haspopup': 'menu',
      'aria-controls': api.contentId,
      'x-bind:aria-expanded': () => api.open,
      'x-bind:data-state': () => (api.open ? 'open' : 'closed'),
      'x-on:click': () => api.toggle(),
    }),

    content: (api) => ({
      id: api.contentId,
      role: 'menu',
      tabindex: -1,
      'aria-labelledby': api.triggerId,
      'x-bind:data-state': () => (api.open ? 'open' : 'closed'),
      'x-bind:data-placement': () => api.placement,
      'x-show': () => api.open,
      'x-transition': '',
      'x-on:click.outside': () => api.close(),
    }),
  },
});
