import assert from 'node:assert/strict';
import test from 'node:test';
import { clampQuantity, normalizeCartItem, sanitizeCart } from '../../resources/js/cart.js';

test('quantity is always a positive integer within stock and business limits', () => {
    assert.equal(clampQuantity(-5, 10), 1);
    assert.equal(clampQuantity(Number.NaN, 10), 1);
    assert.equal(clampQuantity(20, 5), 5);
    assert.equal(clampQuantity(150, 200), 100);
    assert.equal(clampQuantity(1, 0), 0);
});

test('invalid persisted cart entries are discarded', () => {
    assert.deepEqual(sanitizeCart(null), []);
    assert.deepEqual(sanitizeCart([
        null,
        { id: -1, name: 'Invalid', price: 1000, stock: 2, quantity: 1 },
        { id: 1, name: '', price: 1000, stock: 2, quantity: 1 },
        { id: 2, name: 'NaN price', price: Number.NaN, stock: 2, quantity: 1 },
        { id: 3, name: 'No stock', price: 1000, stock: 0, quantity: 1 },
    ]), []);
});

test('persisted duplicate products are merged and clamped', () => {
    assert.deepEqual(sanitizeCart([
        { id: 1, name: 'Ayam', price: 40000, stock: 5, quantity: 3 },
        { id: '1', name: 'Ayam segar', price: 45000, stock: 4, quantity: 3 },
    ]), [{ id: 1, name: 'Ayam segar', price: 45000, stock: 4, image: null, quantity: 4 }]);
});

test('normalization keeps only safe product fields', () => {
    assert.deepEqual(normalizeCartItem({
        id: '12',
        name: '  Ikan Segar  ',
        price: '25000',
        stock: '8',
        image: '/uploads/products/ikan.webp',
        ignored: '<script>',
    }, -2), {
        id: 12,
        name: 'Ikan Segar',
        price: 25000,
        stock: 8,
        image: '/uploads/products/ikan.webp',
        quantity: 1,
    });
});
