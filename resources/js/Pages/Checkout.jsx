import { useEffect, useMemo, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { useToast } from '../Components/Toast';
import { money, t } from '../lib/format';
import { post } from '../lib/api';
import { __ } from '../lib/i18n';

export default function Checkout({ items, subtotal_minor, zones, customer, addresses = [] }) {
    const toast = useToast();
    const isGuest = !usePage().props.auth?.user;
    const currency = items[0]?.currency ?? 'USD';

    const [email, setEmail] = useState(customer?.email ?? '');
    const fromSaved = (a) => ({
        full_name: a.full_name, phone: a.phone, governorate: a.governorate, city: a.city,
        area: a.area ?? '', street: a.street, building: a.building ?? '', floor: a.floor ?? '', landmark: a.landmark ?? '',
    });
    const defaultSaved = addresses.find((a) => a.is_default) ?? addresses[0];
    const [addr, setAddr] = useState(() => defaultSaved ? fromSaved(defaultSaved) : {
        full_name: customer?.name ?? '', phone: customer?.phone ?? '', governorate: '',
        city: '', area: '', street: '', building: '', floor: '', landmark: '',
    });
    const [rateId, setRateId] = useState(null);
    const [coupon, setCoupon] = useState('');
    const [applied, setApplied] = useState('');
    const [notes, setNotes] = useState('');
    const [totals, setTotals] = useState({ subtotal_minor, shipping_minor: 0, discount_minor: 0, total_minor: subtotal_minor });
    const [busy, setBusy] = useState(false);

    const governorates = useMemo(() => [...new Set(zones.flatMap((z) => z.governorates))], [zones]);
    const rates = useMemo(
        () => zones.filter((z) => z.governorates.includes(addr.governorate)).flatMap((z) => z.rates),
        [zones, addr.governorate],
    );

    const set = (k) => (e) => setAddr({ ...addr, [k]: e.target.value });

    // Pick a valid shipping method whenever the governorate changes.
    useEffect(() => {
        if (!rates.some((r) => r.id === rateId)) setRateId(rates[0]?.id ?? null);
    }, [rates]);

    // Recalculate shipping + coupon whenever either changes.
    useEffect(() => {
        post('/checkout/quote', { shipping_rate_id: rateId, coupon_code: applied || null })
            .then(setTotals)
            .catch((e) => {
                toast(e.message, 'error');
                if (applied) setApplied('');
            });
    }, [rateId, applied]);

    const place = async (e) => {
        e.preventDefault();
        setBusy(true);
        try {
            const res = await post('/checkout', {
                shipping_address: addr,
                shipping_rate_id: rateId,
                coupon_code: applied || null,
                payment_method: 'cod',
                guest_email: isGuest ? email : null,
                notes: notes || null,
            });
            router.visit(`/order/${res.order.order_number}`);
        } catch (err) {
            toast(err.message, 'error');
        }
        setBusy(false);
    };

    const field = (label, key, props = {}) => (
        <div className="mb-2">
            <label className="form-label small mb-1">{label}</label>
            <input className="form-control" value={addr[key]} onChange={set(key)} {...props} />
        </div>
    );

    return (
        <form className="container pt-3" onSubmit={place}>
            <h1 className="h5 mb-3">{__('Checkout')}</h1>

            <h6>{__('Contact and address')}</h6>
            {addresses.length > 0 && (
                <select
                    className="form-select mb-3"
                    defaultValue={defaultSaved.id}
                    onChange={(e) => {
                        const a = addresses.find((x) => x.id === Number(e.target.value));
                        if (a) setAddr(fromSaved(a));
                    }}
                >
                    {addresses.map((a) => <option key={a.id} value={a.id}>{a.label || a.street} - {a.city}</option>)}
                </select>
            )}
            {isGuest && (
                <div className="mb-2">
                    <label className="form-label small mb-1">{__('Email')}</label>
                    <input type="email" className="form-control" required value={email} onChange={(e) => setEmail(e.target.value)} />
                </div>
            )}
            {field(__('Full name'), 'full_name', { required: true })}
            {field(__('WhatsApp number'), 'phone', { required: true, type: 'tel', placeholder: '+961 70 123 456' })}
            <small className="text-muted d-block mb-2">{__('Our team will confirm your order with you on WhatsApp using this number.')}</small>
            <div className="mb-2">
                <label className="form-label small mb-1">{__('Governorate')}</label>
                <select className="form-select" required value={addr.governorate} onChange={set('governorate')}>
                    <option value="">{__('Choose...')}</option>
                    {governorates.map((g) => <option key={g} value={g}>{g}</option>)}
                </select>
            </div>
            {field(__('City'), 'city', { required: true })}
            {field(__('Area'), 'area')}
            {field(__('Street'), 'street', { required: true })}
            <div className="row g-2">
                <div className="col-6">{field(__('Building'), 'building')}</div>
                <div className="col-6">{field(__('Floor'), 'floor')}</div>
            </div>
            {field(__('Landmark'), 'landmark')}

            <h6 className="mt-3">{__('Delivery fee')}</h6>
            <p className="mb-2">
                {rates.length === 0
                    ? <span className="text-muted small">{__('Choose a governorate to see the delivery fee.')}</span>
                    : <strong>{money(rates[0].rate_minor, currency)}</strong>}
            </p>

            <h6 className="mt-3">{__('Coupon')}</h6>
            <div className="input-group mb-1">
                <input className="form-control" placeholder={__('Coupon code')} value={coupon} onChange={(e) => setCoupon(e.target.value)} />
                <button type="button" className="btn btn-outline-primary" disabled={!coupon.trim()} onClick={() => setApplied(coupon.trim())}>{__('Apply')}</button>
            </div>
            {applied && <small className="text-success">{__('Coupon ":code" applied.', { code: applied })}</small>}

            <h6 className="mt-3">{__('Payment')}</h6>
            <label className="card card-body flex-row mb-2">
                <input type="radio" className="form-check-input me-2" checked readOnly />
                {__('Cash on delivery (you pay when the order arrives)')}
            </label>

            <label className="form-label small mb-1 mt-2">{__('Order notes (optional)')}</label>
            <textarea className="form-control mb-3" rows="2" value={notes} onChange={(e) => setNotes(e.target.value)}></textarea>

            <h6>{__('Order summary')}</h6>
            {items.map((it) => (
                <div key={it.id} className="d-flex justify-content-between small mb-1">
                    <span>{t(it.product.name)} {it.options.length > 0 && `(${it.options.map(t).join(' / ')})`} x {it.qty}</span>
                    <span>{money(it.unit_price_minor * it.qty, currency)}</span>
                </div>
            ))}
            <hr />
            <div className="d-flex justify-content-between"><span>{__('Subtotal')}</span><span>{money(totals.subtotal_minor, currency)}</span></div>
            <div className="d-flex justify-content-between"><span>{__('Delivery fee')}</span><span>{money(totals.shipping_minor, currency)}</span></div>
            {totals.discount_minor > 0 && (
                <div className="d-flex justify-content-between text-success"><span>{__('Discount')}</span><span>-{money(totals.discount_minor, currency)}</span></div>
            )}
            <div className="d-flex justify-content-between h6 mt-2"><span>{__('Total')}</span><span>{money(totals.total_minor, currency)}</span></div>

            <button className="btn btn-primary w-100 mt-3" type="submit" disabled={busy || !rateId}>
                {busy ? __('Placing order...') : __('Place order')}
            </button>
        </form>
    );
}

Checkout.layout = (page) => <AppLayout>{page}</AppLayout>;
