<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link } from '@inertiajs/vue3'
import { useAuth } from '@/Composables/useAuth.js'

const props = defineProps({
    stats: Object,
    todayAppointments: Array,
    pendingInvoices: Array,
    lowStockMedicines: Array,
    expiringMedicines: Array,
})

const { isAdmin, isReceptionist, isVet, isAccountant, user } = useAuth()

const statusBadgeClass = (status) => {
    const classes = {
        pending:    'bg-amber-50 text-amber-700 border border-amber-200',
        confirmed:  'bg-sky-50 text-sky-700 border border-sky-200',
        checked_in: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        completed:  'bg-zinc-100 text-zinc-700 border border-zinc-200',
        cancelled:  'bg-rose-50 text-rose-700 border border-rose-200',
        no_show:    'bg-orange-50 text-orange-700 border border-orange-200',
    }
    return classes[status] ?? 'bg-gray-100 text-gray-600'
}

const statusLabel = (status) => {
    const labels = {
        pending:    'Chờ xác nhận',
        confirmed:  'Đã xác nhận',
        checked_in: 'Đã check-in',
        completed:  'Hoàn thành',
        cancelled:  'Đã hủy',
        no_show:    'Không đến',
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
</script>

<template>
    <Head title="Tổng quan" />

    <AuthenticatedLayout>
        <!-- Tiêu đề chào -->
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">
                Xin chào, {{ user?.name }}
            </h1>
            <p class="text-xs text-gray-500 font-mono mt-0.5">
                {{ new Date().toLocaleDateString('vi-VN', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                }) }}
            </p>
        </div>

        <!-- ====== DASHBOARD CHO ADMIN ====== -->
        <template v-if="isAdmin">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <StatCard title="Tổng khách hàng" :value="stats?.totalCustomers" icon="users" color="indigo" />
                <StatCard title="Lịch hẹn hôm nay" :value="stats?.todayAppointments" icon="calendar" color="blue" />
                <StatCard title="Doanh thu tháng" :value="stats?.monthRevenue" icon="chart" color="green" format="currency" />
                <StatCard title="Thuốc sắp hết" :value="stats?.lowStockCount" icon="pill" color="red" />
            </div>

            <!-- Cảnh báo tồn kho & Sắp hết hạn -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Cảnh báo thuốc sắp hết kho -->
                <div v-if="lowStockMedicines?.length > 0" class="bg-rose-50/75 border border-rose-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2 text-rose-800 font-semibold text-xs uppercase font-mono">
                            <Icon name="alert" size="15" />
                            <span>Thuốc sắp hết kho ({{ lowStockMedicines.length }})</span>
                        </div>
                        <Link :href="route('medicines.index', { filter: 'low_stock' })" class="text-[11px] text-rose-600 hover:underline font-medium">
                            Nhập kho →
                        </Link>
                    </div>
                    <ul class="space-y-1.5 text-xs text-rose-700 divide-y divide-rose-100">
                        <li v-for="med in lowStockMedicines" :key="med.id" class="pt-1.5 first:pt-0 flex items-center justify-between">
                            <span>{{ med.name }}</span>
                            <span class="font-mono font-medium">Còn <strong>{{ med.stock_quantity }} {{ med.unit }}</strong> (ngưỡng: {{ med.low_stock_threshold }})</span>
                        </li>
                    </ul>
                </div>

                <!-- Cảnh báo thuốc sắp hết hạn -->
                <div v-if="expiringMedicines?.length > 0" class="bg-amber-50/75 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2 text-amber-800 font-semibold text-xs uppercase font-mono">
                            <Icon name="clock" size="15" />
                            <span>Thuốc sắp hết hạn ≤ 30 ngày ({{ expiringMedicines.length }})</span>
                        </div>
                        <Link :href="route('medicines.index', { filter: 'expiring' })" class="text-[11px] text-amber-600 hover:underline font-medium">
                            Xem tất cả →
                        </Link>
                    </div>
                    <ul class="space-y-1.5 text-xs text-amber-700 divide-y divide-amber-100">
                        <li v-for="med in expiringMedicines" :key="med.id" class="pt-1.5 first:pt-0 flex items-center justify-between">
                            <span>{{ med.name }}</span>
                            <span class="font-mono font-medium">HSD: <strong>{{ formatDate(med.expiry_date) }}</strong></span>
                        </li>
                    </ul>
                </div>
            </div>
        </template>

        <!-- ====== DASHBOARD CHO LỄ TÂN ====== -->
        <template v-if="isReceptionist">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <StatCard title="Lịch hẹn hôm nay" :value="stats?.todayAppointments" icon="calendar" color="indigo" />
                <StatCard title="Đã check-in" :value="stats?.checkedIn" icon="check-circle" color="green" />
                <StatCard title="Chờ thanh toán" :value="stats?.pendingPayments" icon="invoice" color="yellow" />
            </div>

            <!-- Danh sách lịch hẹn hôm nay -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                    <h3 class="text-xs font-bold text-gray-900 uppercase font-mono flex items-center gap-1.5">
                        <Icon name="calendar" size="14" class="text-indigo-600" />
                        <span>Lịch tiếp đón hôm nay</span>
                    </h3>
                    <Link :href="route('appointments.index')" class="text-xs text-indigo-600 hover:underline font-medium">
                        Xem tất cả →
                    </Link>
                </div>

                <div v-if="!todayAppointments?.length" class="text-gray-400 text-center py-8 text-xs">
                    Không có lịch hẹn nào hôm nay
                </div>

                <div v-else class="divide-y divide-gray-100 text-xs">
                    <div v-for="apt in todayAppointments" :key="apt.id"
                        class="flex items-center justify-between py-2.5">
                        <div>
                            <p class="font-semibold text-gray-900">{{ apt.pet?.name }} <span class="text-gray-500 font-normal">({{ apt.pet?.customer?.name }})</span></p>
                            <p class="text-gray-500 text-[11px] mt-0.5">{{ apt.reason }}</p>
                        </div>
                        <div class="text-right">
                            <span :class="statusBadgeClass(apt.status)" class="text-[11px] px-2 py-0.5 rounded-full font-mono font-medium">
                                {{ statusLabel(apt.status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ====== DASHBOARD CHO BÁC SĨ ====== -->
        <template v-if="isVet">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <StatCard title="Bệnh nhân hôm nay" :value="stats?.myTodayPatients" icon="pet" color="indigo" />
                <StatCard title="Đang chờ khám" :value="stats?.waitingPatients" icon="clock" color="yellow" />
                <StatCard title="Đã khám xong" :value="stats?.completedToday" icon="check-circle" color="green" />
            </div>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                    <h3 class="text-xs font-bold text-gray-900 uppercase font-mono flex items-center gap-1.5">
                        <Icon name="stethoscope" size="14" class="text-indigo-600" />
                        <span>Bệnh nhân chờ khám</span>
                    </h3>
                    <Link :href="route('appointments.index')" class="text-xs text-indigo-600 hover:underline font-medium">
                        Lịch hẹn hôm nay →
                    </Link>
                </div>

                <div v-if="!todayAppointments?.length" class="text-gray-400 text-center py-8 text-xs">
                    Không có bệnh nhân nào đang chờ
                </div>

                <div v-else class="divide-y divide-gray-100 text-xs">
                    <div v-for="apt in todayAppointments" :key="apt.id"
                        class="flex items-center justify-between py-2.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                <Icon name="pet" size="14" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ apt.pet?.name }}</p>
                                <p class="text-gray-500 text-[11px]">{{ apt.pet?.breed ?? apt.pet?.species }} · {{ apt.reason }}</p>
                            </div>
                        </div>
                        <Link v-if="apt.status === 'checked_in'"
                            :href="route('medical-records.create', { appointment_id: apt.id })"
                            class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold px-3 py-1.5 rounded-md shadow-xs transition-colors">
                            <Icon name="stethoscope" size="12" />
                            <span>Vào khám</span>
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <!-- ====== DASHBOARD CHO KẾ TOÁN ====== -->
        <template v-if="isAccountant">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <StatCard title="Doanh thu hôm nay" :value="stats?.todayRevenue" icon="chart" color="green" format="currency" />
                <StatCard title="Hóa đơn chưa TT" :value="stats?.pendingCount" icon="invoice" color="yellow" />
                <StatCard title="Doanh thu tháng" :value="stats?.monthRevenue" icon="chart" color="indigo" format="currency" />
            </div>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                    <h3 class="text-xs font-bold text-gray-900 uppercase font-mono flex items-center gap-1.5">
                        <Icon name="invoice" size="14" class="text-indigo-600" />
                        <span>Hóa đơn chờ thanh toán</span>
                    </h3>
                    <Link :href="route('invoices.index')" class="text-xs text-indigo-600 hover:underline font-medium">
                        Tất cả hóa đơn →
                    </Link>
                </div>

                <div v-if="!pendingInvoices?.length" class="text-gray-400 text-center py-8 text-xs">
                    Không có hóa đơn nào đang chờ thanh toán
                </div>

                <div v-else class="divide-y divide-gray-100 text-xs">
                    <div v-for="inv in pendingInvoices" :key="inv.id"
                        class="flex justify-between items-center py-2.5">
                        <div>
                            <p class="font-bold text-indigo-600 font-mono">{{ inv.invoice_number }}</p>
                            <p class="text-gray-500 text-[11px] mt-0.5">{{ inv.customer?.name }}</p>
                        </div>
                        <p class="font-bold font-mono text-gray-900">
                            {{ formatCurrency(inv.total_amount) }}
                        </p>
                    </div>
                </div>
            </div>
        </template>

    </AuthenticatedLayout>
</template>