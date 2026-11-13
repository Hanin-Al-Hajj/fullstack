import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Field } from '../Components/AuthLayout';
import { useToast } from '../Components/Toast';
import { __ } from '../lib/i18n';

const EMPTY = {
    label: '', full_name: '', phone: '', governorate: '', city: '', area: '',
    street: '', building: '', floor: '', landmark: '', is_default: false,
};

const pick = (a) => Object.fromEntries(Object.keys(EMPTY).map((k) => [k, a[k] ?? EMPTY[k]]));

function AddressForm({ address, governorates, onDone }) {
    const toast = useToast();
    const { data, setData, post, put, processing, errors } = useForm(address ? pick(address) : EMPTY);
    const bind = (k) => ({ value: data[k], onChange: (e) => setData(k, e.target.value), error: errors[k] });

    const submit = (e) => {
        e.preventDefault();
        const opts = {
            preserveScroll: true,
            onSuccess: (page) => { toast(page.props.status ?? __('Saved')); onDone(); },
        };
        address ? put(`/addresses/${address.id}`, opts) : post('/addresses', opts);
    };

    return (
        <form onSubmit={submit} className="card card-body mb-3">
            <h6>{address ? __('Edit address') : __('New address')}</h6>
            <Field label={__('Label (e.g. Home, Work)')} {...bind('label')} />
            <Field label={__('Full name')} required {...bind('full_name')} />
            <Field label={__('WhatsApp / phone')} type="tel" required {...bind('phone')} />
            <div className="mb-3">
                <label className="form-label small mb-1">{__('Governorate')}</label>
                <select className={`form-select ${errors.governorate ? 'is-invalid' : ''}`} required
                        value={data.governorate} onChange={(e) => setData('governorate', e.target.value)}>
                    <option value="">{__('Choose...')}</option>
                    {governorates.map((g) => <option key={g} value={g}>{g}</option>)}
                </select>
                {errors.governorate && <div className="invalid-feedback">{errors.governorate}</div>}
            </div>
            <Field label={__('City')} required {...bind('city')} />
            <Field label={__('Area')} {...bind('area')} />
            <Field label={__('Street')} required {...bind('street')} />
            <div className="row">
                <div className="col-6"><Field label={__('Building')} {...bind('building')} /></div>
                <div className="col-6"><Field label={__('Floor')} {...bind('floor')} /></div>
            </div>
            <Field label={__('Landmark')} {...bind('landmark')} />
            <label className="form-check mb-3">
                <input type="checkbox" className="form-check-input" checked={data.is_default}
                       onChange={(e) => setData('is_default', e.target.checked)} /> {__('Use as my default address')}
            </label>
            <div className="d-flex gap-2">
                <button className="btn btn-primary flex-grow-1" disabled={processing}>{__('Save')}</button>
                <button type="button" className="btn btn-outline-secondary" onClick={onDone}>{__('Cancel')}</button>
            </div>
        </form>
    );
}

export default function Addresses({ addresses, governorates }) {
    const toast = useToast();
    const [editing, setEditing] = useState(null); // null | 'new' | address object

    const done = () => setEditing(null);
    const remove = (a) => {
        if (!confirm(__('Delete this address?'))) return;
        router.delete(`/addresses/${a.id}`, { preserveScroll: true, onSuccess: (p) => toast(p.props.status ?? __('Deleted')) });
    };
    const makeDefault = (a) =>
        router.put(`/addresses/${a.id}`, { ...pick(a), is_default: true }, { preserveScroll: true, onSuccess: (p) => toast(p.props.status ?? __('Saved')) });

    return (
        <div className="container pt-3">
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h1 className="h5 mb-0">{__('My addresses')}</h1>
                {editing === null && <button className="btn btn-sm btn-primary" onClick={() => setEditing('new')}>{__('Add address')}</button>}
            </div>

            {editing !== null && (
                <AddressForm key={editing === 'new' ? 'new' : editing.id}
                             address={editing === 'new' ? null : editing} governorates={governorates} onDone={done} />
            )}

            {addresses.length === 0 && editing === null && (
                <p className="text-muted text-center py-4">{__('No saved addresses yet.')}</p>
            )}

            {addresses.map((a) => (
                <div className="card mb-2" key={a.id}>
                    <div className="card-body">
                        <div className="d-flex justify-content-between">
                            <strong>{a.label || a.full_name}</strong>
                            {a.is_default && <span className="badge bg-primary">{__('Default')}</span>}
                        </div>
                        <div className="small">{a.full_name} · {a.phone}</div>
                        <div className="small text-muted">
                            {[a.street, a.building && __('Building :n', { n: a.building }), a.floor && __('Floor :n', { n: a.floor })].filter(Boolean).join(', ')}
                            <br />{[a.area, a.city, a.governorate].filter(Boolean).join(', ')}
                            {a.landmark && <><br />{__('Near :place', { place: a.landmark })}</>}
                        </div>
                        <div className="mt-2 d-flex gap-3 small">
                            <a href="#" onClick={(e) => { e.preventDefault(); setEditing(a); }}>{__('Edit')}</a>
                            {!a.is_default && <a href="#" onClick={(e) => { e.preventDefault(); makeDefault(a); }}>{__('Make default')}</a>}
                            <a href="#" className="text-danger" onClick={(e) => { e.preventDefault(); remove(a); }}>{__('Delete')}</a>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}

Addresses.layout = (page) => <AppLayout>{page}</AppLayout>;
