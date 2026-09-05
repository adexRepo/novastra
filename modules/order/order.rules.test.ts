import { describe, expect, it } from 'vitest';
import {
  calculateShipping,
  calculateSubtotal,
  canTransitionOrder,
  canTransitionPayment,
} from './order.rules';

describe('order totals', () => {
  it('calculates integer rupiah totals', () =>
    expect(
      calculateSubtotal([
        { unitPrice: 189000, quantity: 2 },
        { unitPrice: 149000, quantity: 1 },
      ]),
    ).toBe(527000));
  it('rejects unsafe quantities', () =>
    expect(() => calculateSubtotal([{ unitPrice: 1000, quantity: 0 }])).toThrow(
      'INVALID_QUANTITY',
    ));
  it('applies the free shipping threshold', () => {
    expect(calculateShipping(249999)).toBe(25000);
    expect(calculateShipping(250000)).toBe(0);
  });
});

describe('state transitions', () => {
  it('allows the intended order flow and idempotent repeats', () => {
    expect(canTransitionOrder('PENDING', 'CONFIRMED')).toBe(true);
    expect(canTransitionOrder('CONFIRMED', 'CONFIRMED')).toBe(true);
    expect(canTransitionOrder('COMPLETED', 'CANCELLED')).toBe(false);
  });
  it('does not allow paid orders to become unpaid', () => {
    expect(canTransitionPayment('PAID', 'UNPAID')).toBe(false);
    expect(canTransitionPayment('PAID', 'REFUNDED')).toBe(true);
  });
});
