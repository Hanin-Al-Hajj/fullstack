export default function Test({ message }) {
    return (
        <div className="container py-4">
            <h1 className="h4">{message}</h1>
            <p>
                <i className="ti ti-home fs-3 me-2"></i>
                If this icon shows, Tabler icons work.
            </p>
            <button className="btn btn-primary">Template button</button>
        </div>
    );
}