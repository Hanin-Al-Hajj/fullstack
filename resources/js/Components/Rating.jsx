export default function Rating({ value, count }) {
    if (value == null) return null;
    return (
        <div className="product-rating">
            {[1, 2, 3, 4, 5].map((n) => (
                <i key={n} className={`ti ${n <= Math.round(value) ? 'ti-star-filled' : 'ti-star'}`}></i>
            ))}
            {count != null && <span className="ms-1">({count})</span>}
        </div>
    );
}