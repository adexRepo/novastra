import { describe, expect, it } from 'vitest';
import { safeCallbackUrl } from './callback-url';

describe('safeCallbackUrl', () => {
  it('keeps local storefront paths', () => {
    expect(safeCallbackUrl('/checkout')).toBe('/checkout');
    expect(safeCallbackUrl('/orders/order-1?from=account')).toBe(
      '/orders/order-1?from=account',
    );
  });

  it('rejects external and protocol-relative redirects', () => {
    expect(safeCallbackUrl('https://example.com/steal')).toBe('/orders');
    expect(safeCallbackUrl('//example.com/steal')).toBe('/orders');
  });

  it('uses the requested fallback for invalid values', () => {
    expect(safeCallbackUrl(undefined, '/checkout')).toBe('/checkout');
    expect(safeCallbackUrl('', '/checkout')).toBe('/checkout');
  });
});
