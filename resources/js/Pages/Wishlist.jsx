import { Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { ProductRow } from '../Components/ProductCards';
import { __ } from '../lib/i18n';

export default function Wishlist({ items }) {
    if (items.length === 0) {
        return (
            <div className="container text-center py-5">
                <i className="ti ti-heart fs-1 text-muted d-block mb-2"></i>
                <p className="text-muted">{__('Your wishlist is empty. Tap the heart on a product to save it.')}</p>
                <Link href="/shop" className="btn btn-primary">{__('Browse products')}</Link>
            </div>
        );
    }

    return (
        <div className="container pt-3">
            <h1 className="h5 mb-3">{__('My wishlist')}</h1>
            <div className="row g-2">
                {items.map((p) => (
                    <div className="col-12" key={p.default_variant.id}><ProductRow product={p} /></div>
                ))}
            </div>
        </div>
    );
}

Wishlist.layout = (page) => <AppLayout>{page}</AppLayout>;
