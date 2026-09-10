const MAX_QUANTITY = 100;

const integer = (value) => {
    const parsed = Number(value);

    return Number.isSafeInteger(parsed) ? parsed : null;
};

export const clampQuantity = (value, stock) => {
    const availableStock = integer(stock);
    const requestedQuantity = integer(value);

    if (availableStock === null || availableStock < 1) return 0;

    return Math.min(Math.max(requestedQuantity ?? 1, 1), availableStock, MAX_QUANTITY);
};

export const normalizeCartItem = (item, quantity = item?.quantity) => {
    if (!item || typeof item !== 'object') return null;

    const id = integer(item.id);
    const price = integer(item.price);
    const stock = integer(item.stock);
    const name = typeof item.name === 'string' ? item.name.trim().slice(0, 150) : '';
    const normalizedQuantity = clampQuantity(quantity, stock);

    if (id === null || id < 1 || price === null || price < 0 || stock === null || stock < 1 || !name || normalizedQuantity < 1) {
        return null;
    }

    return {
        id,
        name,
        price,
        stock,
        image: typeof item.image === 'string' ? item.image : null,
        quantity: normalizedQuantity,
    };
};

export const sanitizeCart = (items) => {
    if (!Array.isArray(items)) return [];

    const normalizedItems = new Map();

    items.forEach((item) => {
        const normalized = normalizeCartItem(item);
        if (!normalized) return;

        const existing = normalizedItems.get(normalized.id);
        if (existing) {
            normalized.quantity = clampQuantity(existing.quantity + normalized.quantity, normalized.stock);
        }
        normalizedItems.set(normalized.id, normalized);
    });

    return [...normalizedItems.values()];
};
