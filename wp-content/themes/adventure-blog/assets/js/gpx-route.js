(() => {
  const mapEl = document.getElementById('route-map');
  const chartEl = document.getElementById('route-elevation-chart');

  if (!mapEl || !window.L || !window.Chart) {
    return;
  }

  const gpxUrl = mapEl.dataset.gpxUrl || (window.adventureGpx && window.adventureGpx.gpxUrl);
  if (!gpxUrl) {
    return;
  }

  const map = L.map(mapEl, {
    zoomControl: true,
    scrollWheelZoom: true,
  });

  L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
    maxZoom: 17,
    attribution: 'Map data: &copy; OpenStreetMap, SRTM | Map style: &copy; OpenTopoMap',
  }).addTo(map);

  const markerLayer = L.layerGroup().addTo(map);
  let routeLine = null;
  let chart = null;
  let trackPoints = [];

  const haversine = (a, b) => {
    const toRad = (deg) => (deg * Math.PI) / 180;
    const R = 6371000;
    const dLat = toRad(b.lat - a.lat);
    const dLon = toRad(b.lon - a.lon);
    const lat1 = toRad(a.lat);
    const lat2 = toRad(b.lat);
    const h =
      Math.sin(dLat / 2) ** 2 +
      Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLon / 2) ** 2;
    return 2 * R * Math.asin(Math.sqrt(h));
  };

  const parseGpx = (xmlText) => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(xmlText, 'application/xml');
    const pointNodes = [
      ...doc.querySelectorAll('trkpt'),
      ...doc.querySelectorAll('rtept'),
    ];

    const points = [];
    let cumulative = 0;

    pointNodes.forEach((node, index) => {
      const lat = parseFloat(node.getAttribute('lat'));
      const lon = parseFloat(node.getAttribute('lon'));
      const eleNode = node.querySelector('ele');
      const ele = eleNode ? parseFloat(eleNode.textContent) : null;

      if (Number.isNaN(lat) || Number.isNaN(lon)) {
        return;
      }

      if (index > 0) {
        cumulative += haversine(
          { lat: points[index - 1].lat, lon: points[index - 1].lon },
          { lat, lon }
        );
      }

      points.push({ lat, lon, ele, distance: cumulative / 1000 });
    });

    return points;
  };

  const setActivePoint = (index) => {
    if (!trackPoints[index]) {
      return;
    }

    markerLayer.clearLayers();
    const point = trackPoints[index];
    L.circleMarker([point.lat, point.lon], {
      radius: 7,
      color: '#a5a2af',
      fillColor: '#e4e5e2',
      fillOpacity: 1,
      weight: 2,
    }).addTo(markerLayer);
  };

  const setGpxDetail = (id, text) => {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = text;
    }
  };

  const updateGpxDetails = () => {
    if (!trackPoints.length) {
      return;
    }

    const elevations = trackPoints
      .map((p) => p.ele)
      .filter((ele) => ele !== null && !Number.isNaN(ele));

    const totalKm = trackPoints[trackPoints.length - 1].distance;
    setGpxDetail('route-gpx-distance', `${totalKm.toFixed(1)} km`);
    setGpxDetail('route-ele-stat-distance', `${totalKm.toFixed(1)} km`);

    if (elevations.length) {
      const minEle = Math.min(...elevations);
      const maxEle = Math.max(...elevations);
      let gain = 0;

      for (let i = 1; i < elevations.length; i += 1) {
        const diff = elevations[i] - elevations[i - 1];
        if (diff > 0) {
          gain += diff;
        }
      }

      const minText = `${Math.round(minEle)} m n.p.m.`;
      const maxText = `${Math.round(maxEle)} m n.p.m.`;
      const gainText = `${Math.round(gain)} m`;

      setGpxDetail('route-gpx-ele-min', minText);
      setGpxDetail('route-gpx-ele-max', maxText);
      setGpxDetail('route-gpx-ele-gain', gainText);
      setGpxDetail('route-ele-stat-min', minText);
      setGpxDetail('route-ele-stat-max', maxText);
      setGpxDetail('route-ele-stat-gain', gainText);
    }
  };

  const chartAxisColor = 'rgba(255, 255, 255, 0.45)';
  const chartGridColor = 'rgba(255, 255, 255, 0.08)';

  const renderChart = () => {
    const labels = trackPoints.map((p) => p.distance.toFixed(1));
    const elevations = trackPoints.map((p) => (p.ele ?? null));

    chart = new Chart(chartEl, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'Wysokość (m n.p.m.)',
            data: elevations,
            borderColor: '#a5a2af',
            backgroundColor: 'rgba(165, 162, 175, 0.22)',
            fill: true,
            tension: 0.25,
            pointRadius: 0,
            pointHoverRadius: 4,
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(15, 20, 14, 0.94)',
            borderColor: 'rgba(165, 162, 175, 0.35)',
            borderWidth: 1,
            titleColor: '#e4e5e2',
            bodyColor: '#c5c3cc',
            padding: 10,
            callbacks: {
              title: (items) => `Dystans: ${items[0].label} km`,
              label: (context) => {
                const value = context.parsed.y;
                return value === null
                  ? 'Brak danych wysokości'
                  : `Wysokość: ${Math.round(value)} m n.p.m.`;
              },
            },
          },
        },
        scales: {
          x: {
            display: true,
            title: {
              display: true,
              text: 'Dystans (km)',
              color: chartAxisColor,
              font: { size: 11 },
            },
            ticks: {
              color: chartAxisColor,
              maxTicksLimit: 7,
              maxRotation: 0,
            },
            grid: {
              color: chartGridColor,
            },
          },
          y: {
            display: true,
            title: {
              display: true,
              text: 'm n.p.m.',
              color: chartAxisColor,
              font: { size: 11 },
            },
            ticks: {
              color: chartAxisColor,
              maxTicksLimit: 6,
            },
            grid: {
              color: chartGridColor,
            },
          },
        },
        onHover: (_event, elements) => {
          if (elements.length) {
            setActivePoint(elements[0].index);
          }
        },
      },
    });
  };

  fetch(gpxUrl)
    .then((response) => response.text())
    .then((text) => {
      trackPoints = parseGpx(text);
      if (!trackPoints.length) {
        mapEl.innerHTML = '<p class="route-map__fallback">Nie udało się odczytać punktów z pliku GPX.</p>';
        return;
      }

      const latLngs = trackPoints.map((p) => [p.lat, p.lon]);
      routeLine = L.polyline(latLngs, {
        color: '#a5a2af',
        weight: 4,
        opacity: 0.9,
      }).addTo(map);

      map.fitBounds(routeLine.getBounds(), { padding: [24, 24] });

      L.marker(latLngs[0], { title: 'Start' }).addTo(map);
      L.marker(latLngs[latLngs.length - 1], { title: 'Meta' }).addTo(map);

      updateGpxDetails();

      if (chartEl) {
        renderChart();
      }

      setTimeout(() => map.invalidateSize(), 100);
    })
    .catch(() => {
      mapEl.innerHTML = '<p class="route-map__fallback">Nie udało się wczytać pliku GPX. Sprawdź, czy plik jest poprawny.</p>';
    });
})();
