export const businessTimeZone = 'Asia/Makassar';

const dayMs = 86_400_000;

export function businessDayKey(date: Date) {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: businessTimeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(date);
  const value = Object.fromEntries(
    parts.map((part) => [part.type, part.value]),
  );
  return `${value.year}-${value.month}-${value.day}`;
}

export function lastBusinessDayKeys(total: number, now = new Date()) {
  return Array.from({ length: total }, (_, index) =>
    businessDayKey(new Date(now.getTime() - (total - index - 1) * dayMs)),
  );
}

export function percentage(current: number, total: number) {
  return total > 0 ? (current / total) * 100 : 0;
}

export function percentageChange(current: number, previous: number) {
  if (previous === 0) return current > 0 ? 100 : 0;
  return ((current - previous) / previous) * 100;
}

export type HealthInput = {
  cancellationRate: number;
  lowStockCount: number;
  paidOrders: number;
  paidRate: number;
  revenueTrend: number;
};

export function evaluateBusinessHealth(input: HealthInput) {
  if (input.paidOrders === 0) {
    return {
      label: 'Belum cukup data',
      tone: 'neutral' as const,
      summary: 'Belum ada pesanan berbayar dalam 30 hari terakhir.',
    };
  }

  if (
    input.cancellationRate > 20 ||
    input.paidRate < 50 ||
    input.revenueTrend < -20
  ) {
    return {
      label: 'Perlu perhatian',
      tone: 'warning' as const,
      summary: 'Ada indikator utama yang menurun dan perlu diperiksa.',
    };
  }

  if (
    input.cancellationRate <= 10 &&
    input.paidRate >= 70 &&
    input.revenueTrend >= 0 &&
    input.lowStockCount <= 3
  ) {
    return {
      label: 'Sehat',
      tone: 'healthy' as const,
      summary: 'Penjualan dan kualitas pesanan berada dalam kondisi baik.',
    };
  }

  return {
    label: 'Cukup stabil',
    tone: 'stable' as const,
    summary: 'Bisnis berjalan, tetapi masih ada angka yang perlu dipantau.',
  };
}
