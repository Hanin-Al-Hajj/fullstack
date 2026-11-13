import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { useToast } from '../Components/Toast';
import { money, t } from '../lib/format';
import { post } from '../lib/api';
import { __ } from '../lib/i18n';

const color = (s) => ({
    pending: 'warning', confirmed: 'info', processing: 'info', shipped: 'primary', out_for_delivery: 'primary',
    delivered: 'success', delivery_failed: 'danger', returned: 'danger', cancelled: 'danger',
}[s] ?? 'secondary');

export default function OrderConfirmation({ order }) {
    const toast = useToast();
    const [busy, setBusy] = useState(false);
    const a = order.shipping_address;
    const c = order.currency;
    const line1 = [a.street, a.building && __('Building :n', { n: a.building }), a.floor && __('Floor :n', { n: a.floor })].filter(Boolean).join(', ');
    const line2 = [a.area, a.city, a.governorate].filter(Boolean).join(', ');

    const again = async () => {
        setBusy(true);
        try {
            const r = await post(`/orders/${order.order_number}/reorder`);
            if (r.added) {
                toast(r.skipped ? __('Some items were not added because they are unavailable.') : __('Items added to your cart.'));
                router.visit('/cart');
            } else {
                toast(__('None of the items could be added because they are unavailable.'), 'error');
            }
        } catch (e) {
            toast(e.message, 'error');
        }
        setBusy(false);
    };

    return (
        <div className="container pt-4">
            {order.is_new ? (
                <div className="text-center mb-4">
                    <i className="ti ti-circle-check fs-1 text-success"></i>
                    <h1 className="h4 mt-2">{__('Thank you, your order is placed')}</h1>
                    <p className="mb-1">{__('Order number')} <strong>{order.order_number}</strong></p>
                    <p className="text-muted">{__('Our team will contact you on WhatsApp (:phone) to confirm your order.', { phone: a.phone })}</p>
                </div>
            ) : (
                <div className="text-center mb-4">
                    <h1 className="h5 mb-2">{__('Order :number', { number: order.order_number })}</h1>
                    <span className={`badge bg-${color(order.status)}`}>{__(order.status_label)}</span>
                </div>
            )}

            <div className="card mb-3">
                <div className="card-body">
                    <h6>{__('Items')}</h6>
                    {order.items.map((i, idx) => (
                        <div key={idx} className="d-flex justify-content-between small mb-1">
                            <span>{t(i.product_name)}{i.variant_label && ` (${i.variant_label})`} x {i.qty}</span>
                            <span>{money(i.line_total_minor, c)}</span>
                        </div>
                    ))}
                    <hr />
                    <div className="d-flex justify-content-between"><span>{__('Subtotal')}</span><span>{money(order.subtotal_minor, c)}</span></div>
                    <div className="d-flex justify-content-between"><span>{__('Delivery fee')}</span><span>{money(order.shipping_minor, c)}</span></div>
                    {order.discount_minor > 0 && (
                        <div className="d-flex justify-content-between text-success"><span>{__('Discount')}</span><span>-{money(order.discount_minor, c)}</span></div>
                    )}
                    <div className="d-flex justify-content-between h6 mt-2 mb-0"><span>{__('Total')}</span><span>{money(order.total_minor, c)}</span></div>
                </div>
            </div>

            <div className="card mb-3">
                <div className="card-body">
                    <h6>{__('Delivery address')}</h6>
                    <p className="mb-0">{a.full_name}</p>
                    <p className="mb-0">{line1}</p>
                    <p className="mb-0">{line2}</p>
                    {a.landmark && <p className="mb-0 text-muted">{__('Near :place', { place: a.landmark })}</p>}

                    <h6 className="mt-3">{__('Payment')}</h6>
                    <p className="mb-0">{__('Cash on delivery: pay :amount when the order arrives.', { amount: money(order.total_minor, c) })}</p>
                </div>
            </div>

            {order.can_reorder && !order.is_new && (
                <button className="btn btn-primary w-100 mb-2" disabled={busy} onClick={again}>{__('Order again')}</button>
            )}
            <Link href="/shop" className={`btn w-100 mb-2 ${order.is_new ? 'btn-primary' : 'btn-outline-secondary'}`}>{__('Continue shopping')}</Link>
            <Link href="/" className="btn btn-outline-secondary w-100">{__('Back to home')}</Link>
        </div>
    );
}

OrderConfirmation.layout = (page) => <AppLayout>{page}</AppLayout>;
