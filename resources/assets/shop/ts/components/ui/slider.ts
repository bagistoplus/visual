import { defineComponent } from '../../utils/define-component';

interface SliderAPI {
  value: number[];
  min: number;
  max: number;
  step: number;
  minStepsBetweenThumbs: number | undefined;
  activeIndex: number | null;
  setActiveIndex(index: number): void;
  setValue(index: number, value: number): void;
  getPercent(index: number): number;
}

export default defineComponent<SliderAPI>({
  name: 'slider',

  setup: (props) => ({
    value: props.value || [0],
    min: props.min ?? 0,
    max: props.max ?? 100,
    step: props.step ?? 1,
    minStepsBetweenThumbs: props.minStepsBetweenThumbs,
    activeIndex: null,

    setActiveIndex(index) {
      this.activeIndex = index;
    },

    setValue(index: number, newValue: number) {
      const clamped = Math.max(this.min, Math.min(this.max, newValue));
      let stepValue = Math.round(clamped / this.step) * this.step;

      const prev = this.value[index - 1];
      const next = this.value[index + 1];

      if (this.minStepsBetweenThumbs) {
        const stepDistance = this.minStepsBetweenThumbs * this.step;
        if (index > 0 && stepValue < prev + stepDistance) {
          stepValue = prev + stepDistance;
        } else if (
          index < this.value.length - 1 &&
          stepValue > next - stepDistance
        ) {
          stepValue = next - stepDistance;
        }
      }

      this.value[index] = stepValue;

      this.$dispatch('change', this.value);
    },

    getPercent(index) {
      const range = this.max - this.min;
      return ((this.value[index] - this.min) / range) * 100;
    },
  }),

  parts: {
    root(api) {
      return {
        role: 'group',
      };
    },

    control() {
      return {
        role: 'presentation',
      };
    },

    track() {
      return {
        'data-part': 'track',
      };
    },

    range(api, el) {
      return {
        'data-part': 'range',
        'x-bind:style': () => {
          const [start, end] = api.value.map((_, i) => api.getPercent(i));
          return `left:${start}%; width:${end - start}%`;
        },
      };
    },

    thumb(api, el, { value: index }) {
      return {
        role: 'slider',
        tabindex: 0,
        'x-bind:style': () => `left: ${api.getPercent(index)}%`,
        'x-bind:data-index': index,
        'x-bind:aria-valuemin': () => api.min,
        'x-bind:aria-valuemax': () => api.max,
        'x-bind:aria-valuenow': () => api.value[index],
        'x-bind:data-disabled': () => null,

        'x-on:keydown': (e: KeyboardEvent) => {
          const key = e.key;
          if (key === 'ArrowRight' || key === 'ArrowUp')
            api.setValue(index, api.value[index] + api.step);
          if (key === 'ArrowLeft' || key === 'ArrowDown')
            api.setValue(index, api.value[index] - api.step);
        },

        'x-on:pointerdown'(e: PointerEvent) {
          e.preventDefault();
          el.setPointerCapture(e.pointerId);
          api.setActiveIndex(index);
        },

        'x-on:pointermove'(e: PointerEvent) {
          if (api.activeIndex !== index || !el.offsetParent) return;

          const rect = el.offsetParent!.getBoundingClientRect();
          const percent = (e.clientX - rect.left) / rect.width;
          const value = api.min + percent * (api.max - api.min);
          api.setValue(index, Math.round(value / api.step) * api.step);
        },

        'x-on:pointerup'(e: PointerEvent) {
          el.releasePointerCapture(e.pointerId);
          api.setActiveIndex(-1);
        },
      };
    },

    input(api, _, { value: index }) {
      return {
        min: api.min,
        max: api.max,
        'x-bind:value': () => api.value[index],
        'x-on:input': (e: Event) => {
          const target = e.target as HTMLInputElement;
          const next = Number(target.value);
          api.setValue(index, next);
        },
      };
    },

    hiddenInput(api, _, { value: index }) {
      return {
        type: 'hidden',
        'x-bind:value': () => api.value[index],
      };
    },
  },
});
