<flux:card class="space-y-4">
    <flux:heading>Evolución de Facturación (12 meses)</flux:heading>
    <div 
        class="h-64"
        x-data="{
            labels: @js($stats['labels']),
            values: @js($stats['values']),
            init() {
                if (typeof Chart === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                    script.onload = () => this.renderChart();
                    document.head.appendChild(script);
                } else {
                    this.renderChart();
                }
            },
            renderChart() {
                new Chart(this.$refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: this.labels,
                        datasets: [{
                            label: 'Ventas ($)',
                            data: this.values,
                            backgroundColor: '#3b82f6',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { 
                                beginAtZero: true,
                                grid: { color: 'rgba(200, 200, 200, 0.1)' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        }"
    >
        <canvas x-ref="canvas"></canvas>
    </div>
</flux:card>
