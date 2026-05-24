import EmblaCarousel from 'embla-carousel';
import Autoplay from 'embla-carousel-autoplay';

function resolveElement(selector) {
  if (typeof selector === 'string') {
    return document.querySelector(selector);
  }
  return selector;
}

function resolveTarget(target) {
  if (!target) {
    return null;
  }
  if (typeof target === 'string') {
    return document.querySelector(target);
  }
  return target;
}

function getActiveOptions(options) {
  const breakpoints = options.breakpoints;
  if (!breakpoints) {
    return { ...options };
  }

  const width = window.innerWidth;
  const merged = { ...options };
  const keys = Object.keys(breakpoints)
    .map((key) => Number(key))
    .sort((a, b) => a - b);

  keys.forEach((key) => {
    if (width >= key) {
      Object.assign(merged, breakpoints[key]);
    }
  });

  return merged;
}

function applySlidesPerView(root, slidesPerView) {
  if (!slidesPerView || slidesPerView === 'auto') {
    return;
  }

  const value = typeof slidesPerView === 'number' ? slidesPerView : parseFloat(slidesPerView);
  if (!Number.isFinite(value) || value <= 0) {
    return;
  }

  root.style.setProperty('--ez-slides-per-view', String(value));
  root.querySelectorAll('.swiper-slide').forEach((slide) => {
    slide.style.flexBasis = `calc(100% / ${value})`;
  });
}

export function Swiper(selector, options = {}) {
  const root = resolveElement(selector);
  if (!root) {
    return {
      on() {},
      destroy() {},
      slides: [],
      activeIndex: 0,
    };
  }

  const activeOptions = getActiveOptions(options);
  const viewport = root.querySelector('.swiper-wrapper') ? root : root;
  const emblaOptions = {
    loop: Boolean(activeOptions.loop),
    align: activeOptions.centeredSlides ? 'center' : 'start',
    slidesToScroll: activeOptions.slidesPerGroup || 1,
    containScroll: 'trimSnaps',
  };

  const plugins = [];
  if (activeOptions.autoplay) {
    plugins.push(
      Autoplay({
        delay: typeof activeOptions.autoplay === 'object' ? activeOptions.autoplay.delay || 3000 : 3000,
      })
    );
  }

  applySlidesPerView(root, activeOptions.slidesPerView);
  const embla = EmblaCarousel(viewport, emblaOptions, plugins);

  const api = {
    slides: embla.slideNodes(),
    activeIndex: embla.selectedScrollSnap(),
    on(event, handler) {
      if (event === 'slideChange') {
        embla.on('select', () => {
          api.activeIndex = embla.selectedScrollSnap();
          handler.call(api);
        });
      }
    },
    destroy() {
      embla.destroy();
    },
  };

  const nextButton = resolveTarget(activeOptions.navigation?.nextEl);
  const prevButton = resolveTarget(activeOptions.navigation?.prevEl);
  nextButton?.addEventListener('click', () => embla.scrollNext());
  prevButton?.addEventListener('click', () => embla.scrollPrev());

  const paginationRoot = resolveTarget(activeOptions.pagination?.el);
  if (paginationRoot) {
    const renderPagination = () => {
      paginationRoot.innerHTML = '';
      embla.scrollSnapList().forEach((_, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'swiper-pagination-bullet';
        dot.addEventListener('click', () => embla.scrollTo(index));
        paginationRoot.appendChild(dot);
      });
    };
    renderPagination();
    embla.on('reInit', renderPagination);
  }

  const runInit = () => {
    api.slides = embla.slideNodes();
    api.activeIndex = embla.selectedScrollSnap();
    activeOptions.on?.init?.call(api);
  };

  embla.on('init', runInit);
  embla.on('select', () => {
    api.activeIndex = embla.selectedScrollSnap();
    activeOptions.on?.slideChange?.call(api);
  });

  runInit();

  window.addEventListener('resize', () => {
    const nextOptions = getActiveOptions(options);
    applySlidesPerView(root, nextOptions.slidesPerView);
    embla.reInit(emblaOptions, plugins);
  });

  return api;
}

window.Swiper = Swiper;

export default Swiper;
