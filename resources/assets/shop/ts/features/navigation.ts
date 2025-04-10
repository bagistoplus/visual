import { defineScope, defineComponent } from '../utils/define-component';

interface NavigationState {
  dropdownOpen: boolean;
  activeItem: number | null;
  hideDelay: number;
  hideTimer: ReturnType<typeof setTimeout> | null;

  openDropdown(triggerEl: HTMLElement, id: number): void;
  closeDropdown(): void;
  startHideTimer(): void;
  cancelHideTimer(): void;
  positionDropdown(triggerEl: HTMLElement): void;
  isActive(id: number): boolean;
}

interface NavigationItemScope {
  id: number;
  isActive: boolean;
  open(): void;
}

interface NavigationSectionScope {
  id: number;
  isActive: boolean;
}

type NavigationAPI = NavigationState & {
  $item: NavigationItemScope;
  $section: NavigationSectionScope;
};

export default defineComponent<NavigationAPI>({
  name: 'navigation',

  setup() {
    return {
      dropdownOpen: false,
      activeItem: null,
      hideDelay: 200,
      hideTimer: null,

      openDropdown(triggerEl, id) {
        this.dropdownOpen = true;
        this.activeItem = id;
        this.$nextTick(() => {
          this.positionDropdown(triggerEl);
        });
      },

      closeDropdown() {
        this.dropdownOpen = false;
        this.activeItem = null;
      },

      startHideTimer() {
        this.hideTimer = setTimeout(() => {
          this.closeDropdown();
        }, this.hideDelay);
      },

      cancelHideTimer() {
        if (this.hideTimer) {
          clearTimeout(this.hideTimer);
        }
      },

      positionDropdown(triggerEl) {
        this.cancelHideTimer();

        requestAnimationFrame(() => {
          const triggerRect = triggerEl.getBoundingClientRect();
          const dropdown = this.$refs.menuDropdown as HTMLElement;
          const dropdownWidth = dropdown.offsetWidth;
          const triggerCenter = triggerRect.left + triggerRect.width / 2;
          let left = triggerCenter - dropdownWidth / 2;

          const minLeft = 100;
          const maxLeft = window.innerWidth - dropdownWidth - 16;

          if (left < minLeft) left = minLeft;
          if (left > maxLeft) left = maxLeft;

          dropdown.style.left = `${left}px`;
        });
      },

      isActive(id) {
        return this.activeItem === id;
      },
    };
  },

  parts: {
    item: defineScope<NavigationAPI, 'item', NavigationItemScope>({
      name: 'item',

      setup(api, el, { value }) {
        const id = Number(value);

        return {
          id,
          get isActive() {
            return api.isActive(id);
          },

          open() {
            api.openDropdown(el, id);
          },
        };
      },

      bindings(api, scope) {
        return {
          'aria-haspopup': 'true',
          'x-on:mouseover': () => scope.open(),
          'x-on:mouseleave': () => api.startHideTimer(),
          'x-bind:aria-expanded': () => api.dropdownOpen && scope.isActive,
          'x-bind:data-state': () => (scope.isActive ? 'active' : 'inactive'),
        };
      },
    }),

    section: defineScope<NavigationAPI, 'section', NavigationSectionScope>({
      name: 'section',

      setup(api, el, { value }) {
        const id = Number(value);

        return {
          id,
          get isActive() {
            return api.isActive(id);
          },
        };
      },

      bindings(_, scope) {
        return {
          'x-show': () => scope.isActive,
          'x-bind:data-state': () => (scope.isActive ? 'open' : 'closed'),
        };
      },
    }),

    dropdown(api) {
      return {
        'x-ref': 'menuDropdown',
        'x-show': () => api.dropdownOpen,
        'x-on:mouseover': () => api.cancelHideTimer(),
        'x-on:mouseleave': () => api.startHideTimer(),
      };
    },

    subItem(api) {
      return {
        'x-on:click': () => api.closeDropdown(),
      };
    },
  },
});
