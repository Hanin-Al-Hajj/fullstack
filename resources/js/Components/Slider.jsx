import { Children } from 'react';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Autoplay, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

// Replaces Owl Carousel. Each child becomes one slide.
export default function Slider({ children, perView = 1, gap = 0, autoplay = false, dots = false, loop = false }) {
    return (
        <Swiper
            modules={[Autoplay, Pagination]}
            slidesPerView={perView}
            spaceBetween={gap}
            loop={loop}
            autoplay={autoplay ? { delay: 3500, disableOnInteraction: false } : false}
            pagination={dots ? { clickable: true } : false}
            style={{ '--swiper-theme-color': '#625AFA', '--swiper-pagination-bottom': '8px' }}
        >
            {Children.map(children, (child, i) => (
                <SwiperSlide key={i}>{child}</SwiperSlide>
            ))}
        </Swiper>
    );
}