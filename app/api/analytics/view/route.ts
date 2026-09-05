import { z } from 'zod';
import { businessDayKey } from '@/lib/analytics/business-metrics';
import { prisma } from '@/lib/db/prisma';
import { allowRequest } from '@/lib/rate-limit';

const viewSchema = z.object({
  path: z.string().startsWith('/').max(200),
  visitorId: z.uuid(),
});

const excludedPrefixes = ['/admin', '/api', '/login', '/checkout', '/orders'];

function isTrackablePath(path: string) {
  return (
    !path.startsWith('//') &&
    !excludedPrefixes.some(
      (prefix) => path === prefix || path.startsWith(`${prefix}/`),
    )
  );
}

export async function POST(request: Request) {
  const origin = request.headers.get('origin');
  if (origin && origin !== new URL(request.url).origin) {
    return new Response(null, { status: 204 });
  }

  try {
    const parsed = viewSchema.safeParse(await request.json());
    if (!parsed.success || !isTrackablePath(parsed.data.path)) {
      return new Response(null, { status: 204 });
    }

    if (!allowRequest(`page-view:${parsed.data.visitorId}`, 30, 60_000)) {
      return new Response(null, { status: 204 });
    }

    const now = new Date();
    await prisma.pageView.create({
      data: {
        ...parsed.data,
        day: businessDayKey(now),
        createdAt: now,
      },
    });
  } catch {
    // Analytics must never interrupt the storefront.
  }

  return new Response(null, { status: 204 });
}
