import { Link } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Slider from '../Components/Slider';
import { ProductGrid, FeaturedCard, FlashSaleCard } from '../Components/ProductCards';
import useCountdown from '../hooks/useCountdown';
import { t } from '../lib/format';
import { __ } from '../lib/i18n';

const pad = (n) => String(n).padStart(2, '0');

function Heading({ title, href, children }) {
    return (
        <div className="d-flex align-items-center justify-content-between mb-2">
            <h6 className="mb-0">{title}</h6>
            {children}
            {href && <Link href={href} className="small">{__('View all')}</Link>}
        </div>
    );
}

function Banner({ b }) {
    const inner = (
        <div
            className="d-flex align-items-end p-3 text-white"
            style={{
                height: 160,
                borderRadius: 12,
                background: b.image_url ? `center / cover url(${b.image_url})` : 'linear-gradient(135deg, #625AFA, #8f88ff)',
            }}
        >
            <h5 className="mb-0" style={{ textShadow: '0 1px 4px rgba(0,0,0,.4)' }}>{t(b.title)}</h5>
        </div>
    );
    if (!b.link_url) return inner;
    return b.link_url.startsWith('/') ? <Link href={b.link_url}>{inner}</Link> : <a href={b.link_url}>{inner}</a>;
}

function FlashSale({ flash }) {
    const c = useCountdown(flash.ends_at);
    if (c.expired || flash.products.length === 0) return null;
    return (
        <section className="mb-4">
            <Heading title={t(flash.name)} href="/shop">
                <span className="badge bg-danger">
                    {c.days > 0 && `${c.days}d `}{pad(c.hours)}:{pad(c.minutes)}:{pad(c.seconds)}
                </span>
            </Heading>
            <Slider perView={2.3} gap={8}>
                {flash.products.map((p) => <FlashSaleCard key={p.id} product={p} />)}
            </Slider>
        </section>
    );
}

export default function Home({ banners, categories, flash, featured, newArrivals }) {
    return (
        <div className="container pt-3">
            {banners.length > 0 && (
                <div className="mb-4">
                    <Slider autoplay dots loop={banners.length > 1}>
                        {banners.map((b) => <Banner key={b.id} b={b} />)}
                    </Slider>
                </div>
            )}

            {categories.length > 0 && (
                <div className="d-flex gap-2 overflow-auto pb-2 mb-3">
                    {categories.map((c) => (
                        <Link key={c.slug} href={`/categories/${c.slug}`} className="btn btn-sm btn-outline-primary text-nowrap">
                            {t(c.name)}
                        </Link>
                    ))}
                </div>
            )}

            {flash && <FlashSale flash={flash} />}

            {featured.length > 0 && (
                <section className="mb-4">
                    <Heading title={__('Featured products')} href="/shop" />
                    <Slider perView={2.2} gap={8}>
                        {featured.map((p) => <FeaturedCard key={p.id} product={p} />)}
                    </Slider>
                </section>
            )}

            <section className="mb-4">
                <Heading title={__('New arrivals')} href="/shop" />
                <ProductGrid products={newArrivals} />
            </section>
        </div>
    );
}

Home.layout = (page) => <AppLayout>{page}</AppLayout>;
