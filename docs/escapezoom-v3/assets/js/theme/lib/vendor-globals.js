import htmx from 'htmx.org';
import { gsap } from 'gsap';
import EmblaCarousel from 'embla-carousel';
import Autoplay from 'embla-carousel-autoplay';
import ClassNames from 'embla-carousel-class-names';
import Fade from 'embla-carousel-fade';
import AutoScroll from 'embla-carousel-auto-scroll';

import './swal-shim.js';
import './persian-date-shim.js';
import './swiper-compat.js';

window.htmx = htmx;
window.gsap = gsap;
window.EmblaCarousel = EmblaCarousel;
window.EmblaCarouselAutoplay = Autoplay;
window.EmblaCarouselClassNames = ClassNames;
window.EmblaCarouselFade = Fade;
window.EmblaCarouselAutoScroll = AutoScroll;
