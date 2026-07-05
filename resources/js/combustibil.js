import { Chart, registerables } from 'chart.js';
import { initDialog, initDialogTriggers, initEditingEntry } from './dialogs.js';

Chart.register(...registerables);

function parsePositiveNumber(value) {
    if (value.trim() === '') {
        return null;
    }

    const number = Number.parseFloat(value);

    return Number.isFinite(number) && number > 0 ? number : null;
}

function formatPrice(value) {
    return (Math.round(value * 100) / 100).toFixed(2);
}

function initFuelPriceCalculation(form) {
    const litersInput = form.querySelector('[name="liters"]');
    const totalPriceInput = form.querySelector('[data-fuel-price-field="total"]');
    const pricePerLiterInput = form.querySelector('[data-fuel-price-field="per-liter"]');

    if (! litersInput || ! totalPriceInput || ! pricePerLiterInput) {
        return;
    }

    let updating = false;

    const setValue = (input, value) => {
        updating = true;
        input.value = value;
        updating = false;
    };

    const updatePricePerLiter = () => {
        const liters = parsePositiveNumber(litersInput.value);
        const totalPrice = parsePositiveNumber(totalPriceInput.value);

        if (liters === null || totalPriceInput.value.trim() === '' || totalPrice === null) {
            return;
        }

        setValue(pricePerLiterInput, formatPrice(totalPrice / liters));
    };

    const updateTotalPrice = () => {
        const liters = parsePositiveNumber(litersInput.value);
        const pricePerLiter = parsePositiveNumber(pricePerLiterInput.value);

        if (liters === null || pricePerLiterInput.value.trim() === '' || pricePerLiter === null) {
            return;
        }

        setValue(totalPriceInput, formatPrice(liters * pricePerLiter));
    };

    totalPriceInput.addEventListener('keyup', () => {
        if (! updating) {
            updatePricePerLiter();
        }
    });

    pricePerLiterInput.addEventListener('keyup', () => {
        if (! updating) {
            updateTotalPrice();
        }
    });

    litersInput.addEventListener('keyup', () => {
        if (updating) {
            return;
        }

        if (totalPriceInput.value.trim() !== '') {
            updatePricePerLiter();
        } else if (pricePerLiterInput.value.trim() !== '') {
            updateTotalPrice();
        }
    });
}

function initFuelPriceForms() {
    document.querySelectorAll('.fuel-entry-form').forEach(initFuelPriceCalculation);
}

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
    initFuelPriceForms();
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
