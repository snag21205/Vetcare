<script setup>
import { useAuth } from '@/Composables/useAuth';
import { Link } from '@inertiajs/vue3';

const { isAdmin, isVet, isReceptionist, isAccountant, hasRole } = useAuth();
</script>

<template>
    <aside class="w-64 min-h-screen bg-gray-800 text-white flex flex-col">

        <!-- Logo / Tên app -->
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-xl font-bold text-white">🐾 VetCare</h1>
            <!-- Badge hiển thị role hiện tại — hữu ích khi debug -->
            <span class="text-xs text-gray-400 capitalize">{{ role }}</span>
        </div>

        <nav class="flex-1 p-4 space-y-1">

            <!-- Dashboard — MỌI role đều thấy -->
            <Link :href="route('dashboard')"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
                :class="{ 'bg-gray-700': $page.url === '/dashboard' }">
                📊 Dashboard
            </Link>

            <!-- ===== MENU CHO RECEPTIONIST & ADMIN ===== -->
            <!-- v-if="isReceptionist || isAdmin": chỉ render nếu là lễ tân HOẶC admin -->
            <template v-if="isReceptionist || isAdmin">
                <div class="pt-4 pb-1 text-xs text-gray-500 uppercase tracking-wider">
                    Tiếp đón
                </div>

                <Link :href="route('customers.index')" class="sidebar-link">
                    👥 Khách hàng
                </Link>

                <Link :href="route('appointments.index')" class="sidebar-link">
                    📅 Lịch hẹn
                </Link>

                <Link :href="route('invoices.index')" class="sidebar-link">
                    🧾 Hóa đơn
                </Link>
            </template>

            <!-- ===== MENU CHO VET & ADMIN ===== -->
            <template v-if="isVet || isAdmin">
                <div class="pt-4 pb-1 text-xs text-gray-500 uppercase tracking-wider">
                    Khám bệnh
                </div>

                <Link :href="route('appointments.index')" class="sidebar-link">
                    📋 Lịch khám hôm nay
                </Link>

                <Link :href="route('medical-records.index')" class="sidebar-link">
                    🩺 Phiếu khám
                </Link>
            </template>

            <!-- ===== MENU CHO ACCOUNTANT & ADMIN ===== -->
            <template v-if="isAccountant || isAdmin">
                <div class="pt-4 pb-1 text-xs text-gray-500 uppercase tracking-wider">
                    Tài chính
                </div>

                <Link :href="route('invoices.index')" class="sidebar-link">
                    💰 Hóa đơn
                </Link>

                <Link :href="route('expense-vouchers.index')" class="sidebar-link">
                    📑 Phiếu chi
                </Link>

                <Link :href="route('reports.revenue')" class="sidebar-link">
                    📈 Báo cáo
                </Link>
            </template>

            <!-- ===== MENU CHỈ ADMIN ===== -->
            <!-- v-if="isAdmin": chỉ admin mới thấy mục này -->
            <template v-if="isAdmin">
                <div class="pt-4 pb-1 text-xs text-gray-500 uppercase tracking-wider">
                    Quản trị
                </div>

                <Link :href="route('medicines.index')" class="sidebar-link">
                    💊 Quản lý thuốc
                </Link>

                <Link :href="route('services.index')" class="sidebar-link">
                    🔬 Dịch vụ
                </Link>

                <Link :href="route('users.index')" class="sidebar-link">
                    👤 Tài khoản
                </Link>
            </template>

        </nav>

        <!-- Footer: thông tin user + nút logout -->
        <div class="p-4 border-t border-gray-700">
            <div class="text-sm text-gray-300 mb-2">{{ $page.props.auth.user?.name }}</div>
            <Link :href="route('logout')" method="post" as="button" class="text-xs text-red-400 hover:text-red-300">
                Đăng xuất
            </Link>
        </div>

    </aside>
</template>

<style scoped>
/* scoped: CSS chỉ áp dụng trong component này, không ảnh hưởng component khác */
.sidebar-link {
    @apply flex items-center gap-3 px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-colors text-sm;
}
</style>