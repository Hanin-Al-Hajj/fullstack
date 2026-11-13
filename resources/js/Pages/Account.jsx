import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { Field } from '../Components/AuthLayout';
import { useToast } from '../Components/Toast';
import { __ } from '../lib/i18n';

export default function Account({ profile }) {
    const toast = useToast();

    const info = useForm({ name: profile.name ?? '', email: profile.email ?? '', phone: profile.phone ?? '' });
    const pass = useForm({ current_password: '', password: '', password_confirmation: '' });

    const saveInfo = (e) => {
        e.preventDefault();
        info.patch('/account', {
            preserveScroll: true,
            onSuccess: (page) => toast(page.props.status ?? __('Saved')),
        });
    };

    const savePass = (e) => {
        e.preventDefault();
        pass.put('/account/password', {
            preserveScroll: true,
            onSuccess: (page) => {
                pass.reset();
                toast(page.props.status ?? __('Saved'));
            },
        });
    };

    return (
        <div className="container pt-3">
            <div className="text-center mb-4">
                <img src="/icons/avatar.png" alt="" className="rounded-circle mb-2"
                     style={{ width: 80, height: 80, objectFit: 'cover' }} />
                <h1 className="h5 mb-0">{profile.name}</h1>
                <small className="text-muted">{profile.email}</small>
            </div>

            <div className="card mb-3">
                <div className="card-body">
                    <h6 className="mb-3">{__('Personal details')}</h6>
                    <form onSubmit={saveInfo}>
                        <Field label={__('Full name')} required value={info.data.name}
                               onChange={(e) => info.setData('name', e.target.value)} error={info.errors.name} />
                        <Field label={__('Email')} type="email" required value={info.data.email}
                               onChange={(e) => info.setData('email', e.target.value)} error={info.errors.email} />
                        <Field label={__('WhatsApp number')} type="tel" placeholder="+961 70 123 456" value={info.data.phone}
                               onChange={(e) => info.setData('phone', e.target.value)} error={info.errors.phone} />
                        <button className="btn btn-primary w-100" disabled={info.processing}>{__('Save changes')}</button>
                    </form>
                </div>
            </div>

            <div className="card mb-3">
                <div className="card-body">
                    <h6 className="mb-3">{__('Change password')}</h6>
                    <form onSubmit={savePass}>
                        <Field label={__('Current password')} type="password" autoComplete="current-password" required
                               value={pass.data.current_password}
                               onChange={(e) => pass.setData('current_password', e.target.value)} error={pass.errors.current_password} />
                        <Field label={__('New password')} type="password" autoComplete="new-password" required
                               value={pass.data.password}
                               onChange={(e) => pass.setData('password', e.target.value)} error={pass.errors.password} />
                        <Field label={__('Confirm new password')} type="password" autoComplete="new-password" required
                               value={pass.data.password_confirmation}
                               onChange={(e) => pass.setData('password_confirmation', e.target.value)} error={pass.errors.password_confirmation} />
                        <button className="btn btn-primary w-100" disabled={pass.processing}>{__('Update password')}</button>
                    </form>
                </div>
            </div>

            <div className="list-group mb-3">
                <Link href="/orders" className="list-group-item list-group-item-action"><i className="ti ti-package me-2"></i>{__('My orders')}</Link>
                <Link href="/addresses" className="list-group-item list-group-item-action"><i className="ti ti-map-pin me-2"></i>{__('My addresses')}</Link>
                <Link href="/wishlist" className="list-group-item list-group-item-action"><i className="ti ti-heart me-2"></i>{__('My wishlist')}</Link>
            </div>

            <Link href="/logout" method="post" as="button" className="btn btn-outline-danger w-100 mb-3">
                <i className="ti ti-logout me-1"></i>{__('Sign out')}
            </Link>
        </div>
    );
}

Account.layout = (page) => <AppLayout>{page}</AppLayout>;
