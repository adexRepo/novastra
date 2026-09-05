export type PricedItem = { unitPrice: number; quantity: number };
export type OrderState =
  | 'PENDING'
  | 'CONFIRMED'
  | 'PROCESSING'
  | 'SHIPPED'
  | 'COMPLETED'
  | 'CANCELLED';
export type PaymentState =
  | 'UNPAID'
  | 'PENDING'
  | 'PAID'
  | 'FAILED'
  | 'REFUNDED';

export function calculateSubtotal(items: PricedItem[]) {
  return items.reduce((total, item) => {
    if (!Number.isSafeInteger(item.unitPrice) || item.unitPrice < 0)
      throw new Error('INVALID_PRICE');
    if (!Number.isSafeInteger(item.quantity) || item.quantity < 1)
      throw new Error('INVALID_QUANTITY');
    return total + item.unitPrice * item.quantity;
  }, 0);
}

export function calculateShipping(subtotal: number) {
  return subtotal >= 250_000 ? 0 : 25_000;
}

const orderTransitions: Record<OrderState, OrderState[]> = {
  PENDING: ['CONFIRMED', 'CANCELLED'],
  CONFIRMED: ['PROCESSING', 'CANCELLED'],
  PROCESSING: ['SHIPPED', 'CANCELLED'],
  SHIPPED: ['COMPLETED'],
  COMPLETED: [],
  CANCELLED: [],
};

const paymentTransitions: Record<PaymentState, PaymentState[]> = {
  UNPAID: ['PENDING', 'PAID', 'FAILED'],
  PENDING: ['PAID', 'FAILED'],
  PAID: ['REFUNDED'],
  FAILED: ['PENDING', 'PAID'],
  REFUNDED: [],
};

export function canTransitionOrder(from: OrderState, to: OrderState) {
  return from === to || orderTransitions[from].includes(to);
}
export function canTransitionPayment(from: PaymentState, to: PaymentState) {
  return from === to || paymentTransitions[from].includes(to);
}
