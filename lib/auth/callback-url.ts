const localOrigin = 'https://novastra.local';

export function safeCallbackUrl(value: unknown, fallback = '/orders') {
  if (typeof value !== 'string' || value.length === 0 || value.length > 300) {
    return fallback;
  }

  try {
    const parsed = new URL(value, localOrigin);
    if (parsed.origin !== localOrigin) return fallback;
    return `${parsed.pathname}${parsed.search}${parsed.hash}`;
  } catch {
    return fallback;
  }
}
