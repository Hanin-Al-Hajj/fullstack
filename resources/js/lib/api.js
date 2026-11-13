// Small JSON helper for endpoints that return JSON (cart, wishlist...).
const xsrf = () => decodeURIComponent((document.cookie.match(/XSRF-TOKEN=([^;]+)/) || [])[1] || '');

export async function send(method, url, body) {
    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': xsrf() },
        body: body ? JSON.stringify(body) : undefined,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const err = new Error(data.message || 'Something went wrong');
        err.status = res.status;
        throw err;
    }
    return data;
}

export const post = (url, body) => send('POST', url, body);
export const patch = (url, body) => send('PATCH', url, body);
export const del = (url) => send('DELETE', url);