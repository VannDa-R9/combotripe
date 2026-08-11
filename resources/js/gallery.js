import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

export function initGallery() {
  initSwiperSliders();
  initPhotoSwipeLightbox();
}

/**
 * Initialize Swiper Carousels for Top Destinations & About Scenery
 */
function initSwiperSliders() {
  // 1. Homepage Top Destinations Carousel
  const topDestEl = document.querySelector('.top-destinations-swiper');
  if (topDestEl && !topDestEl.dataset.swiperInit) {
    topDestEl.dataset.swiperInit = 'true';
    new Swiper(topDestEl, {
      modules: [Navigation, Pagination, Autoplay],
      slidesPerView: 1,
      spaceBetween: 20,
      loop: false,
      autoplay: {
        delay: 4500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      breakpoints: {
        640: { slidesPerView: 2, spaceBetween: 20 },
        1024: { slidesPerView: 3, spaceBetween: 24 },
        1280: { slidesPerView: 4, spaceBetween: 24 },
      },
    });
  }

  // 2. Place Details About Scenery Carousel
  const sceneryEl = document.querySelector('.about-scenery-swiper');
  if (sceneryEl && !sceneryEl.dataset.swiperInit) {
    sceneryEl.dataset.swiperInit = 'true';
    new Swiper(sceneryEl, {
      modules: [Navigation, Pagination],
      slidesPerView: 1,
      spaceBetween: 16,
      loop: false,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      breakpoints: {
        640: { slidesPerView: 2, spaceBetween: 16 },
        1024: { slidesPerView: 3, spaceBetween: 20 },
      },
    });
  }
}

/**
 * Initialize PhotoSwipe v5 Lightbox with Safe Image Dimensions
 */
function initPhotoSwipeLightbox() {
  // Collect all gallery containers or standalone lightbox image triggers
  const galleryContainers = document.querySelectorAll('[data-pswp-gallery]');
  if (galleryContainers.length === 0) return;

  galleryContainers.forEach(container => {
    if (container.dataset.pswpInit) return;
    container.dataset.pswpInit = 'true';

    const links = Array.from(container.querySelectorAll('a[data-pswp-src], img[data-pswp-src]'));
    if (links.length === 0) return;

    links.forEach((item, index) => {
      item.classList.add('pswp-lightbox-image');
      item.addEventListener('click', e => {
        e.preventDefault();
        openPhotoSwipe(links, index);
      });
    });
  });
}

/**
 * Safely open PhotoSwipe with exact dimensions & fallback strategy
 */
function openPhotoSwipe(items, startIndex) {
  const dataSource = [];

  let loadedCount = 0;
  const total = items.length;

  items.forEach(item => {
    const src = item.getAttribute('data-pswp-src') || item.getAttribute('src') || item.getAttribute('href');
    const alt = item.getAttribute('alt') || item.getAttribute('data-pswp-caption') || 'CamboTrips Image';

    // Target width/height resolution
    let width = parseInt(item.getAttribute('data-pswp-width') || 0, 10);
    let height = parseInt(item.getAttribute('data-pswp-height') || 0, 10);

    const imgObj = {
      src: src,
      msrc: src,
      w: width,
      h: height,
      alt: alt,
      element: item
    };

    dataSource.push(imgObj);
  });

  // Calculate dimensions safely for each item before opening
  const promises = dataSource.map(item => {
    return new Promise(resolve => {
      if (item.w > 0 && item.h > 0) {
        resolve();
        return;
      }

      // Check if item element is an <img> already loaded in DOM
      if (item.element && item.element.tagName === 'IMG' && item.element.complete && item.element.naturalWidth > 0) {
        item.w = item.element.naturalWidth;
        item.h = item.element.naturalHeight;
        resolve();
        return;
      }

      // Preload image object to get exact natural dimensions
      const preloader = new Image();
      preloader.src = item.src;

      if (preloader.complete && preloader.naturalWidth > 0) {
        item.w = preloader.naturalWidth;
        item.h = preloader.naturalHeight;
        resolve();
      } else {
        preloader.onload = () => {
          item.w = preloader.naturalWidth || 1200;
          item.h = preloader.naturalHeight || 800;
          resolve();
        };
        preloader.onerror = () => {
          // Fallback to 16:9 ratio if preloader fails, never distorting
          const rect = item.element ? item.element.getBoundingClientRect() : null;
          item.w = rect && rect.width > 0 ? Math.round(rect.width * 2) : 1200;
          item.h = rect && rect.height > 0 ? Math.round(rect.height * 2) : 800;
          resolve();
        };
      }
    });
  });

  Promise.all(promises).then(() => {
    const lightbox = new PhotoSwipeLightbox({
      dataSource: dataSource,
      index: startIndex,
      pswpModule: () => import('photoswipe'),
      bgOpacity: 0.9,
      showHideAnimationType: 'zoom',
    });

    lightbox.init();
    lightbox.loadAndOpen(startIndex);
  });
}
