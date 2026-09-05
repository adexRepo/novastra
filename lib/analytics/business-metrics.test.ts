import { describe, expect, it } from 'vitest';
import {
  businessDayKey,
  evaluateBusinessHealth,
  percentage,
  percentageChange,
} from './business-metrics';

describe('business metrics', () => {
  it('uses the Makassar business day', () => {
    expect(businessDayKey(new Date('2026-08-29T17:30:00.000Z'))).toBe(
      '2026-08-30',
    );
  });

  it('calculates safe percentages', () => {
    expect(percentage(7, 10)).toBe(70);
    expect(percentage(1, 0)).toBe(0);
    expect(percentageChange(120, 100)).toBe(20);
    expect(percentageChange(10, 0)).toBe(100);
  });

  it('flags unhealthy order quality', () => {
    expect(
      evaluateBusinessHealth({
        cancellationRate: 25,
        lowStockCount: 1,
        paidOrders: 10,
        paidRate: 75,
        revenueTrend: 5,
      }).label,
    ).toBe('Perlu perhatian');
  });

  it('marks strong fundamentals as healthy', () => {
    expect(
      evaluateBusinessHealth({
        cancellationRate: 5,
        lowStockCount: 2,
        paidOrders: 10,
        paidRate: 80,
        revenueTrend: 12,
      }).label,
    ).toBe('Sehat');
  });
});
