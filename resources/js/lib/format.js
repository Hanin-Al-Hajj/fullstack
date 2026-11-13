import { lang } from './i18n';

// Translatable field ({en, ar}) -> string for the current page language.
export const t = (v) => {
    if (typeof v === 'string') return v;
    return v?.[lang()] ?? v?.en ?? '';
};

// Prices are stored as integer minor units (cents). Arabic pages keep
// Latin digits (3.00), the usual way prices are written in Lebanon.
export const money = (minor, currency = 'USD') =>
    new Intl.NumberFormat(lang() === 'ar' ? 'ar-u-nu-latn' : lang(), { style: 'currency', currency, currencyDisplay: 'narrowSymbol' })
        .format((minor ?? 0) / 100);

export const variantOf = (p) => p.default_variant ?? p.defaultVariant ?? p.variants?.[0] ?? {};

// Falls back to a template image until real product images are uploaded.
export const productImage = (p) =>
    p.image_url ?? p.media?.[0]?.original_url ?? `/template/img/product/${(p.id % 18) + 1}.png`;