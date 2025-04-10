import { defineScope, defineComponent } from '../utils/define-component';

interface MediaGalleryState {
  medias: any[];
  defaultMedias: any[];
  selectedIndex: number;
  scrollAmount: number;
  hasOverflowAbove: boolean;
  hasOverflowBelow: boolean;
  selectedMedia: any | null;
  scrollThumbs(direction: 'up' | 'down'): void;
  onMediasChanged(event: CustomEvent): void;
}

interface ThumbsScope {
  checkOverflow(): void;
}

interface ThumbScope {
  index: number;
  isSelected: boolean;
  tabIndex: number;
  select(): void;
  onKeydown(event: KeyboardEvent): void;
}

type MediaGalleryAPI = MediaGalleryState & {
  $thumbs: ThumbsScope;
  $thumb: ThumbScope;
};

export default defineComponent<MediaGalleryAPI>({
  name: 'media-gallery',

  setup(props) {
    let thumbsEl: HTMLElement | null = null;

    return {
      medias: [],
      defaultMedias: [],
      selectedIndex: 0,
      scrollAmount: props.scrollAmount ?? 100,

      hasOverflowAbove: false,
      hasOverflowBelow: false,

      get selectedMedia() {
        return this.medias[this.selectedIndex] ?? null;
      },

      scrollThumbs(direction: 'up' | 'down') {
        if (!thumbsEl) return;

        thumbsEl.scrollBy({
          top: direction === 'up' ? -this.scrollAmount : this.scrollAmount,
          behavior: 'smooth',
        });
      },

      onMediasChanged(event: CustomEvent) {
        const images = event.detail?.images || [];
        const videos = event.detail?.videos || [];

        this.medias = images.length || videos.length ? [...images, ...videos] : [...this.defaultMedias];

        this.selectedIndex = 0;
      },

      init() {
        this.medias = props.medias || [];
        this.defaultMedias = [...this.medias];

        thumbsEl = this.$el.querySelector('[x-media-gallery\\:thumbs]') as HTMLElement;

        window.addEventListener('variant-medias-change', this.onMediasChanged.bind(this) as EventListener);
      },

      destroy() {
        window.removeEventListener('variant-medias-change', this.onMediasChanged as EventListener);
      },
    };
  },

  parts: {
    thumbs: defineScope<MediaGalleryAPI, 'thumbs', ThumbsScope>({
      name: 'thumbs',

      setup(api, el) {
        return {
          checkOverflow() {
            api.hasOverflowAbove = el.scrollTop > 0;
            api.hasOverflowBelow = el.scrollTop + el.clientHeight < el.scrollHeight;
          },
        };
      },

      bindings(_, scope) {
        return {
          'x-on:scroll': () => scope.checkOverflow(),
          'x-init'() {
            this.$nextTick(() => {
              scope.checkOverflow();
            });
          },
        };
      },
    }),

    scrollUpTrigger(api) {
      return {
        'x-show': () => api.hasOverflowAbove,
        'x-on:click': () => api.scrollThumbs('up'),
      };
    },

    scrollDownTrigger(api) {
      return {
        'x-show': () => api.hasOverflowBelow,
        'x-on:click': () => api.scrollThumbs('down'),
      };
    },

    thumb: defineScope<MediaGalleryAPI, 'thumb', ThumbScope>({
      name: 'thumb',

      setup(api, el, ctx) {
        const index = Number(ctx.value);
        let thumbEls: NodeListOf<HTMLElement> | null = null;

        const getAllThumbs = () => {
          if (!thumbEls) {
            const root = el.closest('[x-media-gallery]');
            thumbEls =
              root?.querySelectorAll('[x-media-gallery\\:thumb]') ?? ([] as unknown as NodeListOf<HTMLElement>);
          }

          return thumbEls;
        };

        return {
          index,

          get isSelected() {
            return api.selectedIndex === index;
          },

          select() {
            api.selectedIndex = index;
          },

          get tabIndex() {
            return this.isSelected ? 0 : -1;
          },

          onKeydown(event: KeyboardEvent) {
            const keys = ['ArrowRight', 'ArrowDown', 'ArrowLeft', 'ArrowUp'];
            if (!keys.includes(event.key)) return;

            event.preventDefault();

            let nextIndex = api.selectedIndex;

            if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
              nextIndex = (api.selectedIndex + 1) % api.medias.length;
            }

            if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
              nextIndex = (api.selectedIndex - 1 + api.medias.length) % api.medias.length;
            }

            api.selectedIndex = nextIndex;

            const thumbs = getAllThumbs();
            const next = thumbs[nextIndex];
            next?.focus();
          },
        };
      },

      bindings(_, scope) {
        return {
          'x-on:click': () => scope.select(),
          'x-on:keydown': (e: KeyboardEvent) => scope.onKeydown(e),
          'x-bind:data-selected': () => (scope.isSelected ? 'true' : null),
          'x-bind:tabindex': () => scope.tabIndex,
          'x-bind:aria-current': () => (scope.isSelected ? 'true' : null),
          'x-bind:role': () => 'button',
        };
      },
    }),
  },
});
