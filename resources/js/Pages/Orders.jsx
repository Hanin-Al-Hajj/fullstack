import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Pagination from '../Components/Pagination';
import { useToast } from '../Components/Toast';
import { money } from '../lib/format';
import { post } from '../lib/api';
import { __, lang } from '../lib/i18n';

export const statusColor = (status) => ({
    pending: 'warning', confirmed: 'info', processing: 'info', shipped: 'primary', out_for_delivery: 'primary',
    delivered: 'success', delivery_failed: 'danger', returned: 'danger', cancelled: 'danger',
}[status] ?? 'secondary');

export default function Orders({ orders }) {
    const toast = useToast();
    const [busy, setBusy] = useState(null);

    const again = async (o) => {
        setBusy(o.order_number);
        try {
            const r = await post(`/orders/${o.order_number}/reorder`);
            if (r.added) {
                toast(r.skipped ? __('Some items were not added because they are unavailable.') : __('Items added to your cart.'));
                router.visit('/cart');
            } else {
                toast(__('None of the items could be added because they are unavailable.'), 'error');
            }
        } catch (e) {
            toast(e.message, 'error');
        }
        setBusy(null);
    };

    if (orders.data.length === 0) {
        return (
            <div className="container text-center py-5">
                <i className="ti ti-package fs-1 text-muted d-block mb-2"></i>
                <p className="text-muted">{__('No orders yet.')}</p>
                <Link href="/shop" className="btn btn-primary">{__('Start shopping')}</Link>
            </div>
        );
    }

    return (
        <div className="container pt-3">
            <h1 className="h5 mb-3">{__('My orders')}</h1>

            {orders.data.map((o) => (
                <div className="card mb-2" key={o.order_number}>
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{o.order_number}</strong>
                                <div className="small text-muted">
                                    {new Date(o.placed_at).toLocaleDateString(lang() === 'ar' ? 'ar-u-nu-latn' : 'en', { day: 'numeric', month: 'short', year: 'numeric' })}
                                    {' · '}{__('Items: :n', { n: o.items_count })}
                                </div>
                            </div>
                            <span className={`badge bg-${statusColor(o.status)}`}>{__(o.status_label)}</span>
                        </div>
                        <div className="d-flex justify-content-between align-items-center mt-2">
                            <strong>{money(o.total_minor, o.currency)}</strong>
                            <div className="d-flex gap-2">
                                <Link href={`/order/${o.order_number}`} className="btn btn-sm btn-outline-secondary">{__('View')}</Link>
                                <button className="btn btn-sm btn-primary" disabled={busy === o.order_number} onClick={() => again(o)}>
                                    {__('Order again')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            ))}

            <Pagination links={orders.links} />
        </div>
    );
}

Orders.layout = (page) => <AppLayout>{page}</AppLayout>;
