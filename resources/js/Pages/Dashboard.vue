<script setup>
import StatCard from '@/Components/StatCard.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useAuth } from '@/Composables/useAuth.js'
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    stats: Object,
    todayAppointments: Array,
    pendingInvoices: Array,
    recentRecords: Array,
    recentExpenses: Array,
    inProgressRecords: Array,
    lowStockMedicines: Array,
    expiringMedicines: Array,
    revenue7Days: Array,
})

const { isAdmin, isVet, isReceptionist, isAccountant, user } = useAuth()

const statusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-amber-50 text-amber-700 border border-amber-200',
        confirmed: 'bg-sky-50 text-sky-700 border border-sky-200',
        checked_in: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        completed: 'bg-slate-100 text-slate-700 border border-slate-200',
        cancelled: 'bg-rose-50 text-rose-700 border border-rose-200',
        no_show: 'bg-orange-50 text-orange-700 border border-orange-200',
        in_progress: 'bg-amber-50 text-amber-700 border border-amber-200',
    }
    return classes[status] ?? 'bg-slate-100 text-slate-600'
}

const statusLabel = (status) => {
    const labels = {
        pending: 'Chờ xác nhận',
        confirmed: 'Đã xác nhận',
        checked_in: 'Đã check-in',
        completed: 'Đã hoàn thành',
        cancelled: 'Đã hủy',
        no_show: 'Không đến',
        in_progress: 'Đang theo dõi',
    }
    return labels[status] ?? status
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount ?? 0)
}

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

const formatTime = (dateStr) => {
    if (!dateStr) return ''
    return new Date(dateStr).toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
    })
}

function checkIn(id) {
    router.post(route('appointments.check-in', id), {}, { preserveScroll: true })
}

function confirmAppointment(id) {
    router.post(route('appointments.confirm', id), {}, { preserveScroll: true })
}

const adminChartCanvas = ref(null)
let adminChartInstance = null

function renderAdminChart() {
    if (!isAdmin.value || !props.revenue7Days?.length) return
    if (adminChartInstance) {
        adminChartInstance.destroy()
        adminChartInstance = null
    }
    if (!adminChartCanvas.value) return

    const labels = props.revenue7Days.map(d => d.date)
    const values = props.revenue7Days.map(d => d.value)

    adminChartInstance = new Chart(adminChartCanvas.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: labels.map(() => 0),
                backgroundColor: '#0284c7',
                borderRadius: 6,
                barThickness: 24,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 900,
                easing: 'easeOutQuart',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `Doanh thu: ${formatCurrency(ctx.raw)}`
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: (v) => v >= 1000000 ? `${v / 1000000}M` : `${v / 1000}k`
                    }
                }
            }
        }
    })

    setTimeout(() => {
        if (adminChartInstance) {
            adminChartInstance.data.datasets[0].data = values
            adminChartInstance.update()
        }
    }, 40)
}

onMounted(() => {
    nextTick(() => {
        renderAdminChart()
    })
})

onUnmounted(() => {
    if (adminChartInstance) {
        adminChartInstance.destroy()
        adminChartInstance = null
    }
})

watch(() => props.revenue7Days, () => {
    nextTick(() => {
        renderAdminChart()
    })
}, { deep: true })
</script>

<template>

    <Head title="Tổng quan" />

        <div class="space-y-6 pb-12">

            <!-- Header Tổng quan & Chào mừng -->
            <div
                class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Xin chào, {{ user?.name }}
                        </h1>
                        <span
                            class="text-xs bg-sky-50 text-sky-700 border border-sky-200 px-3 py-0.5 rounded-full font-bold uppercase tracking-wider">
                            {{ isAdmin ? 'Quản trị viên' : (isVet ? 'Bác sĩ Thú y' : (isReceptionist ? 'Lễ tân tiếp đón'
                            : 'Kế toán')) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">
                        Hệ thống quản lý phòng khám Thú y VetCare · {{ new Date().toLocaleDateString('vi-VN', {
                            weekday:
                                'long', day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                    </p>
                </div>

                <div
                    class="flex items-center gap-2.5 self-start sm:self-auto bg-slate-50 border border-slate-200/80 px-3.5 py-2 rounded-xl text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    <span class="font-semibold text-slate-700">Hệ thống đang hoạt động</span>
                </div>
            </div>

            <!-- Phím tắt Thao tác nhanh (Quick Actions) -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Thao tác nhanh</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 text-xs">

                    <!-- Lễ tân / Admin: Đặt lịch -->
                    <Link v-if="isReceptionist || isAdmin" :href="route('appointments.create')"
                        class="p-3 bg-white hover:bg-sky-50/70 border border-slate-200/80 rounded-2xl shadow-2xs hover:border-sky-300 transition-all flex items-center gap-2.5 group">
                        <div
                            class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 group-hover:bg-sky-600 group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <Icon name="calendar" size="16" />
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-sky-700">Đặt lịch hẹn</span>
                    </Link>

                    <!-- Lễ tân / Admin: Khách hàng mới -->
                    <Link v-if="isReceptionist || isAdmin" :href="route('customers.create')"
                        class="p-3 bg-white hover:bg-sky-50/70 border border-slate-200/80 rounded-2xl shadow-2xs hover:border-sky-300 transition-all flex items-center gap-2.5 group">
                        <div
                            class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <Icon name="users" size="16" />
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-sky-700">Thêm khách</span>
                    </Link>

                    <!-- Lễ tân / Admin: Vật nuôi mới -->
                    <Link v-if="isReceptionist || isAdmin" :href="route('pets.create')"
                        class="p-3 bg-white hover:bg-sky-50/70 border border-slate-200/80 rounded-2xl shadow-2xs hover:border-sky-300 transition-all flex items-center gap-2.5 group">
                        <div
                            class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <Icon name="pet" size="16" />
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-sky-700">Thêm thú cưng</span>
                    </Link>

                    <!-- Bác sĩ / Admin: Lập phiếu khám -->
                    <Link v-if="isVet || isAdmin" :href="route('medical-records.create')"
                        class="p-3 bg-white hover:bg-sky-50/70 border border-slate-200/80 rounded-2xl shadow-2xs hover:border-sky-300 transition-all flex items-center gap-2.5 group">
                        <div
                            class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <Icon name="stethoscope" size="16" />
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-sky-700">Khám bệnh mới</span>
                    </Link>

                    <!-- Kế toán / Admin: Lập phiếu chi -->
                    <Link v-if="isAccountant || isAdmin" :href="route('expense-vouchers.create')"
                        class="p-3 bg-white hover:bg-sky-50/70 border border-slate-200/80 rounded-2xl shadow-2xs hover:border-sky-300 transition-all flex items-center gap-2.5 group">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <Icon name="document" size="16" />
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-sky-700">Lập phiếu chi</span>
                    </Link>

                    <!-- Admin: Thêm thuốc mới -->
                    <Link v-if="isAdmin" :href="route('medicines.create')"
                        class="p-3 bg-white hover:bg-sky-50/70 border border-slate-200/80 rounded-2xl shadow-2xs hover:border-sky-300 transition-all flex items-center gap-2.5 group">
                        <div
                            class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <Icon name="pill" size="16" />
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-sky-700">Nhập thuốc mới</span>
                    </Link>

                    <!-- Báo cáo -->
                    <Link v-if="isAccountant || isAdmin" :href="route('reports.revenue')"
                        class="p-3 bg-white hover:bg-sky-50/70 border border-slate-200/80 rounded-2xl shadow-2xs hover:border-sky-300 transition-all flex items-center gap-2.5 group">
                        <div
                            class="w-8 h-8 rounded-xl bg-violet-50 text-violet-600 group-hover:bg-violet-600 group-hover:text-white flex items-center justify-center transition-colors shrink-0">
                            <Icon name="chart" size="16" />
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-sky-700">Báo cáo thu chi</span>
                    </Link>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- 1. DASHBOARD DÀNH CHO ADMIN                -->
            <!-- ========================================== -->
            <template v-if="isAdmin">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard title="Tổng khách hàng" :value="stats?.totalCustomers" icon="users" color="sky" />
                    <StatCard title="Lịch hẹn hôm nay" :value="stats?.todayAppointments" icon="calendar" color="blue" />
                    <StatCard title="Doanh thu tháng này" :value="stats?.monthRevenue" icon="chart" color="green"
                        format="currency" />
                    <StatCard title="Lợi nhuận thuần tháng" :value="stats?.monthProfit" icon="invoice" color="cyan"
                        format="currency" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

                    <!-- Cột chính (2 cols): Biểu đồ 7 ngày & Hoạt động gần đây -->
                    <div class="lg:col-span-2 space-y-5">

                        <!-- Mini Chart Doanh thu 7 ngày -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h3
                                        class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                        <Icon name="chart" size="15" class="text-sky-600" />
                                        <span>Diễn biến Doanh thu 7 ngày gần nhất</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Xu hướng thu tiền từ các ca khám và bán
                                        thuốc</p>
                                </div>
                                <Link :href="route('reports.revenue')"
                                    class="text-xs text-sky-600 hover:text-sky-800 font-semibold">
                                    Chi tiết →
                                </Link>
                            </div>
                            <div class="h-48 w-full">
                                <canvas ref="adminChartCanvas"></canvas>
                            </div>
                        </div>

                        <!-- 6 ca khám gần nhất -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <Icon name="stethoscope" size="15" class="text-sky-600" />
                                    <span>Hồ sơ khám bệnh vừa thực hiện</span>
                                </h3>
                                <Link :href="route('medical-records.index')"
                                    class="text-sky-600 hover:text-sky-800 font-semibold">
                                    Xem tất cả →
                                </Link>
                            </div>

                            <div v-if="!recentRecords?.length" class="py-6 text-center text-slate-400">
                                Chưa có hồ sơ khám bệnh nào gần đây.
                            </div>

                            <div v-else class="divide-y divide-slate-100">
                                <div v-for="rec in recentRecords" :key="rec.id"
                                    class="py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 truncate">
                                            {{ rec.pet?.name }}
                                            <span class="text-slate-400 font-normal text-[11px]">({{ rec.pet?.species
                                                }}) · Chủ: {{ rec.pet?.customer?.name }}</span>
                                        </p>
                                        <p class="text-[11px] text-slate-500 truncate">
                                            <strong class="text-slate-700">Chẩn đoán:</strong> {{ rec.diagnosis }} ·
                                            <span class="text-sky-700 font-medium">BS. {{ rec.doctor?.name }}</span>
                                        </p>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-2">
                                        <span :class="statusBadgeClass(rec.status)"
                                            class="whitespace-nowrap text-[10px] px-2 py-0.5 rounded-full font-bold">
                                            {{ statusLabel(rec.status) }}
                                        </span>
                                        <Link :href="route('medical-records.show', rec.id)"
                                            class="text-sky-600 hover:text-sky-800 font-semibold text-[11px]">
                                            Xem
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Cột phụ (1 col): Cảnh báo kho & Hóa đơn mới nhất -->
                    <div class="space-y-5">

                        <!-- Cảnh báo thuốc sắp hết kho -->
                        <div v-if="lowStockMedicines?.length > 0"
                            class="bg-rose-50/70 border border-rose-200/80 rounded-2xl p-5 shadow-xs space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-rose-200/60 pb-2.5">
                                <div
                                    class="flex items-center gap-2 text-rose-900 font-bold uppercase tracking-wider text-[11px]">
                                    <Icon name="alert" size="15" class="text-rose-600" />
                                    <span>Thuốc sắp hết kho ({{ lowStockMedicines.length }})</span>
                                </div>
                                <Link :href="route('medicines.index')"
                                    class="text-rose-700 hover:text-rose-900 font-bold text-[11px]">
                                    Nhập kho →
                                </Link>
                            </div>
                            <ul class="space-y-2 text-rose-800 divide-y divide-rose-100">
                                <li v-for="med in lowStockMedicines" :key="med.id"
                                    class="pt-1.5 first:pt-0 flex items-center justify-between">
                                    <span class="font-semibold truncate max-w-[150px]">{{ med.name }}</span>
                                    <span class="tabular-nums font-bold">Còn {{ med.stock_quantity }} {{ med.unit
                                        }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Cảnh báo thuốc sắp hết hạn -->
                        <div v-if="expiringMedicines?.length > 0"
                            class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-5 shadow-xs space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-amber-200/60 pb-2.5">
                                <div
                                    class="flex items-center gap-2 text-amber-900 font-bold uppercase tracking-wider text-[11px]">
                                    <Icon name="clock" size="15" class="text-amber-600" />
                                    <span>Hết hạn ≤ 30 ngày ({{ expiringMedicines.length }})</span>
                                </div>
                                <Link :href="route('medicines.index')"
                                    class="text-amber-700 hover:text-amber-900 font-bold text-[11px]">
                                    Xem kho →
                                </Link>
                            </div>
                            <ul class="space-y-2 text-amber-800 divide-y divide-amber-100">
                                <li v-for="med in expiringMedicines" :key="med.id"
                                    class="pt-1.5 first:pt-0 flex items-center justify-between">
                                    <span class="font-semibold truncate max-w-[150px]">{{ med.name }}</span>
                                    <span class="tabular-nums font-bold">HSD: {{ formatDate(med.expiry_date) }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Hóa đơn chờ thu tiền -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                <h3
                                    class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 text-[11px]">
                                    <Icon name="invoice" size="14" class="text-sky-600" />
                                    <span>Hóa đơn vừa tạo</span>
                                </h3>
                                <Link :href="route('invoices.index')"
                                    class="text-sky-600 hover:text-sky-800 font-semibold text-[11px]">
                                    Tất cả →
                                </Link>
                            </div>

                            <div v-if="!pendingInvoices?.length" class="py-4 text-center text-slate-400">
                                Chưa có hóa đơn nào
                            </div>

                            <div v-else class="divide-y divide-slate-100">
                                <div v-for="inv in pendingInvoices" :key="inv.id"
                                    class="py-2 flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-sky-700 font-mono text-[11px]">{{ inv.invoice_number }}
                                        </p>
                                        <p class="text-slate-500 text-[10px]">{{ inv.customer?.name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold tabular-nums text-slate-900 block text-[11px]">{{
                                            formatCurrency(inv.total_amount) }}</span>
                                        <span
                                            :class="inv.status === 'paid' ? 'text-emerald-600 font-semibold text-[10px]' : 'text-amber-600 font-semibold text-[10px]'">
                                            {{ inv.status === 'paid' ? 'Đã thu' : 'Chờ thu' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </template>

            <!-- ========================================== -->
            <!-- 2. DASHBOARD DÀNH CHO LỄ TÂN               -->
            <!-- ========================================== -->
            <template v-if="isReceptionist">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard title="Lịch hẹn hôm nay" :value="stats?.todayAppointments" icon="calendar" color="sky" />
                    <StatCard title="Đã Check-in (Chờ khám)" :value="stats?.checkedIn" icon="check-circle"
                        color="green" />
                    <StatCard title="Hóa đơn chờ thu tiền" :value="stats?.pendingPayments" icon="invoice"
                        color="yellow" />
                    <StatCard title="Khách mới trong tháng" :value="stats?.newCustomersMonth" icon="users"
                        color="cyan" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

                    <!-- Cột chính (2 cols): Bàn tiếp đón & Điều phối lịch khám hôm nay -->
                    <div
                        class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <Icon name="calendar" size="16" class="text-sky-600" />
                                    <span>Bàn Tiếp đón & Điều phối Lịch khám hôm nay</span>
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Thao tác check-in trực tiếp khi khách hàng
                                    mang thú cưng đến</p>
                            </div>
                            <Link :href="route('appointments.index')"
                                class="text-sky-600 hover:text-sky-800 font-semibold">
                                Quản lý lịch hẹn →
                            </Link>
                        </div>

                        <div v-if="!todayAppointments?.length" class="text-slate-400 text-center py-10">
                            <Icon name="calendar" size="32" class="mx-auto text-slate-300 mb-2" />
                            <p class="font-semibold text-slate-600">Hôm nay không có lịch hẹn nào</p>
                            <Link :href="route('appointments.create')"
                                class="mt-2 inline-block text-sky-600 hover:underline font-semibold">
                                + Đặt lịch hẹn mới cho khách
                            </Link>
                        </div>

                        <div v-else class="divide-y divide-slate-100">
                            <div v-for="apt in todayAppointments" :key="apt.id"
                                class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 text-sm">{{ apt.pet?.name }}</span>
                                        <span class="text-slate-400">({{ apt.pet?.species }})</span>
                                        <span class="text-slate-300">·</span>
                                        <span class="text-slate-700 font-semibold">{{ apt.pet?.customer?.name }}</span>
                                        <span class="text-slate-400 tabular-nums">({{ apt.pet?.customer?.phone
                                            }})</span>
                                    </div>
                                    <p class="text-slate-500 text-[11px]">
                                        <strong>Giờ hẹn:</strong> {{ formatTime(apt.scheduled_at) }} ·
                                        <strong>Lý do:</strong> {{ apt.reason }} ·
                                        <span v-if="apt.doctor" class="text-sky-700 font-medium">BS. {{ apt.doctor.name
                                            }}</span>
                                    </p>
                                </div>

                                <!-- Action buttons -->
                                <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                                    <span :class="statusBadgeClass(apt.status)"
                                        class="whitespace-nowrap text-[11px] px-2.5 py-1 rounded-full font-bold">
                                        {{ statusLabel(apt.status) }}
                                    </span>

                                    <!-- Nút Xác nhận nếu pending -->
                                    <button v-if="apt.status === 'pending'" @click="confirmAppointment(apt.id)"
                                        class="px-3 py-1.5 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded-xl font-semibold transition-colors cursor-pointer">
                                        Xác nhận
                                    </button>

                                    <!-- Nút Check-in nếu confirmed -->
                                    <button v-if="apt.status === 'confirmed'" @click="checkIn(apt.id)"
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-xs transition-colors cursor-pointer">
                                        Check-in
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột phụ (1 col): Bàn Thu ngân & Hóa đơn chờ thu -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <Icon name="invoice" size="16" class="text-sky-600" />
                                <span>Hóa đơn chờ thu tiền</span>
                            </h3>
                            <Link :href="route('invoices.index')" class="text-sky-600 hover:text-sky-800 font-semibold">
                                Tất cả →
                            </Link>
                        </div>

                        <div v-if="!pendingInvoices?.length" class="py-8 text-center text-slate-400">
                            Không có hóa đơn nào đang chờ thu tiền
                        </div>

                        <div v-else class="divide-y divide-slate-100">
                            <div v-for="inv in pendingInvoices" :key="inv.id"
                                class="py-3 flex items-center justify-between gap-2">
                                <div>
                                    <p class="font-bold text-sky-700 font-mono">{{ inv.invoice_number }}</p>
                                    <p class="text-slate-700 font-semibold">{{ inv.customer?.name }}</p>
                                    <p class="text-slate-400 tabular-nums text-[10px]">{{ inv.customer?.phone }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold tabular-nums text-slate-900 block">{{
                                        formatCurrency(inv.total_amount) }}</span>
                                    <Link :href="route('invoices.show', inv.id)"
                                        class="inline-block mt-1 bg-sky-600 hover:bg-sky-700 text-white font-semibold px-2.5 py-0.5 rounded-lg text-[10px] shadow-2xs">
                                        Thu tiền
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </template>

            <!-- ========================================== -->
            <!-- 3. DASHBOARD DÀNH CHO BÁC SĨ (VET)         -->
            <!-- ========================================== -->
            <template v-if="isVet">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard title="Bệnh nhân hôm nay" :value="stats?.myTodayPatients" icon="pet" color="sky" />
                    <StatCard title="Đang chờ khám (Sảnh)" :value="stats?.waitingPatients" icon="clock"
                        color="yellow" />
                    <StatCard title="Đã khám xong hôm nay" :value="stats?.completedToday" icon="check-circle"
                        color="green" />
                    <StatCard title="Ca đang theo dõi" :value="stats?.inProgressCount" icon="stethoscope"
                        color="cyan" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

                    <!-- Hàng đợi bệnh nhân đang chờ khám (2 cols) -->
                    <div class="lg:col-span-2 space-y-5">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4 text-xs">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h3
                                        class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                        <Icon name="stethoscope" size="16" class="text-sky-600" />
                                        <span>Hàng đợi Bệnh nhân Chờ khám</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Danh sách thú cưng đã check-in đang chờ
                                        bác sĩ vào phòng khám</p>
                                </div>
                                <Link :href="route('medical-records.create')"
                                    class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold px-3.5 py-1.5 rounded-xl shadow-xs transition-colors cursor-pointer">
                                    <Icon name="plus" size="14" />
                                    <span>Lập phiếu khám tự do</span>
                                </Link>
                            </div>

                            <div v-if="!todayAppointments?.length" class="text-slate-400 text-center py-10">
                                <Icon name="stethoscope" size="36" class="mx-auto text-slate-300 mb-2" />
                                <p class="font-semibold text-slate-600">Không có bệnh nhân nào trong hàng đợi hôm nay
                                </p>
                            </div>

                            <div v-else class="divide-y divide-slate-100">
                                <div v-for="apt in todayAppointments" :key="apt.id"
                                    class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100 shrink-0">
                                            <Icon name="pet" size="18" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-900 text-sm">{{ apt.pet?.name
                                                    }}</span>
                                                <span class="text-slate-400">({{ apt.pet?.species }})</span>
                                                <span v-if="apt.pet?.weight"
                                                    class="text-slate-500 font-mono text-[11px]">· {{ apt.pet?.weight }}
                                                    kg</span>
                                            </div>
                                            <p class="text-slate-500 text-[11px] mt-0.5">
                                                Chủ: {{ apt.pet?.customer?.name }} ({{ apt.pet?.customer?.phone }}) ·
                                                <strong>Lý do:</strong> {{ apt.reason }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                                        <span :class="statusBadgeClass(apt.status)"
                                            class="whitespace-nowrap text-[11px] px-2.5 py-1 rounded-full font-bold">
                                            {{ statusLabel(apt.status) }}
                                        </span>

                                        <Link v-if="apt.status === 'checked_in' && !apt.medical_record"
                                            :href="route('medical-records.create', { appointment_id: apt.id })"
                                            class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold px-3.5 py-1.5 rounded-xl shadow-xs transition-colors">
                                            <Icon name="stethoscope" size="14" />
                                            <span>Bắt đầu khám</span>
                                        </Link>

                                        <Link v-if="apt.medical_record"
                                            :href="route('medical-records.show', apt.medical_record.id)"
                                            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-xl transition-colors">
                                            <span>Xem bệnh án</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ca bệnh đang theo dõi & Lời khuyên AI (1 col) -->
                    <div class="space-y-5">

                        <!-- Ca bệnh đang theo dõi điều trị (In-progress) -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3 text-xs">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                <h3
                                    class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 text-[11px]">
                                    <Icon name="clock" size="14" class="text-amber-600" />
                                    <span>Ca bệnh đang theo dõi ({{ inProgressRecords?.length ?? 0 }})</span>
                                </h3>
                                <Link :href="route('medical-records.index')"
                                    class="text-sky-600 hover:text-sky-800 font-semibold text-[11px]">
                                    Tất cả →
                                </Link>
                            </div>

                            <div v-if="!inProgressRecords?.length" class="py-6 text-center text-slate-400">
                                Không có ca bệnh nào đang theo dõi
                            </div>

                            <div v-else class="divide-y divide-slate-100">
                                <div v-for="rec in inProgressRecords" :key="rec.id"
                                    class="py-2.5 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 truncate">{{ rec.pet?.name }}</p>
                                        <p class="text-slate-500 text-[11px] truncate">{{ rec.diagnosis }}</p>
                                    </div>
                                    <Link :href="route('medical-records.show', rec.id)"
                                        class="text-sky-600 hover:text-sky-800 font-semibold text-[11px] shrink-0">
                                        Chi tiết
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Card AI Powered Assistant Tip -->
                        <div class="bg-sky-50/70 border border-sky-200 rounded-2xl p-5 space-y-2 text-xs">
                            <div class="flex items-center gap-2 text-sky-900 font-bold">
                                <Icon name="sparkles" size="16" class="text-sky-600" />
                                <span>Trợ lý AI Gemini Pro</span>
                            </div>
                            <p class="text-slate-600 text-[11px] leading-relaxed">
                                Khi lập phiếu khám, bạn chỉ cần nhập triệu chứng lâm sàng và bấm <strong>"AI Gợi ý chẩn
                                    đoán"</strong> để nhận ngay phác đồ điều trị khớp chính xác với kho thuốc của phòng
                                khám.
                            </p>
                        </div>

                    </div>

                </div>
            </template>

            <!-- ========================================== -->
            <!-- 4. DASHBOARD DÀNH CHO KẾ TOÁN (ACCOUNTANT) -->
            <!-- ========================================== -->
            <template v-if="isAccountant">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard title="Doanh thu hôm nay" :value="stats?.todayRevenue" icon="chart" color="green"
                        format="currency" />
                    <StatCard title="Doanh thu tháng này" :value="stats?.monthRevenue" icon="chart" color="sky"
                        format="currency" />
                    <StatCard title="Chi phí tháng này" :value="stats?.monthExpense" icon="document" color="red"
                        format="currency" />
                    <StatCard title="Lợi nhuận thuần tháng" :value="stats?.monthProfit" icon="invoice" color="cyan"
                        format="currency" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

                    <!-- Hóa đơn chờ thu tiền & Công nợ (2 cols) -->
                    <div
                        class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <Icon name="invoice" size="16" class="text-sky-600" />
                                    <span>Hóa đơn chưa thanh toán</span>
                                </h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tổng công nợ chờ thu: <strong
                                        class="text-rose-600">{{ formatCurrency(stats?.pendingAmount) }}</strong></p>
                            </div>
                            <Link :href="route('invoices.index')" class="text-sky-600 hover:text-sky-800 font-semibold">
                                Tất cả hóa đơn →
                            </Link>
                        </div>

                        <div v-if="!pendingInvoices?.length" class="py-8 text-center text-slate-400">
                            Không có hóa đơn nào đang nợ thanh toán
                        </div>

                        <div v-else class="divide-y divide-slate-100">
                            <div v-for="inv in pendingInvoices" :key="inv.id"
                                class="py-3 flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold text-sky-700 font-mono">{{ inv.invoice_number }}</p>
                                    <p class="text-slate-900 font-semibold">{{ inv.customer?.name }} <span
                                            class="text-slate-400 font-normal">({{ inv.customer?.phone }})</span></p>
                                    <p v-if="inv.medicalRecord?.doctor" class="text-[11px] text-slate-500">BS: {{
                                        inv.medicalRecord.doctor.name }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold tabular-nums text-slate-900 block text-sm">{{
                                        formatCurrency(inv.total_amount) }}</span>
                                    <Link :href="route('invoices.show', inv.id)"
                                        class="inline-block mt-1 bg-sky-600 hover:bg-sky-700 text-white font-semibold px-3 py-1 rounded-xl text-xs shadow-2xs">
                                        Thu tiền
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phiếu chi gần đây (1 col) -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <Icon name="document" size="16" class="text-amber-600" />
                                <span>Phiếu chi tiền gần đây</span>
                            </h3>
                            <Link :href="route('expense-vouchers.create')"
                                class="text-sky-600 hover:text-sky-800 font-semibold">
                                + Lập phiếu chi
                            </Link>
                        </div>

                        <div v-if="!recentExpenses?.length" class="py-8 text-center text-slate-400">
                            Chưa có phiếu chi nào
                        </div>

                        <div v-else class="divide-y divide-slate-100">
                            <div v-for="exp in recentExpenses" :key="exp.id"
                                class="py-2.5 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ exp.description }}</p>
                                    <p class="text-slate-400 text-[10px]">{{ formatDate(exp.voucher_date) }} · {{
                                        exp.creator?.name ?? 'Admin' }}</p>
                                </div>
                                <span class="font-bold tabular-nums text-rose-600 shrink-0">
                                    -{{ formatCurrency(exp.amount) }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </template>

        </div>
</template>