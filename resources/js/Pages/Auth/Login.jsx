import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout, { Field } from '../../Components/AuthLayout';
import { __ } from '../../lib/i18n';

export default function Login() {
    const { status } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({ email: '', password: '', remember: true });

    const submit = (e) => {
        e.preventDefault();
        post('/login', { onFinish: () => reset('password') });
    };

    return (
        <AuthLayout title={__('Welcome back')} subtitle={__('Sign in to continue')}>
            {status && <div className="alert alert-success py-2 small">{status}</div>}
            <form onSubmit={submit}>
                <Field label={__('Email')} type="email" autoComplete="username" required value={data.email}
                       onChange={(e) => setData('email', e.target.value)} error={errors.email} />
                <Field label={__('Password')} type="password" autoComplete="current-password" required value={data.password}
                       onChange={(e) => setData('password', e.target.value)} error={errors.password} />
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <label className="form-check small mb-0">
                        <input type="checkbox" className="form-check-input" checked={data.remember}
                               onChange={(e) => setData('remember', e.target.checked)} /> {__('Remember me')}
                    </label>
                    <Link href="/forgot-password" className="small">{__('Forgot password?')}</Link>
                </div>
                <button className="btn btn-primary w-100" disabled={processing}>{__('Sign in')}</button>
            </form>
            <p className="text-center small mt-3 mb-0">{__('New here?')} <Link href="/register">{__('Create an account')}</Link></p>
        </AuthLayout>
    );
}
