import { hash } from 'bcryptjs';
import { prisma } from '../lib/db/prisma';

async function main() {
  const categoryRows = await Promise.all(
    [
      ['Ayam & Daging', 'ayam-daging'],
      ['Ikan & Seafood', 'ikan-seafood'],
      ['Sayur & Buah', 'sayur-buah'],
      ['Bumbu & Rempah', 'bumbu-rempah'],
    ].map(([name, slug]) =>
      prisma.category.upsert({
        where: { slug },
        update: { name },
        create: {
          name,
          slug,
          description: `Bahan ${name.toLowerCase()} segar untuk masak sehari-hari.`,
        },
      }),
    ),
  );
  const category = Object.fromEntries(
    categoryRows.map((item) => [item.slug, item.id]),
  );
  const productRows = [
    [
      'Dada Ayam Fillet 500 g',
      'dada-ayam-fillet-500g',
      'NST-AYM-001',
      'ayam-daging',
      42000,
      24,
      true,
    ],
    [
      'Fillet Ikan Dori 500 g',
      'fillet-ikan-dori-500g',
      'NST-IKN-002',
      'ikan-seafood',
      48000,
      11,
      true,
    ],
    [
      'Pakcoy Segar 250 g',
      'pakcoy-segar-250g',
      'NST-SYR-003',
      'sayur-buah',
      12000,
      32,
      true,
    ],
    [
      'Paket Bumbu Dasar Merah',
      'paket-bumbu-dasar-merah',
      'NST-BMB-004',
      'bumbu-rempah',
      24000,
      4,
      true,
    ],
    [
      'Ayam Potong 8 ±1 kg',
      'ayam-potong-8-1kg',
      'NST-AYM-005',
      'ayam-daging',
      58000,
      18,
      false,
    ],
    [
      'Fillet Ikan Kakap 500 g',
      'fillet-ikan-kakap-500g',
      'NST-IKN-006',
      'ikan-seafood',
      62000,
      9,
      false,
    ],
    [
      'Paket Sayur Sop 500 g',
      'paket-sayur-sop-500g',
      'NST-SYR-007',
      'sayur-buah',
      18000,
      27,
      false,
    ],
    [
      'Bawang Putih Kupas 250 g',
      'bawang-putih-kupas-250g',
      'NST-BMB-008',
      'bumbu-rempah',
      20000,
      0,
      false,
    ],
  ] as const;
  const seededProducts = [];
  for (const [
    name,
    slug,
    sku,
    categorySlug,
    price,
    stock,
    featured,
  ] of productRows)
    seededProducts.push(
      await prisma.product.upsert({
        where: { slug },
        update: {
          name,
          sku,
          categoryId: category[categorySlug],
          price,
          stock,
          featured,
        },
        create: {
          name,
          slug,
          sku,
          categoryId: category[categorySlug],
          price,
          stock,
          featured,
          shortDescription: `${name} yang segar, bersih, dan siap diolah.`,
          description: `${name} dipilih dan ditangani dengan bersih agar praktis untuk kebutuhan masak keluarga.`,
        },
      }),
    );
  const adminUsername = process.env.ADMIN_USERNAME;
  const adminPassword = process.env.ADMIN_PASSWORD;
  const adminEmail = process.env.ADMIN_EMAIL;
  if (adminUsername && adminPassword && adminEmail)
    await prisma.admin.upsert({
      where: { username: adminUsername },
      update: {
        email: adminEmail,
        passwordHash: await hash(adminPassword, 12),
      },
      create: {
        username: adminUsername,
        email: adminEmail,
        passwordHash: await hash(adminPassword, 12),
      },
    });
  const customer = await prisma.customer.upsert({
    where: { email: 'pelanggan@example.com' },
    update: {},
    create: {
      name: 'Pelanggan Contoh',
      email: 'pelanggan@example.com',
      provider: 'credentials',
    },
  });
  await prisma.order.upsert({
    where: { orderNumber: 'NVS-SEED-001' },
    update: {},
    create: {
      orderNumber: 'NVS-SEED-001',
      customerId: customer.id,
      customerNameSnapshot: customer.name,
      customerEmailSnapshot: customer.email,
      phoneSnapshot: '081234567890',
      addressSnapshot: 'Jl. Contoh No. 10, Denpasar, Bali',
      subtotal: 84000,
      shippingTotal: 25000,
      total: 109000,
      status: 'PENDING',
      paymentStatus: 'UNPAID',
      items: {
        create: [
          {
            productId: seededProducts[0].id,
            productName: seededProducts[0].name,
            productSku: seededProducts[0].sku,
            unitPrice: seededProducts[0].price,
            quantity: 2,
            lineTotal: 84000,
          },
        ],
      },
    },
  });
  await prisma.feedback.createMany({
    data: [
      {
        customerId: customer.id,
        name: customer.name,
        email: customer.email,
        message: 'Ayamnya segar dan pengemasannya rapi.',
      },
      {
        name: 'Raka',
        email: 'raka@example.com',
        message: 'Tolong tambahkan lebih banyak pilihan ikan dan sayur.',
      },
    ],
  });
}

main()
  .then(() => prisma.$disconnect())
  .catch(async (error) => {
    console.error(error);
    await prisma.$disconnect();
    process.exit(1);
  });
