import { Chart, registerables } from 'chart.js';
import { initDialog, initDialogTriggers, initEditingEntry } from './dialogs.js';

Chart.register(...registerables);

function initFuelChart() {
    const openButton = document.getElementById('fuel-chart-open');
    const dialog = document.getElementById('fuel-chart-dialog');
    const scrollContainer = document.getElementById('fuel-chart-scroll');
    const chartWrapper = document.getElementById('fuel-chart-wrapper');
    const canvas = document.getElementById('fuel-chart-canvas');
    const dataElement = document.getElementById('fuel-chart-data');

    if (! openButton || ! dialog || ! scrollContainer || ! chartWrapper || ! canvas || ! dataElement) {
        return;
    }

    const chartData = JSON.parse(dataElement.textContent);
    const pointWidth = 52;
    const chartHeight = 280;
    let chart = null;

    const renderChart = () => {
        if (chart) {
            return;
        }

        const containerWidth = scrollContainer.clientWidth || window.innerWidth;
        const chartWidth = Math.max(containerWidth, chartData.length * pointWidth);

        chartWrapper.style.width = `${chartWidth}px`;
        chartWrapper.style.height = `${chartHeight}px`;

        chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: chartData.map((point) => point.timestamp),
                datasets: [
                    {
                        label: 'Consum (L/100 km)',
                        data: chartData.map((point) => point.consumption),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgb(245 158 11 / 0.15)',
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fbbf24',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.25,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: '#18181b',
                        titleColor: '#f4f4f5',
                        bodyColor: '#e4e4e7',
                        borderColor: '#3f3f46',
                        borderWidth: 1,
                        callbacks: {
                            label: (context) => `${context.parsed.y} L/100 km`,
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#a1a1aa',
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: false,
                        },
                        grid: {
                            color: '#27272a',
                        },
                        title: {
                            display: true,
                            text: 'Data',
                            color: '#a1a1aa',
                        },
                    },
                    y: {
                        ticks: {
                            color: '#a1a1aa',
                        },
                        grid: {
                            color: '#27272a',
                        },
                        title: {
                            display: true,
                            text: 'Consum (L/100 km)',
                            color: '#a1a1aa',
                        },
                    },
                },
            },
        });
    };

    openButton.addEventListener('click', () => {
        dialog.showModal();
        renderChart();
    });

    dialog.querySelectorAll('[data-fuel-chart-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
}

function initFuelPage() {
    initDialog('fuel-history-open', 'fuel-history-dialog', 'data-fuel-history-close');
    initDialogTriggers();
    initEditingEntry('fuel-editing-entry');
    initFuelChart();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFuelPage);
} else {
    initFuelPage();
}
