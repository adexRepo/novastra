const key = 'novastra-cart-v1';
const readCart = () => {
    try {
        const items = JSON.parse(localStorage.getItem(key) || '[]');

        return Array.isArray(items) ? items : [];
    } catch {
        return [];
    }
};
const saveCart = (items) => {
    localStorage.setItem(key, JSON.stringify(items));
    window.dispatchEvent(new CustomEvent('novastra:cart'));
};
const money = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
const formatCurrencyInput = (input) => {
    const digits = input.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
    input.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};
const productImage = (value) => typeof value === 'string' && /^\/uploads\/products\/[a-z0-9-]+\.(jpg|png|webp)$/i.test(value)
    ? value
    : '/uploads/products/novastra-fresh-collection.webp';
const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;

    return node;
};

window.NovastraCart = {
    items: readCart,
    add(product, quantity = 1) {
        const items = readCart();
        const current = items.find((item) => item.id === product.id);
        if (current) current.quantity = Math.min(product.stock, current.quantity + quantity);
        else items.push({ ...product, quantity: Math.min(product.stock, quantity) });
        saveCart(items);
    },
    update(id, quantity) { saveCart(readCart().map((item) => item.id === id ? { ...item, quantity: Math.max(1, Math.min(item.stock, quantity)) } : item)); },
    remove(id) { saveCart(readCart().filter((item) => item.id !== id)); },
    clear() { saveCart([]); },
};

function updateCount() {
    const count = readCart().reduce((sum, item) => sum + item.quantity, 0);
    document.querySelectorAll('[data-cart-count]').forEach((element) => { element.textContent = count; });
}

function renderCart() {
    const root = document.querySelector('[data-cart-page]');
    if (!root) return;
    const items = readCart();
    root.replaceChildren();

    if (!items.length) {
        const empty = element('div', 'py-24 text-center');
        empty.append(element('h1', 'font-display text-3xl', 'Keranjang masih kosong.'));
        const productsLink = element('a', 'btn mt-6', 'Pilih produk');
        productsLink.href = '/products';
        empty.append(productsLink);
        root.append(empty);

        return;
    }

    const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const layout = element('div', 'grid gap-8 lg:grid-cols-[1fr_360px]');
    const list = element('div', 'space-y-3');

    items.forEach((item) => {
        const article = element('article', 'card flex gap-4 p-4');
        const image = element('img', 'size-24 rounded-xl object-cover');
        image.src = productImage(item.image);
        image.alt = '';

        const details = element('div', 'min-w-0 flex-1');
        details.append(element('h2', 'font-semibold', String(item.name)));
        details.append(element('p', 'mt-1 text-sm text-ink/60', money(item.price)));

        const controls = element('div', 'mt-4 flex items-center gap-3');
        const quantity = element('input', 'field mt-0 w-20');
        quantity.type = 'number';
        quantity.min = '1';
        quantity.max = String(item.stock);
        quantity.value = String(item.quantity);
        quantity.setAttribute('aria-label', `Jumlah ${String(item.name)}`);
        quantity.addEventListener('change', () => {
            window.NovastraCart.update(Number(item.id), Number(quantity.value));
            renderCart();
        });

        const remove = element('button', 'text-sm text-red-700', 'Hapus');
        remove.type = 'button';
        remove.addEventListener('click', () => {
            window.NovastraCart.remove(Number(item.id));
            renderCart();
        });

        controls.append(quantity, remove);
        details.append(controls);
        article.append(image, details, element('strong', '', money(item.price * item.quantity)));
        list.append(article);
    });

    const summary = element('aside', 'card h-fit p-6');
    summary.append(element('p', 'eyebrow', 'Ringkasan'));
    const subtotalRow = element('div', 'mt-4 flex justify-between text-lg font-semibold');
    subtotalRow.append(element('span', '', 'Subtotal'), element('span', '', money(subtotal)));
    summary.append(subtotalRow, element('p', 'mt-2 text-xs text-ink/55', 'Ongkir dihitung saat checkout.'));
    const checkoutLink = element('a', 'btn mt-6 w-full', 'Lanjut checkout');
    checkoutLink.href = '/checkout';
    summary.append(checkoutLink);

    layout.append(list, summary);
    root.append(layout);
}

function prepareCheckout() {
    const form = document.querySelector('[data-checkout-form]');
    const summary = document.querySelector('[data-checkout-summary]');
    if (!form || !summary) return;
    const items = readCart();
    if (!items.length) { window.location.href = '/cart'; return; }
    items.forEach((item, index) => {
        const productId = element('input');
        productId.type = 'hidden';
        productId.name = `items[${index}][product_id]`;
        productId.value = String(item.id);

        const quantity = element('input');
        quantity.type = 'hidden';
        quantity.name = `items[${index}][quantity]`;
        quantity.value = String(item.quantity);
        form.append(productId, quantity);
    });
    const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const shipping = subtotal >= 250000 ? 0 : 25000;
    summary.replaceChildren();
    items.forEach((item) => {
        const row = element('div', 'flex justify-between gap-4 text-sm');
        row.append(element('span', '', `${item.quantity} × ${String(item.name)}`), element('span', '', money(item.price * item.quantity)));
        summary.append(row);
    });

    const totalSection = element('div', 'mt-5 border-t pt-4');
    const totalRow = element('div', 'flex justify-between');
    totalRow.append(element('span', '', 'Total'), element('strong', '', money(subtotal + shipping)));
    totalSection.append(totalRow);
    summary.append(totalSection);
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[data-clear-cart]')) window.NovastraCart.clear();
    updateCount(); renderCart(); prepareCheckout();
    document.querySelectorAll('[data-currency-input]').forEach((input) => {
        formatCurrencyInput(input);
        input.addEventListener('input', () => formatCurrencyInput(input));
    });
    document.querySelectorAll('[data-file-input]').forEach((input) => input.addEventListener('change', () => {
        const filename = input.files?.[0]?.name || 'Pilih gambar produk';
        const label = input.closest('label')?.querySelector('[data-file-name]');
        if (label) label.textContent = filename;
    }));
    document.querySelectorAll('[data-add-cart]').forEach((button) => button.addEventListener('click', () => {
        window.NovastraCart.add(JSON.parse(button.dataset.product), Number(document.querySelector(button.dataset.quantityTarget)?.value || 1));
        button.textContent = 'Ditambahkan'; setTimeout(() => { button.textContent = 'Tambah ke keranjang'; }, 1200);
    }));
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    let visitor = localStorage.getItem('novastra-visitor');
    if (!visitor) { visitor = crypto.randomUUID(); localStorage.setItem('novastra-visitor', visitor); }
    fetch('/analytics/view', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ path: location.pathname, visitor_id: visitor }) }).catch(() => {});
});
window.addEventListener('novastra:cart', updateCount);
