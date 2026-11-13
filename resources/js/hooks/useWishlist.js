import { router, usePage } from '@inertiajs/react';
import { useToast } from '../Components/Toast';
import { post } from '../lib/api';
import { __ } from '../lib/i18n';

// const wishlist = useWishlist(); wishlist.has(variantId); wishlist.toggle(variantId)
export default function useWishlist() {
    const toast = useToast();
    const ids = usePage().props.wishlist ?? [];

    return {
        has: (variantId) => ids.includes(variantId),
        toggle: async (variantId) => {
            if (!variantId) return;
            try {
                const res = await post('/wishlist', { product_variant_id: variantId });
                toast(res.saved ? __('Added to wishlist') : __('Removed from wishlist'));
                router.reload({ only: ['wishlist', 'items'] });
            } catch (e) {
                if (e.status === 401) router.visit('/login'); // guests must sign in first
                else toast(e.message, 'error');
            }
        },
    };
}
