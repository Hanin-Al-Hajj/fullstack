import { Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { t } from '../lib/format';
import { __ } from '../lib/i18n';

export default function Categories({ category, parent, items }) {
    return (
        <div className="container pt-3">
            {category && (
                <Link href={parent ? `/categories/${parent.slug}` : '/categories'} className="d-inline-block mb-2">
                    <i className="ti ti-arrow-left me-1"></i>
                    {parent ? t(parent.name) : __('All categories')}
                </Link>
            )}

            <div className="d-flex justify-content-between align-items-center mb-3">
                <h1 className="h5 mb-0">{category ? t(category.name) : __('Categories')}</h1>
                {category && (
                    <Link href={`/shop?category=${category.slug}`} className="btn btn-sm btn-primary">
                        {__('View all products')}
                    </Link>
                )}
            </div>

            <div className="row g-2">
                {items.map((c) => (
                    <div className="col-6" key={c.slug}>
                        <Link
                            href={c.has_children ? `/categories/${c.slug}` : `/shop?category=${c.slug}`}
                            className="card text-center h-100"
                        >
                            <div className="card-body">
                                {c.image_url ? (
                                    <img src={c.image_url} alt="" className="mb-2" style={{ height: 64, objectFit: 'contain' }} />
                                ) : (
                                    <i className="ti ti-category fs-1 d-block mb-2 text-primary"></i>
                                )}
                                <span className="fw-bold">{t(c.name)}</span>
                                {c.has_children && (
                                    <small className="d-block text-muted">
                                        {__('Subcategories')} <i className="ti ti-chevron-right"></i>
                                    </small>
                                )}
                            </div>
                        </Link>
                    </div>
                ))}
            </div>

            {items.length === 0 && <p className="text-muted text-center py-5">{__('No categories yet.')}</p>}
        </div>
    );
}

Categories.layout = (page) => <AppLayout>{page}</AppLayout>;
