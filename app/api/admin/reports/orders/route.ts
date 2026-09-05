import ExcelJS from 'exceljs';
import { NextResponse } from 'next/server';
import { requireAdmin } from '@/lib/auth/guards';
import { prisma } from '@/lib/db/prisma';

export async function GET() {
  await requireAdmin();
  const orders = await prisma.order.findMany({
    orderBy: { createdAt: 'desc' },
    take: 5000,
  });
  const workbook = new ExcelJS.Workbook();
  const sheet = workbook.addWorksheet('Pesanan');
  sheet.columns = [
    { header: 'Nomor', key: 'number', width: 24 },
    { header: 'Tanggal', key: 'date', width: 20 },
    { header: 'Pelanggan', key: 'customer', width: 28 },
    { header: 'Email', key: 'email', width: 32 },
    { header: 'Status', key: 'status', width: 16 },
    { header: 'Pembayaran', key: 'payment', width: 16 },
    { header: 'Total (IDR)', key: 'total', width: 18 },
  ];
  for (const order of orders)
    sheet.addRow({
      number: order.orderNumber,
      date: order.createdAt.toISOString(),
      customer: order.customerNameSnapshot,
      email: order.customerEmailSnapshot,
      status: order.status,
      payment: order.paymentStatus,
      total: order.total.toNumber(),
    });
  sheet.getRow(1).font = { bold: true };
  sheet.getColumn('total').numFmt = '#,##0';
  const buffer = await workbook.xlsx.writeBuffer();
  return new NextResponse(Buffer.from(buffer), {
    headers: {
      'content-type':
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'content-disposition': `attachment; filename="novastra-orders-${new Date().toISOString().slice(0, 10)}.xlsx"`,
      'cache-control': 'no-store',
    },
  });
}
