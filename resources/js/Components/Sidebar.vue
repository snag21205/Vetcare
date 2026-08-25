<script setup>
import { useAuth } from '@/Composables/useAuth'
import Icon from '@/Components/Icon.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    mobileOpen: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

const { isAdmin, isVet, isReceptionist, isAccountant } = useAuth()
</script>

<template>
    <!-- Mobile Backdrop Overlay -->
    <div v-if="mobileOpen"
        @click="emit('close')"
        class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs z-40 lg:hidden transition-opacity">
    </div>

    <!-- Sidebar Container -->
    <aside
        :class="[
            'fixed lg:static top-0 left-0 bottom-0 z-50 lg:z-40 w-64 h-screen shrink-0 flex flex-col bg-white border-r border-slate-200 selection:bg-sky-500 selection:text-white select-none transition-transform duration-200 ease-in-out',
            mobileOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'
        ]">

        <!-- Fixed Header / Brand (Không bị cuộn theo menu) -->
        <div class="h-16 shrink-0 px-5 flex items-center justify-between border-b border-slate-100 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-cyan-500 flex items-center justify-center text-white shadow-sm shadow-sky-500/20">
                    <Icon name="pet" size="19" />
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-tight">VetCare</h1>
                    <p class="text-[10px] text-sky-600 font-semibold uppercase tracking-wider">Clinic OS</p>
                </div>
            </div>

            <!-- Mobile Close Button -->
            <button @click="emit('close')"
                class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                <Icon name="x" size="18" />
            </button>
        </div>

        <!-- Navigation Menu (Chỉ vùng này cuộn độc lập) -->
        <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto text-xs custom-scrollbar">

            <!-- Core -->
            <div class="space-y-1">
                <Link :href="route('dashboard')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url === '/dashboard' }">
                    <Icon name="dashboard" size="16" />
                    <span>Tổng quan</span>
                </Link>
            </div>

            <!-- Tiếp đón: Receptionist & Admin -->
            <div v-if="isReceptionist || isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    Tiếp đón
                </div>

                <Link :href="route('customers.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/customers') }">
                    <Icon name="users" size="16" />
                    <span>Khách hàng</span>
                </Link>

                <Link :href="route('pets.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/pets') }">
                    <Icon name="pet" size="16" />
                    <span>Vật nuôi</span>
                </Link>

                <Link :href="route('appointments.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/appointments') }">
                    <Icon name="calendar" size="16" />
                    <span>Lịch hẹn khám</span>
                </Link>
            </div>

            <!-- Khám chữa bệnh: Vet & Admin -->
            <div v-if="isVet || isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    Khám chữa bệnh
                </div>

                <Link :href="route('medical-records.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/medical-records') }">
                    <Icon name="stethoscope" size="16" />
                    <span>Hồ sơ khám bệnh</span>
                </Link>
            </div>

            <!-- Tài chính & Thu ngân -->
            <div v-if="isAccountant || isReceptionist || isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    Tài chính & Thu ngân
                </div>

                <Link :href="route('invoices.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/invoices') }">
                    <Icon name="invoice" size="16" />
                    <span>Hóa đơn thu tiền</span>
                </Link>

                <Link v-if="isAccountant || isAdmin" :href="route('expense-vouchers.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/expense-vouchers') }">
                    <Icon name="document" size="16" />
                    <span>Phiếu chi sổ quỹ</span>
                </Link>
            </div>

            <!-- Báo cáo & Thống kê -->
            <div v-if="isAccountant || isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    Báo cáo & Thống kê
                </div>

                <Link :href="route('reports.revenue')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/reports/revenue') }">
                    <Icon name="chart" size="16" />
                    <span>Báo cáo doanh thu</span>
                </Link>

                <Link :href="route('reports.diseases')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/reports/diseases') }">
                    <Icon name="stethoscope" size="16" />
                    <span>Bệnh phổ biến</span>
                </Link>

                <Link :href="route('reports.loyal-customers')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/reports/loyal-customers') }">
                    <Icon name="users" size="16" />
                    <span>Khách thân thiết</span>
                </Link>
            </div>

            <!-- Quản trị hệ thống (Admin) -->
            <div v-if="isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    Quản trị hệ thống
                </div>

                <Link :href="route('medicines.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/medicines') }">
                    <Icon name="medicine" size="16" />
                    <span>Kho thuốc & Vật tư</span>
                </Link>

                <Link :href="route('services.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/services') }">
                    <Icon name="service" size="16" />
                    <span>Bảng giá dịch vụ</span>
                </Link>

                <Link :href="route('users.index')"
                    @click="emit('close')"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': $page.url.startsWith('/users') }">
                    <Icon name="user" size="16" />
                    <span>Tài khoản nhân viên</span>
                </Link>
            </div>

        </nav>

        <!-- Compact Bottom Clinic Status (Fixed) -->
        <div class="p-3 border-t border-slate-100 bg-slate-50/70 shrink-0">
            <div class="flex items-center gap-2 px-2 py-1 text-[11px] text-slate-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 animate-pulse"></span>
                <span class="truncate font-semibold text-slate-700">Hệ thống đang hoạt động</span>
            </div>
        </div>

    </aside>
</template>

<style scoped>
.sidebar-link {
    @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:text-sky-700 hover:bg-sky-50/70 transition-colors font-medium;
}
.sidebar-link-active {
    @apply bg-sky-50 text-sky-700 font-bold border-l-4 border-sky-600 rounded-r-lg rounded-l-none pl-2.5 shadow-xs;
}
</style>