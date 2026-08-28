<script setup>
import { useAuth } from '@/Composables/useAuth'
import Icon from '@/Components/Icon.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'

const props = defineProps({
    mobileOpen: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

const { isAdmin, isVet, isReceptionist, isAccountant } = useAuth()
const page = usePage()

const navRef = ref(null)

// Sliding active indicator state
const indicator = ref({
    top: 0,
    height: 38,
    visible: false,
    expanded: true,
})

let timer1 = null
let timer2 = null
let isAnimating = false  // cờ ngăn syncIndicator chạy đè lên animation đang chạy

function handleScroll(e) {
    if (e.target) {
        sessionStorage.setItem('sidebar_scroll_pos', e.target.scrollTop)
    }
}

function isActive(path) {
    if (path === '/dashboard') {
        return page.url === '/dashboard'
    }
    return page.url.startsWith(path)
}

/**
 * Di chuyển indicator tới element đã cho.
 * - immediate=true: nhảy thẳng không animation (lần đầu mount)
 * - immediate=false: chạy animation 3 giai đoạn
 */
function moveTo(el, immediate = false) {
    if (!el || !navRef.value) return

    const top = el.offsetTop
    const height = el.offsetHeight

    clearTimeout(timer1)
    clearTimeout(timer2)
    isAnimating = false

    if (immediate || !indicator.value.visible) {
        // Nhảy thẳng, không animation
        indicator.value = { top, height, visible: true, expanded: true }
        return
    }

    if (indicator.value.top === top && indicator.value.expanded) {
        // Đã đúng vị trí và đang hiển thị rồi, không cần làm gì
        return
    }

    // ─── Bắt đầu animation 3 giai đoạn ──────────────────────────
    isAnimating = true

    // Giai đoạn 1 (0→120ms): Kéo gọn phần nền xanh nhạt vào dải xanh đậm
    indicator.value.expanded = false

    // Giai đoạn 2 (120→360ms): Dải xanh đậm trượt dọc đến vị trí mới
    timer1 = setTimeout(() => {
        indicator.value.top = top
        indicator.value.height = height

        // Giai đoạn 3 (360→580ms): Nền xanh nhạt bung rộng ra từ dải xanh đậm
        timer2 = setTimeout(() => {
            indicator.value.expanded = true
            isAnimating = false
        }, 220)
    }, 120)
}

function syncIndicator() {
    // Nếu đang chạy animation do click, bỏ qua để tránh chạy đè
    if (isAnimating) return

    nextTick(() => {
        if (!navRef.value) return
        const active = navRef.value.querySelector('.sidebar-link-active')
        if (active) {
            moveTo(active, false)
        } else {
            indicator.value.visible = false
        }
    })
}

// Khi nhấn một liên kết: animation bắt đầu ngay từ nút được click
function handleLinkClick(e) {
    emit('close')
    const linkEl = e.currentTarget
    if (linkEl) {
        moveTo(linkEl, false)
    }
}

onMounted(() => {
    nextTick(() => {
        const savedPos = sessionStorage.getItem('sidebar_scroll_pos')
        if (savedPos && navRef.value) {
            navRef.value.scrollTop = Number(savedPos)
        }
        // Lần đầu: nhảy thẳng, không animate
        const active = navRef.value?.querySelector('.sidebar-link-active')
        if (active) moveTo(active, true)
    })
})

onUnmounted(() => {
    clearTimeout(timer1)
    clearTimeout(timer2)
})

// Khi Inertia chuyển trang xong, URL thay đổi.
// Với persistent layout, Sidebar KHÔNG bị remount.
// watch này chỉ dùng để đồng bộ nếu animation bị lỡ (vd: back/forward browser)
watch(() => page.url, () => {
    syncIndicator()
})
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

        <!-- Fixed Header / Brand -->
        <div class="h-16 shrink-0 px-5 flex items-center justify-between border-b border-slate-100 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-600 flex items-center justify-center text-white shadow-xs">
                    <Icon name="pet" size="20" />
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 tracking-tight leading-tight">VetCare</h1>
                    <p class="text-[10px] text-sky-600 font-bold uppercase tracking-wider">Clinic OS</p>
                </div>
            </div>
            <button @click="emit('close')"
                class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                <Icon name="x" size="18" />
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav ref="navRef" @scroll="handleScroll" class="relative flex-1 px-3 py-4 space-y-5 overflow-y-auto text-xs custom-scrollbar">

            <!-- Dynamic Sliding Active Indicator -->
            <div
                v-show="indicator.visible"
                class="absolute pointer-events-none z-0 flex items-stretch"
                :style="{
                    top: `${indicator.top}px`,
                    height: `${indicator.height}px`,
                    width: 'calc(100% - 1.5rem)',
                    left: '0.75rem',
                    transition: 'top 240ms cubic-bezier(0.34, 1.2, 0.64, 1), height 240ms cubic-bezier(0.34, 1.2, 0.64, 1)'
                }">
                <!-- Dải xanh đậm bên trái (Trượt dọc) -->
                <div class="w-1.5 bg-sky-600 rounded-r-md shrink-0 shadow-xs"></div>
                <!-- Nền xanh nhạt (Kéo vào / Mở rộng ra) -->
                <div
                    class="flex-1 bg-sky-50 border border-l-0 border-sky-200/90 rounded-r-xl origin-left"
                    :style="{
                        transform: indicator.expanded ? 'scaleX(1)' : 'scaleX(0)',
                        opacity: indicator.expanded ? '1' : '0',
                        transition: indicator.expanded
                            ? 'transform 220ms cubic-bezier(0.16, 1, 0.3, 1), opacity 220ms ease'
                            : 'transform 100ms cubic-bezier(0.4, 0, 1, 1), opacity 100ms ease'
                    }">
                </div>
            </div>

            <!-- Core -->
            <div class="space-y-1">
                <Link :href="route('dashboard')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/dashboard') }">
                    <Icon name="dashboard" size="16" />
                    <span>Tổng quan</span>
                </Link>
            </div>

            <!-- Tiếp đón -->
            <div v-if="isReceptionist || isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tiếp đón</div>
                <Link :href="route('customers.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/customers') }">
                    <Icon name="users" size="16" />
                    <span>Khách hàng</span>
                </Link>
                <Link :href="route('pets.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/pets') }">
                    <Icon name="pet" size="16" />
                    <span>Vật nuôi</span>
                </Link>
                <Link :href="route('appointments.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/appointments') }">
                    <Icon name="calendar" size="16" />
                    <span>Lịch hẹn khám</span>
                </Link>
            </div>

            <!-- Khám chữa bệnh -->
            <div v-if="isVet || isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Khám chữa bệnh</div>
                <Link :href="route('medical-records.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/medical-records') }">
                    <Icon name="stethoscope" size="16" />
                    <span>Hồ sơ khám bệnh</span>
                </Link>
            </div>

            <!-- Tài chính -->
            <div v-if="isAccountant || isReceptionist || isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tài chính & Thu ngân</div>
                <Link :href="route('invoices.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/invoices') }">
                    <Icon name="invoice" size="16" />
                    <span>Hóa đơn thu tiền</span>
                </Link>
                <Link v-if="isAccountant || isAdmin" :href="route('expense-vouchers.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/expense-vouchers') }">
                    <Icon name="document" size="16" />
                    <span>Phiếu chi sổ quỹ</span>
                </Link>
            </div>

            <!-- Báo cáo -->
            <div v-if="isAccountant || isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Báo cáo & Thống kê</div>
                <Link :href="route('reports.revenue')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/reports/revenue') }">
                    <Icon name="chart" size="16" />
                    <span>Báo cáo doanh thu</span>
                </Link>
                <Link :href="route('reports.diseases')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/reports/diseases') }">
                    <Icon name="stethoscope" size="16" />
                    <span>Bệnh phổ biến</span>
                </Link>
                <Link :href="route('reports.loyal-customers')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/reports/loyal-customers') }">
                    <Icon name="users" size="16" />
                    <span>Khách thân thiết</span>
                </Link>
            </div>

            <!-- Quản trị hệ thống -->
            <div v-if="isAdmin" class="space-y-1">
                <div class="px-3 pt-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Quản trị hệ thống</div>
                <Link :href="route('medicines.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/medicines') }">
                    <Icon name="medicine" size="16" />
                    <span>Kho thuốc & Vật tư</span>
                </Link>
                <Link :href="route('services.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/services') }">
                    <Icon name="service" size="16" />
                    <span>Bảng giá dịch vụ</span>
                </Link>
                <Link :href="route('users.index')"
                    @click="handleLinkClick"
                    class="sidebar-link"
                    :class="{ 'sidebar-link-active': isActive('/users') }">
                    <Icon name="user" size="16" />
                    <span>Tài khoản nhân viên</span>
                </Link>
            </div>

        </nav>

        <!-- Compact Bottom Status -->
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
    position: relative;
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.875rem;
    border-radius: 0.75rem;
    color: #475569;
    font-weight: 500;
    transition: color 0.15s ease;
}
.sidebar-link:hover { color: #0284c7; }
.sidebar-link-active {
    color: #0369a1 !important;
    font-weight: 700 !important;
}
.sidebar-link-active :deep(svg) { color: #0284c7 !important; }
</style>