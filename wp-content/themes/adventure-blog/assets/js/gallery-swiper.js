(() => {
  if (!window.Swiper) {
    return;
  }

  document.querySelectorAll('.route-gallery__swiper').forEach((galleryEl) => {
    if (galleryEl.swiper) {
      return;
    }

    new Swiper(galleryEl, {
      loop: true,
      slidesPerView: 1,
      spaceBetween: 16,
      pagination: {
        el: galleryEl.querySelector('.swiper-pagination'),
        clickable: true,
      },
      navigation: {
        nextEl: galleryEl.querySelector('.swiper-button-next'),
        prevEl: galleryEl.querySelector('.swiper-button-prev'),
      },
    });
  });
})();
