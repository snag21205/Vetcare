<script setup>
import { useAuth } from '@/Composables/useAuth'
import Icon from '@/Components/Icon.vue'
import { Link } from '@inertiajs/vue3'

const { isAdmin, isVet, isReceptionist, isAccountant, role } = useAuth()
</script>

<template>
    <aside class="w-64 min-h-screen bg-[#18181B] text-zinc-300 flex flex-col border-r border-zinc-800 selection:bg-indigo-500 selection:text-white">

        <!-- Header / Brand -->
        <div class="p-5 border-b border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-sm shadow-indigo-600/30">
                    <Icon name="pet" size="18" />
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-tight uppercase">VetCare</h1>
                    <p class="text-[10px] text-zinc-400 font-mono tracking-wider">CLINIC OS</p>
                </div>
            </div>
            <span class="text-[10px] font-mono uppercase bg-zinc-800 text-zinc-400 border border-zinc-700 px-2 py-0.5 rounded">
                {{ role }}
            </span>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-3 space-y-6 overflow-y-auto">

            <!-- Core -->
            <div class="space-y-1">
                <Link :href="route('dashboard')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url === '/dashboard' }">
                    <Icon name="dashboard" size="17" />
                    <span>Tổng quan</span>
                </Link>
            </div>

            <!-- Receptionist & Admin -->
            <div v-if="isReceptionist || isAdmin" class="space-y-1">
                <div class="px-3 text-[11px] font-semibold font-mono text-zinc-400 uppercase tracking-wider">
                    Tiếp đón
                </div>

                <Link :href="route('customers.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/customers') }">
                    <Icon name="users" size="17" />
                    <span>Khách hàng</span>
                </Link>

                <Link :href="route('pets.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/pets') }">
                    <Icon name="pet" size="17" />
                    <span>Vật nuôi</span>
                </Link>

                <Link :href="route('appointments.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/appointments') }">
                    <Icon name="calendar" size="17" />
                    <span>Lịch hẹn</span>
                </Link>
            </div>

            <!-- Vet & Admin -->
            <div v-if="isVet || isAdmin" class="space-y-1">
                <div class="px-3 text-[11px] font-semibold font-mono text-zinc-400 uppercase tracking-wider">
                    Khám chữa bệnh
                </div>

                <Link :href="route('medical-records.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/medical-records') }">
                    <Icon name="stethoscope" size="17" />
                    <span>Hồ sơ khám bệnh</span>
                </Link>
            </div>

            <!-- Finance & Invoices -->
            <div v-if="isAccountant || isReceptionist || isAdmin" class="space-y-1">
                <div class="px-3 text-[11px] font-semibold font-mono text-zinc-400 uppercase tracking-wider">
                    Tài chính & Thu ngân
                </div>

                <Link :href="route('invoices.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/invoices') }">
                    <Icon name="invoice" size="17" />
                    <span>Hóa đơn</span>
                </Link>

                <Link v-if="isAccountant || isAdmin" :href="route('expense-vouchers.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/expense-vouchers') }">
                    <Icon name="document" size="17" />
                    <span>Phiếu chi</span>
                </Link>

                <Link v-if="isAccountant || isAdmin" :href="route('reports.revenue')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/reports') }">
                    <Icon name="chart" size="17" />
                    <span>Báo cáo doanh thu</span>
                </Link>
            </div>

            <!-- Admin Settings -->
            <div v-if="isAdmin" class="space-y-1">
                <div class="px-3 text-[11px] font-semibold font-mono text-zinc-400 uppercase tracking-wider">
                    Quản trị hệ thống
                </div>

                <Link :href="route('medicines.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/medicines') }">
                    <Icon name="medicine" size="17" />
                    <span>Kho thuốc</span>
                </Link>

                <Link :href="route('services.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/services') }">
                    <Icon name="service" size="17" />
                    <span>Bảng giá dịch vụ</span>
                </Link>

                <Link :href="route('users.index')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/users') }">
                    <Icon name="user" size="17" />
                    <span>Tài khoản nhân viên</span>
                </Link>
            </div>

        </nav>

        <!-- Footer: User info & logout -->
        <div class="p-3 border-t border-zinc-800 bg-zinc-900/50">
            <div class="flex items-center justify-between px-2 py-1.5">
                <div class="truncate">
                    <p class="text-xs font-semibold text-white truncate">{{ $page.props.auth.user?.name }}</p>
                    <p class="text-[10px] text-zinc-400 truncate">{{ $page.props.auth.user?.email }}</p>
                </div>
                <Link :href="route('logout')" method="post" as="button"
                    class="p-1.5 text-zinc-400 hover:text-red-400 hover:bg-zinc-800 rounded transition-colors"
                    title="Đăng xuất">
                    <Icon name="logout" size="16" />
                </Link>
            </div>
        </div>

    </aside>
</template>

<style scoped>
.sidebar-link {
    @apply flex items-center gap-2.5 px-3 py-2 rounded-md text-xs text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800/80 transition-colors;
}
.sidebar-link-active {
    @apply bg-indigo-600/15 text-indigo-400 font-semibold border-l-2 border-indigo-500 rounded-l-none pl-2.5;
}
</style>