export const VisualEvents = {
  CART_UPDATED: 'visual:cart_updated',
  WISHLIST_UPDATED: 'visual:wishlist_updated',
  COMPARE_UPDATED: 'visual:compare_updated',
  SHIPPING_METHOD_SET: 'visual:shipping_method_set',
  COUPON_APPLIED: 'visual:coupon_applied',
  COUPON_REMOVED: 'visual:coupon_removed',
  PAYMENT_METHOD_SET: 'visual:payment_method_set',
} as const;

export type VisualEvent = (typeof VisualEvents)[keyof typeof VisualEvents];