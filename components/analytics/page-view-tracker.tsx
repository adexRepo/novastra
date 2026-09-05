'use client';

import { useEffect } from 'react';
import { usePathname } from 'next/navigation';

const visitorStorageKey = 'novastra:anonymous-visitor';
const excludedPrefixes = ['/admin', '/api', '/login', '/checkout', '/orders'];

function isTrackablePath(path: string) {
  return !excludedPrefixes.some(
    (prefix) => path === prefix || path.startsWith(`${prefix}/`),
  );
}

export function PageViewTracker() {
  const pathname = usePathname();

  useEffect(() => {
    if (!isTrackablePath(pathname)) return;

    let visitorId = window.localStorage.getItem(visitorStorageKey);
    if (!visitorId) {
      visitorId = window.crypto.randomUUID();
      window.localStorage.setItem(visitorStorageKey, visitorId);
    }

    void fetch('/api/analytics/view', {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify({ path: pathname, visitorId }),
      keepalive: true,
    }).catch(() => undefined);
  }, [pathname]);

  return null;
}
