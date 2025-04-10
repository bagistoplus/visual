import type { Alpine as AlpineType } from 'alpinejs';

interface AddToCompareProps {
  productId: number;
  userLoggedIn: boolean;
  messages: {
    alreadyInCompare: string;
    addedToCompare: string;
  };
}

export default function (Alpine: AlpineType) {
  Alpine.data('VisualAddToCompare', ({ productId, userLoggedIn, messages }: AddToCompareProps) => ({
    productId,
    userLoggedIn: userLoggedIn ?? false,
    messages: {
      alreadyInCompare: messages.alreadyInCompare ?? 'The product is already in compare list',
      addedToCompare: messages.addedToCompare ?? 'Successfully added product to compare list',
    },

    handle() {
      if (this.userLoggedIn) {
        this.$wire.call('handle');
        return;
      }

      const localStoredCompareItems = JSON.parse(localStorage.getItem('compare_items') as string) || [];

      if (localStoredCompareItems.includes(this.productId)) {
        this.$toaster.warning(this.messages.alreadyInCompare);
      } else {
        localStoredCompareItems.push(this.productId);
        localStorage.setItem('compare_items', JSON.stringify(localStoredCompareItems));
        this.$toaster.success(this.messages.addedToCompare);
      }
    },
  }));
}
