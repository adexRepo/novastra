import { cn } from '@/lib/utils';

export function ProductImage({
  position,
  name,
  className,
}: {
  position: string;
  name: string;
  className?: string;
}) {
  return (
    <div
      className={cn(
        "absolute inset-0 bg-[url('/uploads/products/novastra-fresh-collection.webp')] bg-[length:200%_200%]",
        className,
      )}
      style={{ backgroundPosition: position }}
      aria-hidden="true"
      data-product-name={name}
    />
  );
}
