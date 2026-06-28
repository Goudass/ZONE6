(() => {
  const mapEl = document.getElementById('contact-map');

  if (!mapEl || typeof window.L === 'undefined') {
    return;
  }

  const lat = Number.parseFloat(mapEl.dataset.lat || '50.2649');
  const lng = Number.parseFloat(mapEl.dataset.lng || '19.0238');
  const label = mapEl.dataset.label || 'Katowice';

  const map = window.L.map(mapEl, {
    scrollWheelZoom: false,
    zoomControl: true,
  }).setView([lat, lng], 12);

  window.L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19,
  }).addTo(map);

  const markerIcon = window.L.divIcon({
    className: 'contact-map__marker-wrap',
    html: '<span class="contact-map__marker" aria-hidden="true"></span>',
    iconSize: [28, 28],
    iconAnchor: [14, 28],
    popupAnchor: [0, -24],
  });

  window.L.marker([lat, lng], { icon: markerIcon }).addTo(map).bindPopup(label);

  const refreshMap = () => {
    map.invalidateSize();
  };

  window.setTimeout(refreshMap, 120);
  window.addEventListener('resize', refreshMap);

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            refreshMap();
          }
        });
      },
      { threshold: 0.2 }
    );

    observer.observe(mapEl);
  }
})();
