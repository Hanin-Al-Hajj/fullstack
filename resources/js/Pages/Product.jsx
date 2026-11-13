import { useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Slider from '../Components/Slider';
import { useToast } from '../Components/Toast';
import { money, t, productImage } from '../lib/format';
import { post } from '../lib/api';
import { __ } from '../lib/i18n';

export default function Product({ product }) {
    const toast = useToast();
    const [variantId, setVariantId] = useState(product.variants[0]?.id);
    const [qty, setQty] = useState(1);
    const [busy, setBusy] = useState(false);

    const v = product.variants.find((x) => x.id === variantId);
    const images = product.images.length ? product.images : [productImage(product)];
    const label = (x) => x.options.map(t).join(' / ') || x.sku;
    const max = v?.stock_qty > 0 ? v.stock_qty : 10;
    const discount = v?.compare_at_price_minor > v?.price_minor
        ? Math.round((1 - v.price_minor / v.compare_at_price_minor) * 100)
        : null;

    const pick = (id) => { setVariantId(id); setQty(1); };

    const add = async () => {
        setBusy(true);
        try {
            await post('/cart/items', { product_variant_id: v.id, qty });
            toast(__('Added to cart'));
            router.reload({ only: ['cartCount'] });
        } catch (e) {
            toast(e.message, 'error');
        }
        setBusy(false);
    };

    return (
        <>
            <Slider dots>
                {images.map((src) => (
                    <img key={src} src={src} alt="" className="w-100" style={{ aspectRatio: '1 / 1', objectFit: 'cover' }} />
                ))}
            </Slider>

            <div className="container pt-3">
                {product.brand && <p className="text-muted mb-1">{product.brand}</p>}
                <h1 className="h4">{t(product.name)}</h1>

                {v && (
                    <>
                        <p className="h5 mb-1">
                            {money(v.price_minor, v.currency)}
                            {discount && (
                                <>
                                    <span className="text-muted text-decoration-line-through ms-2 fs-6">
                                        {money(v.compare_at_price_minor, v.currency)}
                                    </span>
                                    <span className="badge bg-danger ms-2">-{discount}%</span>
                                </>
                            )}
                        </p>
                        <p className={`mb-3 ${v.in_stock ? 'text-success' : 'text-danger'}`}>
                            {v.in_stock ? (v.low_stock ? __('Only :n left', { n: v.stock_qty }) : __('In stock')) : __('Out of stock')}
                        </p>
                    </>
                )}

                {t(product.short_description) && <p>{t(product.short_description)}</p>}

                {product.variants.length > 1 && (
                    <div className="d-flex flex-wrap gap-2 mb-3">
                        {product.variants.map((x) => (
                            <button
                                key={x.id}
                                className={`btn btn-sm ${x.id === variantId ? 'btn-primary' : 'btn-outline-secondary'}`}
                                disabled={!x.in_stock}
                                onClick={() => pick(x.id)}
                            >
                                {label(x)}
                            </button>
                        ))}
                    </div>
                )}

                <div className="d-flex align-items-center gap-3 mb-4">
                    <div className="input-group" style={{ width: 130 }}>
                        <button className="btn btn-outline-secondary" onClick={() => setQty(Math.max(1, qty - 1))}>-</button>
                        <span className="form-control text-center">{qty}</span>
                        <button className="btn btn-outline-secondary" onClick={() => setQty(Math.min(max, qty + 1))}>+</button>
                    </div>
                    <button className="btn btn-primary flex-grow-1" disabled={busy || !v?.in_stock} onClick={add}>
                        <i className="ti ti-basket me-1"></i>{__('Add to cart')}
                    </button>
                </div>

                {t(product.description) && (
                    <div className="mb-4" dangerouslySetInnerHTML={{ __html: t(product.description) }} />
                )}
            </div>
        </>
    );
}

Product.layout = (page) => <AppLayout>{page}</AppLayout>;
