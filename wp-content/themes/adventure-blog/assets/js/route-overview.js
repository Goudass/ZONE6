(() => {
  const root = document.querySelector('.route-overview');
  if (!root) {
    return;
  }

  const gpxUrl = root.dataset.gpxUrl;
  const svg = root.querySelector('.route-overview__svg');
  const line = root.querySelector('.route-overview__line');
  const startDot = root.querySelector('.route-overview__dot--start');
  const endDot = root.querySelector('.route-overview__dot--end');

  const parseGpx = (xmlText) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(xmlText, 'application/xml');
    const pointNodes = [
      ...doc.querySelectorAll('trkpt'),
      ...doc.querySelectorAll('rtept'),
    ];

    const points = [];

    pointNodes.forEach((node) => {
      const lat = parseFloat(node.getAttribute('lat'));
      const lon = parseFloat(node.getAttribute('lon'));

      if (!Number.isNaN(lat) && !Number.isNaN(lon)) {
        points.push({ lat, lon });
      }
    });

    return points;
  };

  const projectPoints = (points, padding = 10) => {
    const lats = points.map((point) => point.lat);
    const lons = points.map((point) => point.lon);
    const minLat = Math.min(...lats);
    const maxLat = Math.max(...lats);
    const minLon = Math.min(...lons);
    const maxLon = Math.max(...lons);
    const latSpan = maxLat - minLat || 0.001;
    const lonSpan = maxLon - minLon || 0.001;
    const width = 100 - padding * 2;
    const height = 100 - padding * 2;

    return points.map((point) => ({
      x: padding + ((point.lon - minLon) / lonSpan) * width,
      y: padding + (1 - (point.lat - minLat) / latSpan) * height,
    }));
  };

  const renderRoute = (points) => {
    if (!line || points.length < 2) {
      return;
    }

    const projected = projectPoints(points);
    const polyline = projected.map((point) => `${point.x},${point.y}`).join(' ');

    line.setAttribute('points', polyline);

    if (startDot) {
      startDot.setAttribute('cx', projected[0].x);
      startDot.setAttribute('cy', projected[0].y);
    }

    if (endDot) {
      const last = projected[projected.length - 1];
      endDot.setAttribute('cx', last.x);
      endDot.setAttribute('cy', last.y);
    }

    root.classList.add('is-route-ready');
  };

  if (!gpxUrl || !svg) {
    return;
  }

  fetch(gpxUrl)
    .then((response) => response.text())
    .then((text) => {
      const points = parseGpx(text);
      renderRoute(points);
    })
    .catch(() => {
      root.classList.add('is-route-fallback');
    });
})();
