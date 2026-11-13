import { useForm } from '@inertiajs/react';
import AuthLayout, { Field } from '../../Components/AuthLayout';
import { __ } from '../../lib/i18n';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token, email: email ?? '', password: '', password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/reset-password', { onFinish: () => reset('password', 'password_confirmation') });
    };

    return (
        <AuthLayout title={__('Choose a new password')}>
            <form onSubmit={submit}>
                <Field label={__('Email')} type="email" required value={data.email}
                       onChange={(e) => setData('email', e.target.value)} error={errors.email} />
                <Field label={__('New password')} type="password" autoComplete="new-password" required value={data.password}
                       onChange={(e) => setData('password', e.target.value)} error={errors.password} />
                <Field label={__('Confirm new password')} type="password" autoComplete="new-password" required value={data.password_confirmation}
                       onChange={(e) => setData('password_confirmation', e.target.value)} error={errors.password_confirmation} />
                <button className="btn btn-primary w-100" disabled={processing}>{__('Reset password')}</button>
            </form>
        </AuthLayout>
    );
}
