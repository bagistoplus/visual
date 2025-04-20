import { defineComponent } from '../../utils/define-component';
import { useId } from '../../utils/use-id';

/**
 * Example Usage:
 *
 * <div x-accordion="{ value: [], multiple: false }">
 *   <div x-accordion:item="'Apple'">
 *     <button x-accordion:item-trigger>Apple</button>
 *     <div x-accordion:item-content>Apples are crisp and juicy.</div>
 *   </div>
 *   <div x-accordion:item="'Banana'">
 *     <button x-accordion:item-trigger>Banana</button>
 *     <div x-accordion:item-content>Bananas are rich in potassium.</div>
 *   </div>
 * </div>
 */

interface AccordionAPI {
  value: string[];
  multiple: boolean;
  toggle(id: string): void;
  isOpen(id: string): boolean;
}

export default defineComponent<AccordionAPI>({
  name: 'accordion',

  setup: (props) => ({
    value: props.value || [],
    multiple: props.multiple || false,

    init() {
      if (!this.multiple && this.value.length > 1) {
        this.value = [this.value[0]];
      }
    },

    toggle(id) {
      const isOpen = this.value.includes(id);

      if (isOpen) {
        this.value = this.value.filter((i) => i !== id);
      } else {
        if (this.multiple) {
          this.value = [...this.value, id];
        } else {
          this.value = [id];
        }
      }
    },

    isOpen(id) {
      return this.value.includes(id);
    },
  }),

  parts: {
    item(api, _, { value }) {
      const id = value ?? useId('item');
      return {
        'x-collapsible': '',
        'x-on:toggle': () => api.toggle(id),
        'x-effect'() {
          const isExpanded = api.isOpen(id);
          if (this.$collapsible.expanded !== isExpanded) {
            this.$collapsible.expanded = isExpanded;
          }
        },
      };
    },

    itemTrigger() {
      return {
        'x-collapsible:trigger': '',
      };
    },

    itemContent: () => ({
      'x-collapsible:content': '',
    }),

    itemIndicator: () => ({
      'x-collapsible:indicator': '',
    }),
  },
});
