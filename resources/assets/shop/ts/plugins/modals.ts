import type { Alpine as AlpineType } from 'alpinejs';

export default function (Alpine: AlpineType) {
  Alpine.magic('modals', () => (name: string) => ({
    show() {
      window.dispatchEvent(new CustomEvent('modal:open', { detail: name }));
    },

    hide() {
      window.dispatchEvent(new CustomEvent('modal:hide', { detail: name }));
    },
  }));
}
