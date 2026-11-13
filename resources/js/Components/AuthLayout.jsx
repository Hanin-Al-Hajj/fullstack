import { Link } from '@inertiajs/react';

// Full-screen purple page with a white card, like the template's login pages.
export default function AuthLayout({ title, subtitle, children }) {
    return (
        <div className="py-4 login-wrapper d-flex align-items-center justify-content-center">
            <div className="container" style={{ maxWidth: 440 }}>
                <div className="card">
                    <div className="p-4 card-body">
                        <div className="mb-3 text-center">
                            <Link href="/"><img src="/template/logo1.png" alt="" style={{ height: 56 }} /></Link>
                        </div>
                        <h1 className="mb-1 text-center h5">{title}</h1>
                        {subtitle && <p className="mb-3 text-center text-muted small">{subtitle}</p>}
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}

export function Field({ label, error, ...props }) {
    return (
        <div className="mb-3">
            <label className="mb-1 form-label small">{label}</label>
            <input className={`form-control ${error ? 'is-invalid' : ''}`} {...props} />
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}