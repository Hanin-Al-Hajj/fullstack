import { Link } from '@inertiajs/react';

// Pass the Laravel paginator's `links` array.
export default function Pagination({ links = [] }) {
    if (links.length <= 3) return null;
    return (
        <nav className="mt-3">
            <ul className="pagination justify-content-center">
                {links.map((l, i) => (
                    <li key={i} className={`page-item ${l.active ? 'active' : ''} ${l.url ? '' : 'disabled'}`}>
                        {l.url
                            ? <Link className="page-link" href={l.url} preserveScroll dangerouslySetInnerHTML={{ __html: l.label }} />
                            : <span className="page-link" dangerouslySetInnerHTML={{ __html: l.label }} />}
                    </li>
                ))}
            </ul>
        </nav>
    );
}