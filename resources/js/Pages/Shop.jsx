import { useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { ProductGrid, ProductRow } from '../Components/ProductCards';
import Pagination from '../Components/Pagination';
import { __ } from '../lib/i18n';

const EMPTY = { search: '', brand_id: '', min_price: '', max_price: '', in_stock_only: false, sort: 'newest' };
const sorts = [
    ['newest', 'Newest'],
    ['price_asc', 'Price: low to high'],
    ['price_desc', 'Price: high to low'],
    ['name', 'Name'],
];

export default function Shop({ products, brands, filters }) {
    const [f, setF] = useState({ ...EMPTY, ...filters });
    const [open, setOpen] = useState(false);
    const [view, setView] = useState('grid');

    const set = (k) => (e) => setF({ ...f, [k]: e.target.type === 'checkbox' ? e.target.checked : e.target.value });

    const apply = (next = f) => {
        const params = Object.fromEntries(Object.entries(next).filter(([, v]) => v !== '' && v !== false && v != null));
        router.get('/shop', params, { preserveState: true });
        setOpen(false);
    };

    const reset = () => {
        setF(EMPTY);
        apply(EMPTY);
    };

    return (
        <div className="container pt-3">
            <form className="d-flex gap-2 mb-3" onSubmit={(e) => { e.preventDefault(); apply(); }}>
                <input className="form-control" placeholder={__('Search products')} value={f.search} onChange={set('search')} />
                <button className="btn btn-primary" type="submit"><i className="ti ti-search"></i></button>
            </form>

            <div className="d-flex align-items-center justify-content-between mb-2">
                <span>{__(':n products', { n: products.total })}</span>
                <div>
                    <button className={`btn btn-sm me-1 ${view === 'grid' ? 'btn-primary' : 'btn-outline-secondary'}`} onClick={() => setView('grid')}>
                        <i className="ti ti-layout-grid"></i>
                    </button>
                    <button className={`btn btn-sm me-1 ${view === 'list' ? 'btn-primary' : 'btn-outline-secondary'}`} onClick={() => setView('list')}>
                        <i className="ti ti-list"></i>
                    </button>
                    <button className="btn btn-sm btn-outline-secondary" onClick={() => setOpen(true)}>
                        <i className="ti ti-adjustments-horizontal"></i>
                    </button>
                </div>
            </div>

            {products.data.length === 0 ? (
                <p className="text-center text-muted py-5">{__('No products match these filters.')}</p>
            ) : view === 'grid' ? (
                <ProductGrid products={products.data} />
            ) : (
                <div className="row g-2">
                    {products.data.map((p) => (
                        <div className="col-12" key={p.id}><ProductRow product={p} /></div>
                    ))}
                </div>
            )}

            <Pagination links={products.links} />

            {/* Filter sheet */}
            {open && <div className="offcanvas-backdrop fade show" onClick={() => setOpen(false)}></div>}
            {open && (
                <div
                    className="bg-white p-3"
                    style={{ position: 'fixed', bottom: 0, left: '50%', transform: 'translateX(-50%)', width: '100%', maxWidth: 480, zIndex: 1060, borderRadius: '16px 16px 0 0', maxHeight: '85vh', overflowY: 'auto' }}
                >
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <h6 className="mb-0">{__('Filters')}</h6>
                        <button className="btn-close" onClick={() => setOpen(false)}></button>
                    </div>

                    <label className="form-label">{__('Sort by')}</label>
                    <select className="form-select mb-3" value={f.sort} onChange={set('sort')}>
                        {sorts.map(([v, label]) => <option key={v} value={v}>{__(label)}</option>)}
                    </select>

                    <label className="form-label">{__('Brand')}</label>
                    <select className="form-select mb-3" value={f.brand_id} onChange={set('brand_id')}>
                        <option value="">{__('All brands')}</option>
                        {brands.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
                    </select>

                    <label className="form-label">{__('Price')}</label>
                    <div className="row g-2 mb-3">
                        <div className="col-6"><input type="number" className="form-control" placeholder={__('Min')} value={f.min_price} onChange={set('min_price')} /></div>
                        <div className="col-6"><input type="number" className="form-control" placeholder={__('Max')} value={f.max_price} onChange={set('max_price')} /></div>
                    </div>

                    <div className="form-check mb-3">
                        <input className="form-check-input" type="checkbox" id="inStock" checked={f.in_stock_only} onChange={set('in_stock_only')} />
                        <label className="form-check-label" htmlFor="inStock">{__('In stock only')}</label>
                    </div>

                    <button className="btn btn-success w-100 mb-2" onClick={() => apply()}>{__('Apply filters')}</button>
                    <button className="btn btn-outline-secondary w-100" onClick={reset}>{__('Reset')}</button>
                </div>
            )}
        </div>
    );
}

Shop.layout = (page) => <AppLayout>{page}</AppLayout>;
