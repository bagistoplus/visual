import { defineComponent } from '../utils/define-component';

interface ProductsCompareAPI {
  isUserLoggedIn: boolean;
  productIds: number[];
  messages: {
    itemRemoved: string;
    removeAll: string;
  };
  removeItem(id: number): void;
  removeAllItems(): void;
}

export default defineComponent<ProductsCompareAPI>({
  name: 'products-compare',

  setup(props) {
    const stored = localStorage.getItem('compare_items');
    const productIds = stored ? JSON.parse(stored) : [];

    return {
      productIds,
      isUserLoggedIn: props.isUserLoggedIn ?? false,

      messages: {
        itemRemoved: props.messages?.itemRemoved || 'Item successfully removed from compare list',
        removeAll: props.messages?.removeAll || 'Compare list successfully cleared',
      },

      init() {
        if (this.productIds.length > 0) {
          this.$wire.loadItems(this.productIds);
        }
      },

      removeItem(id: number) {
        if (this.isUserLoggedIn) {
          this.$wire.removeItem(id);
          return;
        }

        this.productIds = this.productIds.filter((pid) => pid !== id);
        localStorage.setItem('compare_items', JSON.stringify(this.productIds));
        this.$wire.loadItems(this.productIds);

        this.$toaster.success(this.messages.itemRemoved);
      },

      removeAllItems() {
        if (this.isUserLoggedIn) {
          this.$wire.removeAllItems();
          return;
        }

        localStorage.removeItem('compare_items');
        this.productIds = [];
        this.$wire.loadItems([]);

        this.$toaster.success(this.messages.removeAll);
      },
    };
  },

  parts: {
    remove(api, _, { value }) {
      return {
        'x-on:click.stop': () => api.removeItem(Number(value)),
      };
    },

    removeAll(api) {
      return {
        'x-on:click': () => api.removeAllItems(),
      };
    },
  },
});
