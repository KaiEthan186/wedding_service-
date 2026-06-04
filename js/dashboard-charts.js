(function () {
  const canvases = document.querySelectorAll('canvas[data-chart]');
  if (!canvases.length || typeof Chart === 'undefined') return;

  const rose = '#b76e79';
  const gold = '#c4a35a';
  const palette = ['#b76e79', '#c4a35a', '#9a5560', '#d4a5ad', '#8b7355', '#6b8e9f'];

  canvases.forEach((canvas) => {
    let payload = {};
    try {
      payload = JSON.parse(canvas.getAttribute('data-payload') || '{}');
    } catch (e) {
      return;
    }

    const type = canvas.getAttribute('data-chart');
    const labels = payload.labels || [];
    const values = payload.values || [];

    if (type === 'revenue') {
      new Chart(canvas, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Actual revenue',
              data: payload.actual || [],
              borderColor: rose,
              backgroundColor: 'rgba(183, 110, 121, 0.15)',
              fill: true,
              tension: 0.35,
            },
            {
              label: 'Forecast',
              data: payload.forecast || [],
              borderColor: gold,
              borderDash: [6, 4],
              tension: 0.35,
            },
          ],
        },
        options: chartOptions(),
      });
      return;
    }

    if (type === 'pie') {
      new Chart(canvas, {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [{ data: values, backgroundColor: palette }],
        },
        options: { ...chartOptions(), plugins: { legend: { position: 'bottom' } } },
      });
      return;
    }

    new Chart(canvas, {
      type: type === 'bar' ? 'bar' : 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Count',
            data: values,
            backgroundColor: type === 'bar' ? palette : rose,
            borderColor: rose,
            borderWidth: type === 'bar' ? 0 : 2,
            tension: 0.35,
          },
        ],
      },
      options: chartOptions(),
    });
  });

  function chartOptions() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: true } },
      scales: {
        y: { beginAtZero: true },
      },
    };
  }
})();
