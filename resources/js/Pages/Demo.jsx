import AppLayout from '../Layouts/AppLayout';
import { ProductGrid, ProductRow, FeaturedCard, FlashSaleCard } from '../Components/ProductCards';
import Pagination from '../Components/Pagination';
import { useToast } from '../Components/Toast';

const sample = [1, 2, 3, 4].map((i) => ({
    id: i,
    slug: `demo-${i}`,
    name: { en: `Sample product ${i}`, ar: `منتج ${i}` },
    default_variant: { price_minor: 1300 * i, compare_at_price_minor: i % 2 ? 4200 : null },
    rating_avg: 4.5,
}));

const links = [
    { url: null, label: '&laquo; Previous', active: false },
    { url: '/demo/home?page=1', label: '1', active: true },
    { url: '/demo/home?page=2', label: '2', active: false },
    { url: '/demo/home?page=2', label: 'Next &raquo;', active: false },
];

export default function Demo({ page }) {
    const toast = useToast();

    return (
        <div className="container pt-3">
            <h1 className="h4 text-capitalize">{page} page</h1>

            <ProductGrid products={sample} badge={{ text: 'Sale' }} onAdd={() => toast('Added to cart')} />

            <h6 className="mt-4">List card</h6>
            <ProductRow product={sample[1]} />

            <h6 className="mt-4">Featured + flash sale</h6>
            <div className="row g-2">
                <div className="col-6"><FeaturedCard product={sample[2]} /></div>
                <div className="col-6"><FlashSaleCard product={sample[0]} soldPercent={40} /></div>
            </div>

            <Pagination links={links} />
            <button className="btn btn-danger btn-sm" onClick={() => toast('Something failed', 'error')}>Error toast</button>
        </div>
    );
}

Demo.layout = (page) => <AppLayout>{page}</AppLayout>;