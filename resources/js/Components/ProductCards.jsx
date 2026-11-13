import { Link } from '@inertiajs/react';
import Rating from './Rating';
import useAddToCart from '../hooks/useAddToCart';
import useWishlist from '../hooks/useWishlist';
import { money, t, variantOf, productImage } from '../lib/format';

const href = (p) => `/products/${p.slug}`;
const stop = (fn, p) => (e) => { e.preventDefault(); fn?.(p); };

function Price({ v, cls = 'sale-price', oldCls }) {
    const old = v.compare_at_price_minor > v.price_minor ? v.compare_at_price_minor : null;
    return (
        <p className={cls}>
            {money(v.price_minor, v.currency)}
            {old && <span className={oldCls}>{money(old, v.currency)}</span>}
        </p>
    );
}

function WishlistButton({ product, onWishlist }) {
    const wishlist = useWishlist();
    const id = variantOf(product).id;
    const saved = wishlist.has(id);
    return (
        <a className="wishlist-btn" href="#" onClick={stop(onWishlist ?? (() => wishlist.toggle(id)), product)}>
            <i className={`ti ${saved ? 'ti-heart-filled text-danger' : 'ti-heart'}`}></i>
        </a>
    );
}

// Grid card (shop-grid)
export function ProductCard({ product, badge, onAdd, onWishlist }) {
    const v = variantOf(product);
    const addToCart = useAddToCart();
    return (
        <div className="card product-card">
            <div className="card-body">
                {badge && <span className={`badge rounded-pill badge-${badge.color ?? 'warning'}`}>{badge.text}</span>}
                <WishlistButton product={product} onWishlist={onWishlist} />
                <Link className="product-thumbnail d-block" href={href(product)}>
                    <img className="mb-2" src={productImage(product)} alt="" />
                </Link>
                <Link className="product-title" href={href(product)}>{t(product.name)}</Link>
                <Price v={v} />
                <Rating value={product.rating_avg} />
                <a className="btn btn-primary btn-sm" href="#" onClick={stop(onAdd ?? ((p) => addToCart(variantOf(p).id)), product)}><i className="ti ti-plus"></i></a>
            </div>
        </div>
    );
}

// Horizontal card (shop-list, wishlist)
export function ProductRow({ product, onWishlist }) {
    const v = variantOf(product);
    return (
        <div className="card horizontal-product-card">
            <div className="d-flex align-items-center">
                <div className="product-thumbnail-side">
                    <Link className="product-thumbnail d-block" href={href(product)}>
                        <img src={productImage(product)} alt="" />
                    </Link>
                    <WishlistButton product={product} onWishlist={onWishlist} />
                </div>
                <div className="product-description">
                    <Link className="product-title d-block" href={href(product)}>{t(product.name)}</Link>
                    <Price v={v} />
                    <Rating value={product.rating_avg} />
                </div>
            </div>
        </div>
    );
}

// Small featured card (3 per row)
export function FeaturedCard({ product }) {
    const v = variantOf(product);
    return (
        <div className="card featured-product-card">
            <div className="card-body">
                <span className="badge badge-warning custom-badge"><i className="ti ti-star-filled"></i></span>
                <div className="product-thumbnail-side">
                    <Link className="product-thumbnail d-block" href={href(product)}>
                        <img src={productImage(product)} alt="" />
                    </Link>
                </div>
                <div className="product-description">
                    <Link className="product-title d-block" href={href(product)}>{t(product.name)}</Link>
                    <Price v={v} />
                </div>
            </div>
        </div>
    );
}

// Flash sale card; soldPercent is optional (hidden when not provided)
export function FlashSaleCard({ product, soldPercent }) {
    const v = variantOf(product);
    return (
        <div className="card flash-sale-card">
            <div className="card-body">
                <Link href={href(product)}>
                    <img src={productImage(product)} alt="" />
                    <span className="product-title">{t(product.name)}</span>
                    <Price v={v} cls="sale-price" oldCls="real-price" />
                    {soldPercent != null && (
                        <>
                            <span className="progress-title">{soldPercent}% Sold</span>
                            <div className="progress">
                                <div className="progress-bar" role="progressbar" style={{ width: `${soldPercent}%` }}></div>
                            </div>
                        </>
                    )}
                </Link>
            </div>
        </div>
    );
}

// Responsive grid wrapper: <ProductGrid products={...} />
export function ProductGrid({ products, ...cardProps }) {
    return (
        <div className="row g-2 rtl-flex-d-row-r">
            {products.map((p) => (
                <div className="col-6 col-md-4" key={p.id}>
                    <ProductCard product={p} {...cardProps} />
                </div>
            ))}
        </div>
    );
}