<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Visitor statistics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1100px; margin: 24px auto; padding: 0 16px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        .total { color: #555; margin-bottom: 24px; }
        .charts { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; }
        h2 { font-size: 16px; margin: 0 0 12px; }
        @media (max-width: 800px) { .charts { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>Visitor statistics</h1>
    <div class="total">Total visits recorded: <strong id="total">…</strong></div>

    <div class="charts">
        <div class="card">
            <h2>Unique visitors per hour (last 24 h)</h2>
            <canvas id="hourly"></canvas>
        </div>
        <div class="card">
            <h2>Visits by city</h2>
            <canvas id="cities"></canvas>
        </div>
    </div>

    <script>
        fetch('/api/stats/data')
            .then(function (r) { return r.json(); })
            .then(function (d) {
                document.getElementById('total').textContent = d.total;

                new Chart(document.getElementById('hourly'), {
                    type: 'bar',
                    data: {
                        labels: d.hourly.labels,
                        datasets: [{
                            label: 'Unique visitors',
                            data: d.hourly.values,
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });

                new Chart(document.getElementById('cities'), {
                    type: 'pie',
                    data: {
                        labels: d.cities.labels,
                        datasets: [{ data: d.cities.values }]
                    }
                });
            });
    </script>
</body>
</html>
