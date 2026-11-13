import { createContext, useCallback, useContext, useState } from 'react';

const ToastContext = createContext(() => {});
export const useToast = () => useContext(ToastContext); // const toast = useToast(); toast('Added to cart')

export function ToastProvider({ children }) {
    const [items, setItems] = useState([]);

    const show = useCallback((message, type = 'success') => {
        const id = Date.now() + Math.random();
        setItems((list) => [...list, { id, message, type }]);
        setTimeout(() => setItems((list) => list.filter((x) => x.id !== id)), 3000);
    }, []);

    return (
        <ToastContext.Provider value={show}>
            {children}
            <div
                className="gap-2 d-flex flex-column align-items-center"
                style={{ position: 'fixed', top: 90, left: '50%', transform: 'translateX(-50%)', width: '100%', maxWidth: 480, zIndex: 2000, pointerEvents: 'none' }}
            >
                {items.map((x) => (
                    <div key={x.id} className={`toast show text-white border-0 bg-${x.type === 'error' ? 'danger' : 'success'}`} role="alert">
                        <div className="toast-body">{x.message}</div>
                    </div>
                ))}
            </div>
        </ToastContext.Provider>
    );
}