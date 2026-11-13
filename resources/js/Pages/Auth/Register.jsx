import { Link, useForm } from '@inertiajs/react';
import AuthLayout, { Field } from '../../Components/AuthLayout';
import { __ } from '../../lib/i18n';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '', email: '', password: '', password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/register', { onFinish: () => reset('password', 'password_confirmation') });
    };

    return (
        <AuthLayout title={__('Create your account')} subtitle={__('It only takes a minute')}>
            <form onSubmit={submit}>
                <Field label={__('Full name')} autoComplete="name" required value={data.name}
                       onChange={(e) => setData('name', e.target.value)} error={errors.name} />
                <Field label={__('Email')} type="email" autoComplete="username" required value={data.email}
                       onChange={(e) => setData('email', e.target.value)} error={errors.email} />
                <Field label={__('Password')} type="password" autoComplete="new-password" required value={data.password}
                       onChange={(e) => setData('password', e.target.value)} error={errors.password} />
                <Field label={__('Confirm password')} type="password" autoComplete="new-password" required value={data.password_confirmation}
                       onChange={(e) => setData('password_confirmation', e.target.value)} error={errors.password_confirmation} />
                <button className="btn btn-primary w-100" disabled={processing}>{__('Create account')}</button>
            </form>
            <p className="text-center small mt-3 mb-0">{__('Already have an account?')} <Link href="/login">{__('Sign in')}</Link></p>
        </AuthLayout>
    );
}
