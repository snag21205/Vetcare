<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import StatCard from '@/Components/StatCard.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, onMounted, nextTick } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    month: String,
    selectedDoctorId: [String, Number],
    doctors: Array,
    summary: Object,
    dailyData: Array,
    doctorStats: Array,
})

const selectedMonth = ref(props.month)
const selectedDoctor = ref(props.selectedDoctorId ?? '')

watch([selectedMonth, selectedDoctor], ([newMonth, newDoctor]) => {
    router.get(route('reports.revenue'), {
        month: newMonth,
        doctor_id: newDoctor,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['month', 'selectedDoctorId', 'summary', 'dailyData', 'doctorStats'],
        replace: true,
    })
})

const formatCurrency = (val) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val ?? 0)
}

// Chart references
const dailyChartCanvas = ref(null)
const breakdownChartCanvas = ref(null)
let dailyChartInstance = null
let breakdownChartInstance = null

function renderCharts() {
    if (dailyChartInstance) dailyChartInstance.destroy()
    if (breakdownChartInstance) breakdownChartInstance.destroy()

    // 1. Daily Chart (Line / Bar)
    if (dailyChartCanvas.value) {
        const labels = props.dailyData.map(d => `Ngày ${d.day}`)
        const revenues = props.dailyData.map(d => d.revenue)
        const expenses = props.dailyData.map(d => d.expense)

        dailyChartInstance = new Chart(dailyChartCanvas.value, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Doanh thu (VNĐ)',
                        data: revenues,
                        backgroundColor: 'rgba(2, 132, 199, 0.85)',
                        borderColor: '#0284c7',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'Chi phí (VNĐ)',
                        data: expenses,
                        backgroundColor: 'rgba(244, 63, 94, 0.75)',
                        borderColor: '#e11d48',
                        borderWidth: 1,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11, family: 'Plus Jakarta Sans, sans-serif' }, boxWidth: 12 }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatCurrency(ctx.raw)}`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            callback: (v) => v >= 1000000 ? `${v/1000000}M` : `${v/1000}k`
                        }
                    }
                }
            }
        })
    }

    // 2. Breakdown Chart (Doughnut)
    if (breakdownChartCanvas.value) {
        const total = (props.summary.revenue_service || 0) + (props.summary.revenue_medicine || 0)
        breakdownChartInstance = new Chart(breakdownChartCanvas.value, {
            type: 'doughnut',
            data: {
                labels: ['Dịch vụ kỹ thuật', 'Thuốc & Vật tư'],
                datasets: [{
                    data: [
                        props.summary.revenue_service || 0,
                        props.summary.revenue_medicine || 0
                    ],
                    backgroundColor: [
                        '#0284c7', // Sky 600
                        '#10b981', // Emerald 500
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11, family: 'Plus Jakarta Sans, sans-serif' }, boxWidth: 12 }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const val = ctx.raw
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0
                                return `${ctx.label}: ${formatCurrency(val)} (${pct}%)`
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        })
    }
}

onMounted(() => {
    nextTick(() => renderCharts())
})

watch(() => props.dailyData, () => {
    nextTick(() => renderCharts())
}, { deep: true })
</script>

<template>
    <Head title="Báo cáo Doanh thu" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Báo cáo Doanh thu & Chi phí</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Phân tích hiệu quả tài chính, cơ cấu nguồn thu và đóng góp của bác sĩ</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Nút xuất Excel -->
                    <a :href="route('reports.revenue.excel', { month: selectedMonth })"
                        class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                        <Icon name="document" size="14" />
                        <span>Xuất Excel (.csv)</span>
                    </a>
                </div>
            </div>

            <!-- Filter bar -->
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex flex-wrap items-center gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-600">Tháng:</span>
                    <input v-model="selectedMonth" type="month"
                        class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-semibold" />
                </div>

                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-600">Bác sĩ:</span>
                    <select v-model="selectedDoctor"
                        class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium">
                        <option value="">-- Tất cả bác sĩ --</option>
                        <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                            BS. {{ doc.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                <StatCard title="Tổng Doanh thu" :value="summary.total_revenue" icon="chart" color="sky" format="currency" />
                <StatCard title="Tổng Chi phí" :value="summary.total_expense" icon="document" color="red" format="currency" />
                <StatCard title="Lợi nhuận Thuần" :value="summary.net_profit" icon="check" :color="summary.net_profit >= 0 ? 'green' : 'red'" format="currency" />
                <StatCard title="Hóa đơn thanh toán" :value="summary.invoice_count" icon="invoice" color="blue" />
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Daily Chart (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Diễn biến Doanh thu & Chi phí theo ngày</h3>
                            <p class="text-[11px] text-slate-400">Tháng {{ selectedMonth }}</p>
                        </div>
                    </div>
                    <div class="h-64 relative">
                        <canvas ref="dailyChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Breakdown Chart (1 col) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                    <div class="border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">Cơ cấu Doanh thu</h3>
                        <p class="text-[11px] text-slate-400">Tỷ trọng tiền khám, thuốc và dịch vụ</p>
                    </div>
                    <div class="h-64 relative">
                        <canvas ref="breakdownChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tables Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Bác sĩ đóng góp -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                    <h3 class="text-sm font-bold text-slate-900 mb-3 border-b border-slate-100 pb-2.5">
                        Doanh thu theo Bác sĩ phụ trách
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold">Bác sĩ</th>
                                    <th class="px-4 py-2.5 font-semibold text-center">Số ca khám</th>
                                    <th class="px-4 py-2.5 font-semibold text-right">Doanh thu mang lại</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="doc in doctorStats" :key="doc.doctor_id" class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-semibold text-slate-900">BS. {{ doc.doctor_name }}</td>
                                    <td class="px-4 py-3 text-center tabular-nums font-bold">{{ doc.count }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-bold text-sky-700">
                                        {{ formatCurrency(doc.revenue) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Cơ cấu chi phí -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                    <h3 class="text-sm font-bold text-slate-900 mb-3 border-b border-slate-100 pb-2.5">
                        Cơ cấu Chi phí tháng {{ selectedMonth }}
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                                    <Icon name="pill" size="15" />
                                </div>
                                <span class="font-semibold text-emerald-900">Chi nhập kho thuốc & vật tư</span>
                            </div>
                            <span class="tabular-nums font-bold text-emerald-800">{{ formatCurrency(summary.expense_import) }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-sky-50/60 border border-sky-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                                    <Icon name="chart" size="15" />
                                </div>
                                <span class="font-semibold text-sky-900">Chi phí vận hành (Điện, nước, mặt bằng)</span>
                            </div>
                            <span class="tabular-nums font-bold text-sky-800">{{ formatCurrency(summary.expense_ops) }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center font-bold">
                                    <Icon name="document" size="15" />
                                </div>
                                <span class="font-semibold text-slate-900">Chi phí khác</span>
                            </div>
                            <span class="tabular-nums font-bold text-slate-800">{{ formatCurrency(summary.expense_other) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
