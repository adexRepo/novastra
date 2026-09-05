import { sendMail } from '@/lib/email/mailer';

export async function notifyOrderCreated(order: {
  orderNumber: string;
  customerEmailSnapshot: string;
}) {
  const adminEmail = process.env.ADMIN_EMAIL;
  const tasks = [
    sendMail({
      to: order.customerEmailSnapshot,
      subject: `Pesanan ${order.orderNumber} diterima`,
      text: `Terima kasih. Pesanan ${order.orderNumber} sudah diterima Novastra dan akan ditinjau oleh tim kami.`,
    }),
  ];
  if (adminEmail)
    tasks.push(
      sendMail({
        to: adminEmail,
        subject: `Pesanan baru ${order.orderNumber}`,
        text: `Pesanan ${order.orderNumber} baru saja dibuat. Buka dashboard Novastra untuk meninjaunya.`,
      }),
    );
  const results = await Promise.allSettled(tasks);
  for (const result of results)
    if (result.status === 'rejected')
      console.error('Order notification failed', {
        orderNumber: order.orderNumber,
        reason:
          result.reason instanceof Error ? result.reason.message : 'UNKNOWN',
      });
}
