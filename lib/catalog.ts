export type Product = {
  id: string;
  slug: string;
  sku: string;
  name: string;
  category: string;
  categorySlug: string;
  shortDescription: string;
  description: string;
  price: number;
  stock: number;
  featured: boolean;
  position: string;
};

export const products: Product[] = [
  {
    id: 'p1',
    slug: 'dada-ayam-fillet-500g',
    sku: 'NST-AYM-001',
    name: 'Dada Ayam Fillet 500 g',
    category: 'Ayam & Daging',
    categorySlug: 'ayam-daging',
    shortDescription: 'Dada ayam tanpa tulang, bersih dan siap diolah.',
    description:
      'Dada ayam fillet dipotong dan dikemas pada hari yang sama untuk menjaga kesegaran. Cocok untuk tumisan, ayam panggang, sup, atau stok meal prep keluarga.',
    price: 42000,
    stock: 24,
    featured: true,
    position: '0% 0%',
  },
  {
    id: 'p2',
    slug: 'fillet-ikan-dori-500g',
    sku: 'NST-IKN-002',
    name: 'Fillet Ikan Dori 500 g',
    category: 'Ikan & Seafood',
    categorySlug: 'ikan-seafood',
    shortDescription:
      'Fillet ikan lembut, tanpa duri besar, dan mudah dimasak.',
    description:
      'Fillet dori disimpan dalam rantai dingin dan dikemas rapi. Teksturnya lembut untuk digoreng, dikukus, atau dimasak dengan saus rumahan favorit.',
    price: 48000,
    stock: 11,
    featured: true,
    position: '100% 0%',
  },
  {
    id: 'p3',
    slug: 'pakcoy-segar-250g',
    sku: 'NST-SYR-003',
    name: 'Pakcoy Segar 250 g',
    category: 'Sayur & Buah',
    categorySlug: 'sayur-buah',
    shortDescription: 'Pakcoy renyah yang dipilih dan dikemas setiap pagi.',
    description:
      'Pakcoy segar dengan daun hijau dan batang yang renyah. Dicuci sebelum dimasak dan cocok untuk tumisan, sup, mi, atau hotpot.',
    price: 12000,
    stock: 32,
    featured: true,
    position: '0% 100%',
  },
  {
    id: 'p4',
    slug: 'paket-bumbu-dasar-merah',
    sku: 'NST-BMB-004',
    name: 'Paket Bumbu Dasar Merah',
    category: 'Bumbu & Rempah',
    categorySlug: 'bumbu-rempah',
    shortDescription: 'Cabai, bawang, jahe, dan rempah segar dalam satu paket.',
    description:
      'Takaran praktis untuk sambal, balado, dan masakan berbumbu merah. Isinya dipilih segar agar Anda tinggal membersihkan dan mengolah.',
    price: 24000,
    stock: 4,
    featured: true,
    position: '100% 100%',
  },
  {
    id: 'p5',
    slug: 'ayam-potong-8-1kg',
    sku: 'NST-AYM-005',
    name: 'Ayam Potong 8 ±1 kg',
    category: 'Ayam & Daging',
    categorySlug: 'ayam-daging',
    shortDescription:
      'Ayam segar dipotong delapan, cocok untuk masak keluarga.',
    description:
      'Ayam dipotong menjadi delapan bagian dengan ukuran seimbang dan dikemas dingin. Praktis untuk digoreng, diungkep, atau dibuat opor.',
    price: 58000,
    stock: 18,
    featured: false,
    position: '0% 0%',
  },
  {
    id: 'p6',
    slug: 'fillet-ikan-kakap-500g',
    sku: 'NST-IKN-006',
    name: 'Fillet Ikan Kakap 500 g',
    category: 'Ikan & Seafood',
    categorySlug: 'ikan-seafood',
    shortDescription: 'Daging ikan padat dan segar untuk lauk harian.',
    description:
      'Fillet kakap dengan rasa bersih dan tekstur yang padat. Cocok untuk sup ikan, panggang, atau saus asam manis.',
    price: 62000,
    stock: 9,
    featured: false,
    position: '100% 0%',
  },
  {
    id: 'p7',
    slug: 'paket-sayur-sop-500g',
    sku: 'NST-SYR-007',
    name: 'Paket Sayur Sop 500 g',
    category: 'Sayur & Buah',
    categorySlug: 'sayur-buah',
    shortDescription:
      'Wortel, kentang, buncis, kol, dan daun bawang dalam satu paket.',
    description:
      'Satu paket sayur dengan porsi pas untuk masakan keluarga. Bahan dipilih segar dan belum dipotong agar lebih tahan disimpan.',
    price: 18000,
    stock: 27,
    featured: false,
    position: '0% 100%',
  },
  {
    id: 'p8',
    slug: 'bawang-putih-kupas-250g',
    sku: 'NST-BMB-008',
    name: 'Bawang Putih Kupas 250 g',
    category: 'Bumbu & Rempah',
    categorySlug: 'bumbu-rempah',
    shortDescription: 'Bawang putih pilihan yang sudah dikupas bersih.',
    description:
      'Bawang putih siap cincang untuk mempercepat persiapan masak. Disimpan dingin dan dikemas dalam porsi rumah tangga.',
    price: 20000,
    stock: 0,
    featured: false,
    position: '100% 100%',
  },
];

export const categories = [
  { name: 'Ayam & Daging', slug: 'ayam-daging' },
  { name: 'Ikan & Seafood', slug: 'ikan-seafood' },
  { name: 'Sayur & Buah', slug: 'sayur-buah' },
  { name: 'Bumbu & Rempah', slug: 'bumbu-rempah' },
];

export function formatRupiah(value: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value);
}

export function getProduct(slug: string) {
  return products.find((product) => product.slug === slug);
}
