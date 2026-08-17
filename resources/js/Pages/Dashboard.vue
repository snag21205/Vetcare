<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
// Head là component để set <title> của trang (SEO)
import { Head } from '@inertiajs/vue3'
import { useAuth } from '@/Composables/useAuth.js'

// defineProps: khai báo props nhận từ Inertia Controller
// Mỗi prop tương ứng với key trong array của Inertia::render()
const props = defineProps({
    // Dữ liệu thống kê cho từng role — Controller sẽ truyền xuống
    stats: Object,
    todayAppointments: Array,
    pendingInvoices: Array,
    lowStockMedicines: Array,
})

const { isAdmin, isReceptionist, isVet, isAccountant, user } = useAuth()
</script>

<template>

    <Head title="Dashboard" />

    <!-- Dùng AuthenticatedLayout làm khung -->
    <AuthenticatedLayout>

        <!-- Tiêu đề chào -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Xin chào, {{ user?.name }} 👋
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ new Date().toLocaleDateString('vi-VN', {
                    weekday: 'long', year: 'numeric', month: 'long', day:
                'numeric' }) }}
            </p>
        </div>

        <!-- ====== DASHBOARD CHO ADMIN ====== -->
        <template v-if="isAdmin">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <StatCard title="Tổng khách hàng" :value="stats?.totalCustomers" icon="👥" color="blue" />
                <StatCard title="Lịch hẹn hôm nay" :value="stats?.todayAppointments" icon="📅" color="green" />
                <StatCard title="Doanh thu tháng" :value="stats?.monthRevenue" icon="💰" color="yellow"
                    format="currency" />
                <StatCard title="Thuốc sắp hết" :value="stats?.lowStockCount" icon="⚠️" color="red" />
            </div>

            <!-- Cảnh báo thuốc sắp hết -->
            <div v-if="lowStockMedicines?.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="font-semibold text-red-800 mb-2">⚠️ Thuốc sắp hết kho</h3>
                <ul class="space-y-1">
                    <li v-for="med in lowStockMedicines" :key="med.id" class="text-sm text-red-700">
                        {{ med.name }} — còn <strong>{{ med.stock_quantity }} {{ med.unit }}</strong>
                        (ngưỡng: {{ med.low_stock_threshold }})
                    </li>
                </ul>
            </div>
        </template>

        <!-- ====== DASHBOARD CHO LỄ TÂN ====== -->
        <template v-if="isReceptionist">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <StatCard title="Lịch hẹn hôm nay" :value="stats?.todayAppointments" icon="📅" color="blue" />
                <StatCard title="Đã check-in" :value="stats?.checkedIn" icon="✅" color="green" />
                <StatCard title="Chờ thanh toán" :value="stats?.pendingPayments" icon="💳" color="yellow" />
            </div>

            <!-- Danh sách lịch hẹn hôm nay -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-4">📅 Lịch hẹn hôm nay</h3>
                <div v-if="!todayAppointments?.length" class="text-gray-400 text-center py-8">
                    Không có lịch hẹn nào hôm nay
                </div>
                <div v-for="apt in todayAppointments" :key="apt.id"
                    class="flex items-center justify-between py-3 border-b last:border-0">
                    <div>
                        <p class="font-medium">{{ apt.pet?.name }} ({{ apt.pet?.customer?.name }})</p>
                        <p class="text-sm text-gray-500">{{ apt.reason }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium">{{ apt.scheduled_at }}</p>
                        <!-- Badge trạng thái với màu khác nhau -->
                        <span :class="statusBadgeClass(apt.status)" class="text-xs px-2 py-1 rounded-full">
                            {{ apt.status }}
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <!-- ====== DASHBOARD CHO BÁC SĨ ====== -->
        <template v-if="isVet">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <StatCard title="Bệnh nhân hôm nay" :value="stats?.myTodayPatients" icon="🐾" color="blue" />
                <StatCard title="Đang chờ khám" :value="stats?.waitingPatients" icon="⏳" color="yellow" />
                <StatCard title="Đã khám xong" :value="stats?.completedToday" icon="✅" color="green" />
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-4">🐾 Bệnh nhân chờ khám</h3>
                <div v-if="!todayAppointments?.length" class="text-gray-400 text-center py-8">
                    Không có bệnh nhân nào đang chờ
                </div>
                <div v-for="apt in todayAppointments" :key="apt.id"
                    class="flex items-center justify-between py-3 border-b last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-xl">
                            {{ apt.pet?.species === 'chó' ? '🐕' : '🐈' }}
                        </div>
                        <div>
                            <p class="font-medium">{{ apt.pet?.name }}</p>
                            <p class="text-sm text-gray-500">{{ apt.pet?.breed }} · {{ apt.reason }}</p>
                        </div>
                    </div>
                    <a :href="`/appointments/${apt.id}/examine`"
                        class="bg-blue-500 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-600">
                        Bắt đầu khám
                    </a>
                </div>
            </div>
        </template>

        <!-- ====== DASHBOARD CHO KẾ TOÁN ====== -->
        <template v-if="isAccountant">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <StatCard title="Doanh thu hôm nay" :value="stats?.todayRevenue" icon="💰" color="green"
                    format="currency" />
                <StatCard title="Hóa đơn chưa TT" :value="stats?.pendingCount" icon="📋" color="yellow" />
                <StatCard title="Doanh thu tháng" :value="stats?.monthRevenue" icon="📈" color="blue"
                    format="currency" />
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-4">📋 Hóa đơn chờ thanh toán</h3>
                <div v-for="inv in pendingInvoices" :key="inv.id"
                    class="flex justify-between items-center py-3 border-b last:border-0">
                    <div>
                        <p class="font-medium">{{ inv.invoice_number }}</p>
                        <p class="text-sm text-gray-500">{{ inv.customer?.name }}</p>
                    </div>
                    <p class="font-bold text-gray-800">
                        {{ formatCurrency(inv.total_amount) }}
                    </p>
                </div>
            </div>
        </template>

    </AuthenticatedLayout>
</template>

<script>
// Helper functions
function statusBadgeClass(status) {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        checked_in: 'bg-green-100 text-green-800',
        completed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
        no_show: 'bg-red-100 text-red-800',
    }
    return classes[status] ?? 'bg-gray-100 text-gray-600'
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount)
}
</script>