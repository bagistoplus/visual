import { defineComponent } from '../../utils/define-component';

interface NumberInputAPI {
  value: number;
  min: number;
  max: number;
  step: number;
  increment(): void;
  decrement(): void;
  setValue(v: number): void;
}

export default defineComponent<NumberInputAPI>({
  name: 'number-input',

  setup: (props) => ({
    value: 0,
    min: props.min ?? Number.MIN_SAFE_INTEGER,
    max: props.max ?? Number.MAX_SAFE_INTEGER,
    step: props.step ?? 1,

    init() {
      this.setValue(props.value as any);
    },

    increment() {
      const next = this.value + this.step;
      this.value = Math.min(next, this.max);
      this.$dispatch('change', this.value);
    },

    decrement() {
      const prev = this.value - this.step;
      this.value = Math.max(prev, this.min);
      this.$dispatch('change', this.value);
    },

    setValue(v) {
      const n = Number(v);
      if (!isNaN(n)) {
        this.value = Math.max(this.min, Math.min(this.max, n));
        this.$dispatch('change', this.value);
      }
    },
  }),

  parts: {
    label(api, el, { generateId }) {
      const labelId = generateId('label');
      const inputId = generateId('input');

      return {
        id: labelId,
        for: inputId,
      };
    },

    input(api, el, { generateId }) {
      const labelId = generateId('label');

      return {
        id: generateId('input'),
        role: 'spinbutton',
        type: 'text',
        autocomplete: 'off',
        autocorrect: 'off',
        spellcheck: 'false',
        'aria-roledescription': 'numberfield',
        'x-bind:aria-valuemin': () => api.min,
        'x-bind:aria-valuemax': () => api.max,
        'x-bind:aria-valuenow': () => api.value,
        'x-bind:aria-labelledby': () =>
          document.getElementById(labelId) ? labelId : undefined,

        'x-bind:value': () => api.value,
        'x-on:change.stop': (e: Event) => {
          const target = e.target as HTMLInputElement;
          api.setValue(target.value as any);
        },
        'x-on:keydown.arrow-up': () => api.increment(),
        'x-on:keydown.arrow-down': () => api.decrement(),
        'x-on:wheel': (e: WheelEvent) => e.preventDefault(),
      };
    },

    incrementTrigger(api, el, { generateId }) {
      return {
        id: generateId('inc'),
        type: 'button',
        tabindex: -1,
        'aria-label': 'increment value',
        'aria-controls': generateId('input'),
        'x-bind:disabled': () => api.value >= api.max,
        'x-on:click': () => api.increment(),
      };
    },

    decrementTrigger(api, el, { generateId }) {
      return {
        id: generateId('dec'),
        type: 'button',
        tabindex: -1,
        'aria-label': 'decrease value',
        'aria-controls': generateId('input'),
        'x-bind:disabled': () => api.value <= api.min,
        'x-on:click': () => api.decrement(),
      };
    },
  },
});
