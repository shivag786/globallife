import ApexCharts from 'apexcharts';

// Brand-aligned categorical palette (matches the brand/gold tokens in app.css).
const PALETTE = ['#2c704c', '#d4af37', '#5fa97e', '#93c8a9', '#1f4834', '#b5912b'];

function inr(value) {
    return '₹' + Number(value).toLocaleString('en-IN', { maximumFractionDigits: 2 });
}

function buildOptions(cfg) {
    const circular = cfg.type === 'donut' || cfg.type === 'pie';
    const colors = cfg.colors && cfg.colors.length ? cfg.colors : PALETTE;

    const options = {
        chart: {
            type: cfg.type,
            height: cfg.height || 300,
            fontFamily: 'inherit',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeout', speed: 700 },
            parentHeightOffset: 0,
        },
        colors,
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '13px', markers: { radius: 12 }, labels: { colors: '#94a3b8' } },
        grid: { borderColor: 'rgba(148,163,184,0.15)', strokeDashArray: 4 },
        noData: { text: 'No data yet', style: { color: '#94a3b8', fontSize: '14px' } },
        tooltip: cfg.currency ? { y: { formatter: inr } } : {},
    };

    if (circular) {
        options.series = cfg.series;
        options.labels = cfg.labels;
        options.dataLabels = { enabled: true, formatter: (val) => Math.round(val) + '%' };
        options.plotOptions = { pie: { donut: { size: '68%' } } };
        options.stroke = { width: 0 };
        if (cfg.currency) {
            options.tooltip = { y: { formatter: inr } };
        }
        return options;
    }

    options.series = cfg.series;
    options.xaxis = {
        categories: cfg.categories,
        labels: { style: { colors: '#94a3b8', fontSize: '12px' } },
        axisBorder: { show: false },
        axisTicks: { show: false },
    };
    options.yaxis = {
        labels: {
            style: { colors: '#94a3b8', fontSize: '12px' },
            formatter: cfg.currency ? (v) => inr(Math.round(v)) : (v) => Math.round(v),
        },
    };

    if (cfg.type === 'area') {
        options.stroke = { curve: 'smooth', width: 3 };
        options.fill = {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.03, stops: [0, 90, 100] },
        };
    } else if (cfg.type === 'line') {
        options.stroke = { curve: 'smooth', width: 3 };
    } else if (cfg.type === 'bar') {
        options.plotOptions = { bar: { borderRadius: 6, columnWidth: '45%' } };
    }

    return options;
}

export function initCharts() {
    document.querySelectorAll('[data-apex-for]').forEach((scriptEl) => {
        const el = document.getElementById(scriptEl.dataset.apexFor);
        if (!el) return;

        let cfg;
        try {
            cfg = JSON.parse(scriptEl.textContent);
        } catch {
            return;
        }

        new ApexCharts(el, buildOptions(cfg)).render();
    });
}
