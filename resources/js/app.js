import './bootstrap';
import ApexCharts from 'apexcharts';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

// PWA service worker registration (Fase 7)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            /* registration is best-effort; ignore failures */
        });
    });
}

const defaultConfirmMessage = 'Apakah Anda yakin ingin melanjutkan aksi ini?';
const destructivePattern = /\b(hapus|delete|remove|destroy|batalkan|cancel)\b/i;

function buildConfirmOptions(message, trigger) {
    const text = message || defaultConfirmMessage;
    const isDestructive = destructivePattern.test(text) || trigger?.dataset.confirmVariant === 'danger';

    return {
        title: trigger?.dataset.confirmTitle || (isDestructive ? 'Konfirmasi Hapus' : 'Konfirmasi'),
        text,
        icon: isDestructive ? 'warning' : 'question',
        showCancelButton: true,
        reverseButtons: true,
        focusCancel: true,
        confirmButtonText: trigger?.dataset.confirmYes || (isDestructive ? 'Ya, Hapus' : 'Ya'),
        cancelButtonText: trigger?.dataset.confirmNo || 'Tidak',
        buttonsStyling: false,
        customClass: {
            confirmButton: isDestructive ? 'btn btn-error text-white' : 'btn btn-primary',
            cancelButton: 'btn btn-ghost',
        },
    };
}

async function askForConfirmation(message, trigger) {
    const result = await Swal.fire(buildConfirmOptions(message, trigger));

    return Boolean(result.isConfirmed);
}

function showPopupNotification(detail = {}) {
    const type = detail.type === 'error' ? 'error' : 'success';

    void Swal.fire({
        title: detail.title || (type === 'error' ? 'Gagal' : 'Berhasil'),
        text: detail.message || '',
        icon: type,
        timer: type === 'success' ? 1800 : undefined,
        timerProgressBar: type === 'success',
        buttonsStyling: false,
        confirmButtonText: 'OK',
        customClass: {
            confirmButton: type === 'error' ? 'btn btn-error text-white' : 'btn btn-primary',
        },
    });
}

window.addEventListener('cart-notification', (event) => {
    showPopupNotification(event.detail || {});
});

document.addEventListener(
    'submit',
    (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const message = form.dataset.confirm;
        if (!message) {
            return;
        }

        if (form.dataset.confirmed === '1') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        void askForConfirmation(message, form).then((confirmed) => {
            if (!confirmed) {
                return;
            }

            form.dataset.confirmed = '1';

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();

                return;
            }

            form.submit();
        });
    },
    true,
);

document.addEventListener(
    'click',
    (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const trigger = target.closest('[data-confirm]');
        if (!trigger || trigger instanceof HTMLFormElement) {
            return;
        }

        // Let form-level confirmation handle submit buttons.
        if (trigger.closest('form[data-confirm]')) {
            return;
        }

        const message = trigger.getAttribute('data-confirm');

        if (trigger.dataset.confirmed === '1') {
            delete trigger.dataset.confirmed;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        void askForConfirmation(message, trigger).then((confirmed) => {
            if (!confirmed) {
                return;
            }

            trigger.dataset.confirmed = '1';
            trigger.click();
        });
    },
    true,
);

// Chart Report
window.ApexCharts = ApexCharts;

document.addEventListener('alpine:init', () => {
    Alpine.data('salesChartHandler', (initialLabels, initialValues) => ({
        chart: null,

        init() {
            this.renderChart(initialLabels, initialValues);
        },

        renderChart(labels, values) {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }

            const hasData = values.some(v => v > 0);
            if (!hasData) {
                this.$refs.apexChart.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-stone-500 pt-10 pb-10">
                        <i class="ri-bar-chart-2-line text-6xl mb-3 text-stone-300"></i>
                        <p class="font-medium text-lg text-stone-600">Belum ada data penjualan</p>
                        <p class="text-sm mt-1">Data grafik akan muncul setelah ada transaksi dibayar pada periode ini.</p>
                    </div>
                `;
                return;
            }

            this.$refs.apexChart.innerHTML = '';

            let options = {
                series: [{
                    name: 'Pendapatan',
                    data: values
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                colors: ['#065f46'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: labels,
                    tooltip: { enabled: false },
                    labels: { style: { colors: '#62646b' } }
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true,
                    decimalsInFloat: 0,
                    labels: {
                        style: { colors: '#62646b' },
                        formatter: function (value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                }
            };

            this.chart = new window.ApexCharts(this.$refs.apexChart, options);
            this.chart.render();
        },

        updateChart(labels, values) {
            this.renderChart(labels, values);
        }
    }));
});
