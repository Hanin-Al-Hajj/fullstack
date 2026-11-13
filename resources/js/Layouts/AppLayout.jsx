import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { ToastProvider } from '../Components/Toast';
import { __, lang } from '../lib/i18n';

const T = '/template';

const footerItems = [
    { href: '/', icon: 'ti-home', label: 'Home' },
    { href: '/shop', icon: 'ti-building-store', label: 'Shop' },
    { href: '/cart', icon: 'ti-basket', label: 'Cart' },
    { href: '/wishlist', icon: 'ti-heart', label: 'Wishlist' },
    { href: '/account', icon: 'ti-user', label: 'Account' },
];

// On desktop the template stays a centered phone-width column.
const shellCss = `
/* The template flips flex rows itself in RTL; with dir=rtl that would flip them twice. */
[dir=rtl] .rtl-flex-d-row-r { flex-direction: row !important; }
[dir=rtl] .header-area, [dir=rtl] .footer-nav-area { right: auto !important; }
@media (min-width: 576px) {
  body { background: #e9e9f2; }
  #appShell { max-width: 480px; margin: 0 auto; min-height: 100vh; background: #fff; }
  .header-area, .footer-nav-area { max-width: 480px; left: 50% !important; transform: translateX(-50%); }
  .suha-offcanvas-wrap { left: calc(50% - 240px) !important; }
  [dir=rtl] .suha-offcanvas-wrap { left: auto !important; right: calc(50% - 240px) !important; }
}`;

function Preloader() {
    const [loading, setLoading] = useState(true);
    useEffect(() => {
        const done = () => setLoading(false);
        if (document.readyState === 'complete') {
            done();
            return;
        }
        window.addEventListener('load', done);
        return () => window.removeEventListener('load', done);
    }, []);

    if (!loading) return null;
    return (
        <div className="preloader" id="preloader">
            <div className="spinner-grow text-secondary" role="status"></div>
        </div>
    );
}

function DropdownItem({ icon, label, children }) {
    const [open, setOpen] = useState(false);
    return (
        <li className="suha-dropdown-menu">
            <a href="#" onClick={(e) => { e.preventDefault(); setOpen(!open); }}>
                <i className={`ti ${icon}`}></i>{label}
            </a>
            <ul style={{ display: open ? 'block' : 'none' }}>{children}</ul>
            <div className={`dropdown-trigger-btn ${open ? 'active' : ''}`} onClick={() => setOpen(!open)}>
                <i className="ti ti-chevron-down"></i>
            </div>
        </li>
    );
}

function Shell({ children }) {
    const { url, props } = usePage();
    const [menuOpen, setMenuOpen] = useState(false);
    const close = () => setMenuOpen(false);

    const user = props.auth?.user ?? null;
    const cartCount = props.cartCount ?? 0;
    const current = lang();

    return (
        <div id="appShell">
            <style>{shellCss}</style>
            <Preloader />

            {/* Header */}
            <div className="header-area" id="headerArea" style={{ height: '80px' }}>
                <div className="container h-100 d-flex align-items-center justify-content-between rtl-flex-d-row-r">
                    <div className="logo-wrapper">
                        <Link href="/"><img src={`${T}/logo1.png`} style={{ height: '57px' }} alt="" /></Link>
                    </div>
                    <div className="navbar-logo-container d-flex align-items-center">
                        <div className="cart-icon-wrap">
                            <Link href="/cart">
                                <i className="ti ti-basket-bolt"></i>
                                {cartCount > 0 && <span>{cartCount}</span>}
                            </Link>
                        </div>
                        <div className="user-profile-icon ms-2">
                            <Link href="/account"><img src={'/icons/avatar.png'} alt="" /></Link>
                        </div>
                        <div className="suha-navbar-toggler ms-2" onClick={() => setMenuOpen(true)}>
                            <div><span></span><span></span><span></span></div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Side menu */}
            {menuOpen && <div className="offcanvas-backdrop fade show" onClick={close}></div>}
            <div
                className={`offcanvas offcanvas-start suha-offcanvas-wrap ${menuOpen ? 'show' : ''}`}
                style={{ visibility: menuOpen ? 'visible' : 'hidden' }}
                tabIndex={-1}
            >
                <button className="btn-close btn-close-white" type="button" aria-label="Close" onClick={close}></button>
                <div className="offcanvas-body">
                    <div className="sidenav-profile">
                        <div className="user-profile"><img src={'/icons/avatar.png'} alt="" /></div>
                        <div className="user-info">
                            <h5 className="mb-1 text-white user-name">{user ? user.name : __('Guest')}</h5>
                        </div>
                    </div>
                    <ul className="sidenav-nav ps-0">
                        <li><Link href="/account" onClick={close}><i className="ti ti-user"></i>{__('My Profile')}</Link></li>
                        {user && <li><Link href="/orders" onClick={close}><i className="ti ti-package"></i>{__('My orders')}</Link></li>}
                        <DropdownItem icon="ti-building-store" label={__('Shop')}>
                            <li><Link href="/shop" onClick={close}>- {__('All Products')}</Link></li>
                            <li><Link href="/categories" onClick={close}>- {__('Categories')}</Link></li>
                        </DropdownItem>
                        <li><Link href="/wishlist" onClick={close}><i className="ti ti-heart"></i>{__('My Wishlist')}</Link></li>
                        <DropdownItem icon="ti-help" label={__('Help')}>
                            <li><Link href="/about" onClick={close}>- {__('About Us')}</Link></li>
                            <li><Link href="/support" onClick={close}>- {__('Support')}</Link></li>
                            <li><Link href="/contact" onClick={close}>- {__('Contact Us')}</Link></li>
                            <li><Link href="/privacy-policy" onClick={close}>- {__('Privacy Policy')}</Link></li>
                        </DropdownItem>
                        <li>
                            {/* Plain links on purpose: the language change needs a full page load. */}
                            <a href="/locale/en" style={{ fontWeight: current === 'en' ? 700 : 400 }}>
                                <i className="ti ti-language"></i>English
                            </a>
                        </li>
                        <li>
                            <a href="/locale/ar" style={{ fontWeight: current === 'ar' ? 700 : 400 }}>
                                <i className="ti ti-language"></i>العربية
                            </a>
                        </li>
                        <li>
                            {user ? (
                                <Link href="/logout" method="post" as="button" onClick={close} className="p-0 btn btn-link">
                                    <i className="ti ti-logout"></i>{__('Sign Out')}
                                </Link>
                            ) : (
                                <Link href="/login" onClick={close}><i className="ti ti-login"></i>{__('Sign In')}</Link>
                            )}
                        </li>
                    </ul>
                </div>
            </div>

            {/* Page content */}
            <div className="page-content-wrapper" style={{ marginTop: '5rem', marginBottom: '5rem' }}>
                {children}
            </div>

            {/* Footer nav */}
            <div className="footer-nav-area" id="footerNav">
                <div className="suha-footer-nav">
                    <ul className="h-100 d-flex align-items-center justify-content-between ps-0 rtl-flex-d-row-r">
                        {footerItems.map((item) => (
                            <li key={item.href}>
                                <Link
                                    href={item.href}
                                    style={(item.href === '/' ? url === '/' : url.startsWith(item.href)) ? { color: '#625AFA' } : undefined}
                                >
                                    <i className={`ti ${item.icon}`}></i>{__(item.label)}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </div>
    );
}

export default function AppLayout({ children }) {
    return <ToastProvider><Shell>{children}</Shell></ToastProvider>;
}
