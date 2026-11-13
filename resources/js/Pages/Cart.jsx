import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { useToast } from '../Components/Toast';
import { money, t, productImage } from '../lib/format';
import { patch, del } from '../lib/api';
import { __ } from '../lib/i18n';

export default function Cart({ items, subtotal_minor }) {
    const toast = useToast();
    const [busy, setBusy] = useState(false);

    const run = async (fn) => {
        setBusy(true);
        try {
            await fn();
            router.reload({ only: ['items', 'subtotal_minor', 'cartCount'] });
        } catch (e) {
            toast(e.message, 'error');
        }
        setBusy(false);
    };

    const setQty = (it, qty) => run(() => patch(`/cart/items/${it.id}`, { qty }));
    const remove = (it) => run(() => del(`/cart/items/${it.id}`));
    const hasProblem = items.some((i) => i.problem);

    if (items.length === 0) {
        return (
            <div className="container text-center py-5">
                <i className="ti ti-basket fs-1 text-muted d-block mb-2"></i>
                <p className="text-muted">{__('Your cart is empty.')}</p>
                <Link href="/shop" className="btn btn-primary">{__('Start shopping')}</Link>
            </div>
        );
    }

    return (
        <div className="container pt-3">
            <h1 className="h5 mb-3">{__('Your cart')}</h1>

            {items.map((it) => (
                <div className="card mb-2" key={it.id}>
                    <div className="card-body d-flex gap-3">
                        <Link href={`/products/${it.product.slug}`}>
                            <img src={productImage(it.product)} alt="" style={{ width: 72, height: 72, objectFit: 'cover', borderRadius: 8 }} />
                        </Link>

                        <div className="flex-grow-1">
                            <Link href={`/products/${it.product.slug}`} className="fw-bold d-block">{t(it.product.name)}</Link>
                            <small className="text-muted d-block">{it.options.map(t).join(' / ') || it.sku}</small>
                            <span>{money(it.unit_price_minor, it.currency)}</span>
                            {it.problem && <small className="text-danger d-block">{__('Only :n available. Please reduce the quantity.', { n: it.max_qty })}</small>}

                            <div className="d-flex align-items-center justify-content-between mt-2">
                                <div className="input-group input-group-sm" style={{ width: 110 }}>
                                    <button className="btn btn-outline-secondary" disabled={busy || it.qty <= 1} onClick={() => setQty(it, it.qty - 1)}>-</button>
                                    <span className="form-control text-center">{it.qty}</span>
                                    <button className="btn btn-outline-secondary" disabled={busy || it.qty >= it.max_qty} onClick={() => setQty(it, it.qty + 1)}>+</button>
                                </div>
                                <strong>{money(it.unit_price_minor * it.qty, it.currency)}</strong>
                                <button className="btn btn-sm btn-link text-danger p-0" disabled={busy} onClick={() => remove(it)}>
                                    <i className="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            ))}

            <div className="d-flex justify-content-between my-3 h6">
                <span>{__('Subtotal')}</span>
                <span>{money(subtotal_minor, items[0].currency)}</span>
            </div>
            <p className="text-muted small">{__('Shipping and discounts are calculated at checkout.')}</p>

            {hasProblem ? (
                <button className="btn btn-primary w-100" disabled>{__('Proceed to checkout')}</button>
            ) : (
                <Link href="/checkout" className="btn btn-primary w-100">{__('Proceed to checkout')}</Link>
            )}
        </div>
    );
}

Cart.layout = (page) => <AppLayout>{page}</AppLayout>;
