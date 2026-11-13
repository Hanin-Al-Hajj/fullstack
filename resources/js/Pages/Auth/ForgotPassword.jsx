import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout, { Field } from '../../Components/AuthLayout';
import { __ } from '../../lib/i18n';

export default function ForgotPassword() {
    const { status } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit = (e) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <AuthLayout title={__('Forgot your password?')} subtitle={__('Enter your email and we will send you a reset link')}>
            {status && <div className="alert alert-success py-2 small">{status}</div>}
            <form onSubmit={submit}>
                <Field label={__('Email')} type="email" required value={data.email}
                       onChange={(e) => setData('email', e.target.value)} error={errors.email} />
                <button className="btn btn-primary w-100" disabled={processing}>{__('Send reset link')}</button>
            </form>
            <p className="text-center small mt-3 mb-0"><Link href="/login">{__('Back to sign in')}</Link></p>
        </AuthLayout>
    );
}
