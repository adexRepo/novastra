import { randomBytes } from 'node:crypto';
import { Prisma } from '@prisma/client';
import { prisma } from '@/lib/db/prisma';
import {
  calculateShipping,
  canTransitionOrder,
  canTransitionPayment,
} from './order.rules';

export type CreateOrderInput = {
  customerId: string;
  customerName: string;
  customerEmail: string;
  phone: string;
  address: string;
  notes?: string;
  items: { productId: string; quantity: number }[];
};

function orderNumber() {
  const date = new Date().toISOString().slice(2, 10).replaceAll('-', '');
  return `NVS-${date}-${randomBytes(3).toString('hex').toUpperCase()}`;
}

export async function createOrder(input: CreateOrderInput) {
  if (input.items.length === 0) throw new Error('EMPTY_ORDER');
  return prisma.$transaction(
    async (tx) => {
      const requested = new Map(
        input.items.map((item) => [item.productId, item.quantity]),
      );
      if (
        requested.size !== input.items.length ||
        [...requested.values()].some(
          (quantity) => !Number.isSafeInteger(quantity) || quantity < 1,
        )
      )
        throw new Error('INVALID_ITEMS');
      const products = await tx.product.findMany({
        where: { id: { in: [...requested.keys()] }, status: 'ACTIVE' },
      });
      if (products.length !== requested.size)
        throw new Error('PRODUCT_UNAVAILABLE');
      const items = products.map((product) => {
        const quantity = requested.get(product.id)!;
        if (quantity > product.stock) throw new Error('INSUFFICIENT_STOCK');
        return {
          productId: product.id,
          productName: product.name,
          productSku: product.sku,
          unitPrice: product.price,
          quantity,
          lineTotal: product.price.mul(quantity),
        };
      });
      const subtotal = items.reduce(
        (sum, item) => sum.add(item.lineTotal),
        new Prisma.Decimal(0),
      );
      const shipping = new Prisma.Decimal(
        calculateShipping(subtotal.toNumber()),
      );
      return tx.order.create({
        data: {
          orderNumber: orderNumber(),
          customerId: input.customerId,
          customerNameSnapshot: input.customerName,
          customerEmailSnapshot: input.customerEmail,
          phoneSnapshot: input.phone,
          addressSnapshot: input.address,
          notes: input.notes,
          subtotal,
          shippingTotal: shipping,
          total: subtotal.add(shipping),
          items: { create: items },
        },
        include: { items: true },
      });
    },
    { isolationLevel: Prisma.TransactionIsolationLevel.Serializable },
  );
}

export async function transitionOrder(
  orderId: string,
  target: 'CONFIRMED' | 'PROCESSING' | 'SHIPPED' | 'COMPLETED' | 'CANCELLED',
) {
  return prisma.$transaction(
    async (tx) => {
      const order = await tx.order.findUnique({
        where: { id: orderId },
        include: { items: true },
      });
      if (!order) throw new Error('ORDER_NOT_FOUND');
      if (!canTransitionOrder(order.status, target))
        throw new Error('INVALID_ORDER_TRANSITION');
      if (order.status === target) return order;
      if (target === 'CONFIRMED' && !order.stockDeducted) {
        for (const item of order.items) {
          if (!item.productId) throw new Error('PRODUCT_UNAVAILABLE');
          const result = await tx.product.updateMany({
            where: {
              id: item.productId,
              status: 'ACTIVE',
              stock: { gte: item.quantity },
            },
            data: { stock: { decrement: item.quantity } },
          });
          if (result.count !== 1) throw new Error('INSUFFICIENT_STOCK');
        }
      }
      if (
        target === 'CANCELLED' &&
        order.stockDeducted &&
        !order.stockRestored
      ) {
        for (const item of order.items)
          if (item.productId)
            await tx.product.update({
              where: { id: item.productId },
              data: { stock: { increment: item.quantity } },
            });
      }
      return tx.order.update({
        where: { id: orderId },
        data: {
          status: target,
          stockDeducted: target === 'CONFIRMED' ? true : order.stockDeducted,
          stockRestored:
            target === 'CANCELLED' && order.stockDeducted
              ? true
              : order.stockRestored,
        },
      });
    },
    { isolationLevel: Prisma.TransactionIsolationLevel.Serializable },
  );
}

export async function transitionPayment(
  orderId: string,
  target: 'PENDING' | 'PAID' | 'FAILED' | 'REFUNDED',
) {
  return prisma.$transaction(async (tx) => {
    const order = await tx.order.findUnique({ where: { id: orderId } });
    if (!order) throw new Error('ORDER_NOT_FOUND');
    if (!canTransitionPayment(order.paymentStatus, target))
      throw new Error('INVALID_PAYMENT_TRANSITION');
    if (order.paymentStatus === target) return order;
    return tx.order.update({
      where: { id: orderId },
      data: { paymentStatus: target },
    });
  });
}
