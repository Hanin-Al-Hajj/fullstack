import { router } from '@inertiajs/react';
import { useToast } from '../Components/Toast';
import { post } from '../lib/api';
import { __ } from '../lib/i18n';

// const addToCart = useAddToCart(); addToCart(variantId, qty)
export default function useAddToCart() {
    const toast = useToast();
    return async (variantId, qty = 1) => {
        try {
            await post('/cart/items', { product_variant_id: variantId, qty });
            toast(__('Added to cart'));
            router.reload({ only: ['cartCount'] }); // refresh the header badge
        } catch (e) {
            toast(e.message, 'error');
        }
    };
}
