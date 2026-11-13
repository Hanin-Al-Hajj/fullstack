import { Head, usePage } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import { lang } from '../lib/i18n';

// Every text is [English, Arabic]. {name} becomes the store name.
const pages = {
    about: {
        title: ['About us', 'من نحن'],
        blocks: [
            { p: ['{name} is an online store delivering all over Lebanon. We bring you a wide range of products at fair prices, with simple ordering and cash on delivery.', '{name} متجر إلكتروني يوصّل إلى كل لبنان. نقدّم لك مجموعة واسعة من المنتجات بأسعار عادلة، مع طلب سهل ودفع نقداً عند الاستلام.'] },
            { h: ['How we work', 'كيف نعمل'], p: ['You place your order on the website, and our team confirms it with you on WhatsApp before it is prepared and delivered. You pay in cash when it arrives.', 'تقدّم طلبك عبر الموقع، ثم يؤكده معك فريقنا عبر واتساب قبل تجهيزه وتوصيله. تدفع نقداً عند الاستلام.'] },
            { h: ['Our promise', 'وعدنا'], p: ['Honest prices, careful packing and friendly service. If anything is not right, talk to us and we will make it right.', 'أسعار عادلة، وتغليف دقيق، وخدمة ودودة. إذا كان هناك أي مشكلة، تواصل معنا وسنحلّها.'] },
        ],
    },
    contact: {
        title: ['Contact us', 'اتصل بنا'],
        contact: true,
    },
    support: {
        title: ['Support', 'الدعم'],
        faqs: [
            { q: ['How long does delivery take?', 'كم يستغرق التوصيل؟'], a: ['The delivery fee is shown at checkout before you confirm. Our team will tell you the expected delivery time when they confirm your order on WhatsApp.', 'تظهر رسوم التوصيل عند إتمام الطلب قبل التأكيد. يخبرك فريقنا بموعد التوصيل المتوقع عند تأكيد طلبك عبر واتساب.'] },
            { q: ['How do I pay?', 'كيف أدفع؟'], a: ['We accept cash on delivery only. You pay the driver when your order arrives.', 'نقبل الدفع نقداً عند الاستلام فقط. تدفع للسائق عند وصول طلبك.'] },
            { q: ['How is my order confirmed?', 'كيف يتم تأكيد طلبي؟'], a: ['After you place an order, our team contacts you on the WhatsApp number you entered at checkout. Please make sure it is correct.', 'بعد تقديم الطلب، يتواصل معك فريقنا على رقم واتساب الذي أدخلته عند إتمام الطلب. يرجى التأكد من صحته.'] },
            { q: ['Can I change or cancel my order?', 'هل يمكنني تعديل طلبي أو إلغاؤه؟'], a: ['Yes, as long as it has not been delivered. Message us on WhatsApp as soon as possible.', 'نعم، طالما لم يتم تسليمه. راسلنا عبر واتساب في أسرع وقت.'] },
            { q: ['What if there is a problem with an item?', 'ماذا لو كانت هناك مشكلة في أحد المنتجات؟'], a: ['Tell us on WhatsApp and we will help you.', 'أخبرنا عبر واتساب وسنساعدك.'] },
        ],
    },
    'privacy-policy': {
        title: ['Privacy policy', 'سياسة الخصوصية'],
        blocks: [
            { h: ['What we collect', 'ما الذي نجمعه'], p: ['When you order or create an account we collect your name, email, WhatsApp or phone number and delivery address. We never ask for card details, because we only accept cash on delivery.', 'عند الطلب أو إنشاء حساب نجمع اسمك وبريدك الإلكتروني ورقم واتساب أو الهاتف وعنوان التوصيل. لا نطلب أي بيانات بطاقة لأننا نقبل الدفع نقداً عند الاستلام فقط.'] },
            { h: ['How we use it', 'كيف نستخدمها'], p: ['We use your details only to confirm and deliver your orders, to contact you about them, and to improve the store. We do not sell your information.', 'نستخدم بياناتك فقط لتأكيد طلباتك وتوصيلها والتواصل معك بشأنها وتحسين المتجر. نحن لا نبيع معلوماتك.'] },
            { h: ['Sharing', 'المشاركة'], p: ['We share your name, phone number and address with the delivery team only so they can deliver your order.', 'نشارك اسمك ورقم هاتفك وعنوانك مع فريق التوصيل فقط ليتمكن من إيصال طلبك.'] },
            { h: ['Cookies', 'ملفات تعريف الارتباط'], p: ['The site uses cookies to keep your cart, your language and your login. They are needed for the store to work.', 'يستخدم الموقع ملفات تعريف الارتباط للحفاظ على سلتك ولغتك وتسجيل دخولك. وهي ضرورية لعمل المتجر.'] },
            { h: ['Your choices', 'خياراتك'], p: ['You can edit your details from your account page. To delete your data, contact us and we will help.', 'يمكنك تعديل بياناتك من صفحة حسابك. لحذف بياناتك تواصل معنا وسنساعدك.'] },
        ],
    },
};

export default function Info({ page }) {
    const { store: s } = usePage().props;
    const ar = lang() === 'ar';
    const pick = (pair) => (pair ? pair[ar ? 1 : 0].replaceAll('{name}', s.name) : '');
    const data = pages[page];

    const contacts = [
        { icon: 'ti-brand-whatsapp', label: ['Chat with us on WhatsApp', 'تحدث معنا عبر واتساب'], href: `https://wa.me/${s.whatsapp}` },
        { icon: 'ti-phone', label: [s.phone, s.phone], href: `tel:${s.phone.replaceAll(' ', '')}`, ltr: true },
        { icon: 'ti-mail', label: [s.email, s.email], href: `mailto:${s.email}`, ltr: true },
        { icon: 'ti-map-pin', label: [s.address, s.address] },
        { icon: 'ti-clock', label: [s.hours, s.hours] },
    ];

    return (
        <div className="container pt-3 pb-3">
            <Head title={pick(data.title)} />
            <h1 className="mb-3 h5">{pick(data.title)}</h1>

            {data.blocks?.map((b, i) => (
                <div key={i} className="mb-3">
                    {b.h && <h6>{pick(b.h)}</h6>}
                    <p className="mb-0 text-muted">{pick(b.p)}</p>
                </div>
            ))}

            {data.contact && (
                <div className="list-group">
                    {contacts.map((c, i) => {
                        const inner = (
                            <>
                                <i className={`ti ${c.icon} fs-5 me-3`}></i>
                                {c.ltr ? <bdi>{pick(c.label)}</bdi> : pick(c.label)}
                            </>
                        );
                        return c.href ? (
                            <a key={i} href={c.href} target={c.href.startsWith('http') ? '_blank' : undefined} rel="noreferrer"
                               className="list-group-item list-group-item-action d-flex align-items-center">{inner}</a>
                        ) : (
                            <div key={i} className="list-group-item d-flex align-items-center">{inner}</div>
                        );
                    })}
                </div>
            )}

            {data.faqs?.map((f, i) => (
                <details key={i} className="mb-2 card">
                    <summary className="card-body fw-bold" style={{ cursor: 'pointer' }}>{pick(f.q)}</summary>
                    <div className="px-3 pb-3 text-muted">{pick(f.a)}</div>
                </details>
            ))}
        </div>
    );
}

Info.layout = (page) => <AppLayout>{page}</AppLayout>;