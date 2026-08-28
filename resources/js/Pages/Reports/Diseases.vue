<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    diseases: Array,
    totalCases: Number,
    speciesBreakdown: Array,
    selectedSpecies: String,
})

const speciesFilter = ref(props.selectedSpecies ?? '')

watch(speciesFilter, (newSpecies) => {
    router.get(route('reports.diseases'), {
        species: newSpecies,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['diseases', 'totalCases', 'speciesBreakdown', 'selectedSpecies'],
        replace: true,
    })
})

const chartCanvas = ref(null)
let chartInstance = null

function renderChart() {
    if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
    }
    if (!chartCanvas.value || props.diseases.length === 0) return

    // Top 7 common diseases
    const top7 = props.diseases.slice(0, 7)
    const labels = top7.map(d => `${d.diagnosis} (${d.species})`)
    const counts = top7.map(d => d.count)

    chartInstance = new Chart(chartCanvas.value, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: labels.map(() => 0),
                backgroundColor: [
                    '#0284c7', '#06b6d4', '#10b981', '#f59e0b',
                    '#ec4899', '#8b5cf6', '#64748b'
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1100,
                easing: 'easeOutQuart',
            },
            plugins: {
                legend: {
                    position: 'right',
                    labels: { font: { size: 11, family: 'Plus Jakarta Sans, sans-serif' }, boxWidth: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${ctx.raw} ca`
                    }
                }
            },
            cutout: '65%',
        }
    })

    setTimeout(() => {
        if (chartInstance) {
            chartInstance.data.datasets[0].data = counts
            chartInstance.update()
        }
    }, 40)
}

onMounted(() => {
    nextTick(() => {
        renderChart()
    })
})

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
    }
})

watch(() => props.diseases, () => {
    nextTick(() => {
        renderChart()
    })
}, { deep: true })
</script>

<template>

    <Head title="Báo cáo Bệnh phổ biến" />

        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Thống kê Bệnh phổ biến theo Loài</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Tổng hợp chẩn đoán từ hồ sơ bệnh án để phục vụ dự báo dịch
                        bệnh và nhập kho thuốc</p>
                </div>
            </div>

            <!-- Filter bar -->
            <div
                class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-600">Lọc theo loài vật nuôi:</span>
                    <select v-model="speciesFilter"
                        class="border border-slate-200 rounded-xl pr-9 px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium">
                        <option value=""> Tất cả loài </option>
                        <option value="chó">Chó</option>
                        <option value="mèo">Mèo</option>
                        <option value="chim">Chim</option>
                        <option value="khác">Loài khác</option>
                    </select>
                </div>

                <span class="text-xs text-slate-500">
                    Tổng cộng: <strong class="text-slate-900 font-bold tabular-nums">{{ totalCases }}</strong> ca khám
                    bệnh được ghi nhận
                </span>
            </div>

            <!-- Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Biểu đồ Top bệnh phổ biến -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                    <h3 class="text-sm font-bold text-slate-900 mb-1">Top các loại bệnh thường gặp nhất</h3>
                    <p class="text-[11px] text-slate-400 mb-4">Tỷ trọng các chẩn đoán lâm sàng</p>
                    <div class="h-64 relative">
                        <canvas ref="chartCanvas"></canvas>
                    </div>
                </div>

                <!-- Thống kê theo loài -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                    <h3 class="text-sm font-bold text-slate-900 mb-3 border-b border-slate-100 pb-2.5">
                        Số ca khám theo loài
                    </h3>
                    <div class="space-y-3">
                        <div v-for="sp in speciesBreakdown" :key="sp.species"
                            class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                                    <Icon name="pet" size="15" />
                                </div>
                                <span class="capitalize font-semibold text-slate-900">{{ sp.species }}</span>
                            </div>
                            <span class="tabular-nums font-bold text-slate-800">{{ sp.count }} ca</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden text-xs">
                <div class="p-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm">Bảng chi tiết chẩn đoán bệnh</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead
                            class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold text-center w-12">STT</th>
                                <th class="px-5 py-3.5 font-semibold">Loài vật nuôi</th>
                                <th class="px-5 py-3.5 font-semibold">Chẩn đoán / Loại bệnh</th>
                                <th class="px-5 py-3.5 font-semibold text-center">Số ca ghi nhận</th>
                                <th class="px-5 py-3.5 font-semibold text-right">Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, idx) in diseases" :key="idx" class="hover:bg-slate-50/80">
                                <td class="px-5 py-3.5 text-slate-400 text-center font-medium">{{ idx + 1 }}</td>
                                <td class="px-5 py-3.5 capitalize font-semibold text-slate-800">
                                    <span class="inline-flex items-center gap-1.5">
                                        <Icon name="pet" size="13" class="text-sky-600" />
                                        {{ item.species }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-slate-900">
                                    {{ item.diagnosis }}
                                </td>
                                <td class="px-5 py-3.5 text-center tabular-nums font-bold text-sky-700">
                                    {{ item.count }}
                                </td>
                                <td class="px-5 py-3.5 text-right tabular-nums font-semibold text-slate-600">
                                    {{ totalCases > 0 ? ((item.count / totalCases) * 100).toFixed(1) : 0 }}%
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="diseases.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="stethoscope" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Chưa có dữ liệu chẩn đoán bệnh</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</template>
