<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'
import Icon from '@/Components/Icon.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? {})
const role = computed(() => page.props.auth?.role ?? 'receptionist')
const flash = computed(() => page.props.flash ?? {})

// Mobile sidebar drawer state
const mobileSidebarOpen = ref(false)

// Notifications state
const showNotifications = ref(false)
const recentNotifications = ref(page.props.recentNotifications ?? [])
const unreadCount = ref(Number(page.props.unreadNotificationsCount) || 0)
const notifRef = ref(null)
const isShaking = ref(false)
const toastNotification = ref(null)
let toastTimeout = null
let pollInterval = null
let removeRouterListener = null

// Đồng bộ phản ứng khi page.props thay đổi (khi trang /notifications hoặc controller reload)
watch(
    () => page.props.unreadNotificationsCount,
    (val) => {
        if (val !== undefined && val !== null) {
            unreadCount.value = Number(val) || 0
        }
    },
    { immediate: true }
)

watch(
    () => page.props.recentNotifications,
    (val) => {
        if (val) {
            recentNotifications.value = val
        }
    },
    { immediate: true }
)

// User Menu Dropdown state
const showUserMenu = ref(false)
const userMenuRef = ref(null)

function toggleNotifications() {
    showNotifications.value = !showNotifications.value
    showUserMenu.value = false
    if (showNotifications.value) {
        fetchNotifications()
    }
}

function toggleUserMenu() {
    showUserMenu.value = !showUserMenu.value
    showNotifications.value = false
}

function handleClickOutside(e) {
    if (notifRef.value && !notifRef.value.contains(e.target)) {
        showNotifications.value = false
    }
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        showUserMenu.value = false
    }
}

const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

async function fetchNotifications() {
    try {
        const res = await fetch('/api/notifications/recent', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        if (res.ok) {
            const data = await res.json()
            recentNotifications.value = data.notifications
            unreadCount.value = Number(data.unread_count) || 0
        }
    } catch {
        // silent fail
    }
}

async function pollUnreadCount() {
    try {
        const res = await fetch('/api/notifications/recent', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        if (res.ok) {
            const data = await res.json()
            const newCount = Number(data.unread_count) || 0
            const oldCount = Number(unreadCount.value) || 0

            // Nếu có thêm thông báo mới phát sinh
            if (newCount > oldCount) {
                isShaking.value = true
                setTimeout(() => { isShaking.value = false }, 1200)

                // Lấy thông báo mới nhất để làm Toast popup
                if (data.notifications && data.notifications.length > 0) {
                    const latest = data.notifications[0]
                    showToastAlert(latest)
                }
            }

            recentNotifications.value = data.notifications
            unreadCount.value = newCount
        }
    } catch {
        // silent fail
    }
}

function showToastAlert(notif) {
    clearTimeout(toastTimeout)
    toastNotification.value = notif
    toastTimeout = setTimeout(() => {
        toastNotification.value = null
    }, 6000)
}

function closeToast() {
    clearTimeout(toastTimeout)
    toastNotification.value = null
}

function markAsRead(id, redirectUrl) {
    unreadCount.value = Math.max(0, unreadCount.value - 1)
    const target = recentNotifications.value.find(n => n.id === id)
    if (target) target.read_at = new Date().toISOString()
    showNotifications.value = false

    router.patch(route('notifications.read', id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            if (redirectUrl) {
                router.visit(redirectUrl)
            } else {
                fetchNotifications()
            }
        },
        onError: () => {
            fetchNotifications()
        }
    })
}

function markAllAsRead() {
    unreadCount.value = 0
    recentNotifications.value.forEach(n => { n.read_at = new Date().toISOString() })

    router.post(route('notifications.mark-all-read'), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            fetchNotifications()
        },
        onError: () => {
            fetchNotifications()
        }
    })
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    fetchNotifications()

    // Đồng bộ tức thì với router Inertia mỗi khi có action hoàn tất
    removeRouterListener = router.on('finish', () => {
        if (page.props.unreadNotificationsCount !== undefined && page.props.unreadNotificationsCount !== null) {
            unreadCount.value = Number(page.props.unreadNotificationsCount) || 0
        }
        if (page.props.recentNotifications) {
            recentNotifications.value = page.props.recentNotifications
        }
    })

    // Polling nhẹ nhàng mỗi 20 giây
    pollInterval = setInterval(pollUnreadCount, 20000)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    if (removeRouterListener) removeRouterListener()
    if (pollInterval) clearInterval(pollInterval)
    if (toastTimeout) clearTimeout(toastTimeout)
})

const getNotifMeta = (type) => {
    switch (type) {
        case 'new_appointment':
            return {
                icon: 'calendar',
                bg: 'bg-sky-50 text-sky-600 border-sky-200',
                badge: 'bg-sky-100 text-sky-700',
                label: 'Lịch hẹn mới',
            }
        case 'appointment_checked_in':
            return {
                icon: 'check',
                bg: 'bg-emerald-50 text-emerald-600 border-emerald-200',
                badge: 'bg-emerald-100 text-emerald-700',
                label: 'Check-in',
            }
        case 'invoice_created':
            return {
                icon: 'credit-card',
                bg: 'bg-amber-50 text-amber-600 border-amber-200',
                badge: 'bg-amber-100 text-amber-700',
                label: 'Hóa đơn mới',
            }
        case 'invoice_paid':
            return {
                icon: 'check',
                bg: 'bg-teal-50 text-teal-600 border-teal-200',
                badge: 'bg-teal-100 text-teal-700',
                label: 'Đã thanh toán',
            }
        case 'low_stock':
            return {
                icon: 'alert',
                bg: 'bg-rose-50 text-rose-600 border-rose-200',
                badge: 'bg-rose-100 text-rose-700',
                label: 'Tồn kho thấp',
            }
        case 'expiring_medicine':
            return {
                icon: 'clock',
                bg: 'bg-orange-50 text-orange-600 border-orange-200',
                badge: 'bg-orange-100 text-orange-700',
                label: 'Sắp hết hạn',
            }
        default:
            return {
                icon: 'bell',
                bg: 'bg-slate-50 text-slate-600 border-slate-200',
                badge: 'bg-slate-100 text-slate-700',
                label: 'Thông báo',
            }
    }
}

const roleBadge = (r) => {
    const map = {
        admin: { label: 'Quản trị viên', class: 'bg-rose-50 text-rose-700 border-rose-200' },
        vet: { label: 'Bác sĩ thú y', class: 'bg-sky-50 text-sky-700 border-sky-200' },
        receptionist: { label: 'Lễ tân tiếp đón', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
        accountant: { label: 'Kế toán / Thu ngân', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    }
    return map[r] ?? { label: r, class: 'bg-slate-100 text-slate-700 border-slate-200' }
}

const timeAgo = (dateStr) => {
    if (!dateStr) return ''
    const diff = Math.floor((new Date() - new Date(dateStr)) / 1000)
    if (diff < 60) return 'Vừa xong'
    if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`
    if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`
    return `${Math.floor(diff / 86400)} ngày trước`
}
</script>

<template>
    <div class="flex h-screen w-full overflow-hidden bg-[#F8FAFC]">

        <!-- Sidebar bên trái -->
        <Sidebar :mobile-open="mobileSidebarOpen" @close="mobileSidebarOpen = false" />

        <!-- Khu vực chính bên phải -->
        <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden w-full relative">

            <!-- Top Header Bar (Cố định ở đỉnh) -->
            <header
                class="h-16 shrink-0 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between z-30 shadow-xs">

                <!-- Left: Hamburger button on mobile + Status & Breadcrumb -->
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <!-- Mobile Hamburger Button -->
                    <button @click="mobileSidebarOpen = true"
                        class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none cursor-pointer"
                        title="Mở menu">
                        <Icon name="pet" size="19" class="text-sky-600" />
                    </button>

                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-full font-semibold text-[11px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="hidden sm:inline">Phòng khám</span> VetCare
                    </span>
                    <span class="text-slate-300 hidden sm:inline">/</span>
                    <span class="text-slate-500 font-medium capitalize hidden md:inline">
                        {{ new Date().toLocaleDateString('vi-VN', {
                            weekday: 'long', day: '2-digit', month: '2-digit',
                            year: 'numeric'
                        }) }}
                    </span>
                </div>

                <!-- Right Actions: Notification Bell + User Profile -->
                <div class="flex items-center gap-3">

                    <!-- Notification Bell Dropdown -->
                    <div class="relative" ref="notifRef">
                        <button @click.stop="toggleNotifications"
                            class="relative p-2.5 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors focus:outline-none cursor-pointer"
                            :class="{ 'animate-bounce': isShaking }"
                            title="Thông báo hệ thống">
                            <Icon name="bell" size="19" :class="{ 'text-sky-600': unreadCount > 0 }" />
                            
                            <!-- Badge số lượng chưa đọc -->
                            <span v-if="unreadCount > 0"
                                class="absolute top-1.5 right-1.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-rose-600 rounded-full ring-2 ring-white shadow-xs">
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Notifications Popover -->
                        <div v-if="showNotifications"
                            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl border border-slate-200 shadow-2xl z-50 text-xs animate-in fade-in zoom-in-95 duration-150 overflow-hidden">
                            <!-- Dropdown Header -->
                            <div
                                class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                                <div class="flex items-center gap-2 font-bold text-slate-900">
                                    <Icon name="bell" size="15" class="text-sky-600" />
                                    <span>Thông báo phòng khám</span>
                                    <span v-if="unreadCount > 0"
                                        class="text-[10px] bg-rose-50 text-rose-700 border border-rose-200 px-1.5 py-0.5 rounded-full font-bold">
                                        {{ unreadCount }} mới
                                    </span>
                                </div>
                                <button v-if="unreadCount > 0" @click="markAllAsRead"
                                    class="text-[11px] text-sky-600 hover:text-sky-800 font-semibold transition-colors cursor-pointer">
                                    Đã đọc tất cả
                                </button>
                            </div>

                            <!-- Notifications List -->
                            <div class="max-h-84 overflow-y-auto divide-y divide-slate-100">
                                <div v-for="n in recentNotifications" :key="n.id" @click="markAsRead(n.id, n.data?.url)"
                                    class="p-3 hover:bg-slate-50/90 cursor-pointer transition-colors flex gap-3 items-start"
                                    :class="{ 'bg-sky-50/40': !n.read_at }">
                                    
                                    <!-- Icon theo loại -->
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 border"
                                        :class="getNotifMeta(n.data?.type).bg">
                                        <Icon :name="getNotifMeta(n.data?.type).icon" size="14" />
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1.5">
                                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-md uppercase tracking-wider"
                                                :class="getNotifMeta(n.data?.type).badge">
                                                {{ getNotifMeta(n.data?.type).label }}
                                            </span>
                                            <span v-if="!n.read_at"
                                                class="w-2 h-2 rounded-full bg-sky-600 shrink-0"></span>
                                        </div>

                                        <p class="font-bold text-slate-900 text-xs mt-1 truncate">
                                            {{ n.data?.title ?? 'Thông báo' }}
                                        </p>
                                        <p class="text-[11px] text-slate-600 line-clamp-2 mt-0.5 leading-snug">
                                            {{ n.data?.message }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 font-medium mt-1 block">
                                            {{ timeAgo(n.created_at) }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="recentNotifications.length === 0" class="p-8 text-center text-slate-400">
                                    <Icon name="bell" size="26" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Bạn chưa có thông báo nào</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Các thông báo về lịch hẹn, ca khám và thuốc sẽ hiển thị ở đây.</p>
                                </div>
                            </div>

                            <!-- Dropdown Footer -->
                            <div class="p-2.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs px-3.5">
                                <Link :href="route('notifications.index')" @click="showNotifications = false"
                                    class="text-[11px] text-sky-600 hover:text-sky-800 font-bold transition-colors">
                                    Xem toàn bộ thông báo →
                                </Link>
                                <span class="text-[10px] text-slate-400">Cập nhật tự động</span>
                            </div>
                        </div>
                    </div>

                    <div class="h-5 w-px bg-slate-200"></div>

                    <!-- User Profile Dropdown Menu -->
                    <div class="relative" ref="userMenuRef">
                        <button @click.stop="toggleUserMenu"
                            class="flex items-center gap-2.5 p-1.5 pr-2.5 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none group cursor-pointer">
                            <!-- Avatar -->
                            <div
                                class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                                {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                            </div>
                            <!-- Name & Role -->
                            <div class="hidden sm:block text-left leading-tight">
                                <span
                                    class="font-bold text-xs text-slate-800 block truncate max-w-[130px] group-hover:text-sky-700 transition-colors">
                                    {{ user.name }}
                                </span>
                                <span class="text-[10px] font-semibold text-slate-500 block">
                                    {{ roleBadge(role).label }}
                                </span>
                            </div>
                            <Icon name="chevron-down" size="13"
                                class="text-slate-400 group-hover:text-slate-600 transition-colors" />
                        </button>

                        <!-- User Popover Menu -->
                        <div v-if="showUserMenu"
                            class="absolute right-0 mt-2 w-60 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 text-xs animate-in fade-in zoom-in-95 duration-150 overflow-hidden">
                            <!-- User Details Header -->
                            <div class="p-3.5 border-b border-slate-100 bg-slate-50/60">
                                <p class="font-bold text-slate-900 text-xs truncate">{{ user.name }}</p>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ user.email }}</p>
                                <div class="mt-2">
                                    <span class="text-[10px] font-semibold border px-2 py-0.5 rounded-full inline-block"
                                        :class="roleBadge(role).class">
                                        {{ roleBadge(role).label }}
                                    </span>
                                </div>
                            </div>

                            <!-- Menu Links -->
                            <div class="p-1.5 space-y-0.5">
                                <Link :href="route('profile.edit')" @click="showUserMenu = false"
                                    class="flex items-center gap-2.5 px-3 py-2 text-slate-700 hover:text-sky-700 hover:bg-sky-50/70 rounded-lg font-medium transition-colors">
                                    <Icon name="user" size="15" class="text-slate-400" />
                                    <span>Hồ sơ & Đổi mật khẩu</span>
                                </Link>
                                <Link :href="route('notifications.index')" @click="showUserMenu = false"
                                    class="flex items-center gap-2.5 px-3 py-2 text-slate-700 hover:text-sky-700 hover:bg-sky-50/70 rounded-lg font-medium transition-colors">
                                    <Icon name="bell" size="15" class="text-slate-400" />
                                    <span>Trung tâm Thông báo</span>
                                </Link>
                            </div>

                            <!-- Logout Button -->
                            <div class="p-1.5 border-t border-slate-100">
                                <Link :href="route('logout')" method="post" as="button"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-rose-600 hover:bg-rose-50 rounded-lg font-semibold transition-colors text-left cursor-pointer">
                                    <Icon name="logout" size="15" class="text-rose-500" />
                                    <span>Đăng xuất khỏi hệ thống</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Toast Notification Popup nổi góc trên bên phải khi có thông báo mới -->
            <transition enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="toastNotification"
                    class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full bg-white rounded-2xl border border-slate-200 shadow-2xl p-4 text-xs cursor-pointer hover:shadow-xl transition-all"
                    @click="markAsRead(toastNotification.id, toastNotification.data?.url)">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border"
                            :class="getNotifMeta(toastNotification.data?.type).bg">
                            <Icon :name="getNotifMeta(toastNotification.data?.type).icon" size="16" />
                        </div>
                        <div class="flex-1 min-w-0 pr-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.2 rounded-md"
                                    :class="getNotifMeta(toastNotification.data?.type).badge">
                                    {{ getNotifMeta(toastNotification.data?.type).label }}
                                </span>
                                <button @click.stop="closeToast" class="text-slate-400 hover:text-slate-600 p-0.5">
                                    <Icon name="x" size="14" />
                                </button>
                            </div>
                            <p class="font-bold text-slate-900 text-xs mt-1 truncate">
                                {{ toastNotification.data?.title }}
                            </p>
                            <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">
                                {{ toastNotification.data?.message }}
                            </p>
                            <div class="mt-2 flex items-center justify-between pt-1 border-t border-slate-100">
                                <span class="text-[10px] text-sky-600 font-bold">Bấm để xem chi tiết →</span>
                                <span class="text-[10px] text-slate-400">Vừa xong</span>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Flash messages thông báo -->
            <div v-if="flash.success"
                class="mx-4 sm:mx-6 mt-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center justify-between text-xs font-semibold shadow-xs animate-in fade-in slide-in-from-top-2 duration-200 shrink-0">
                <div class="flex items-center gap-2">
                    <Icon name="check" size="16" class="text-emerald-600" />
                    <span>{{ flash.success }}</span>
                </div>
            </div>

            <div v-if="flash.error"
                class="mx-4 sm:mx-6 mt-4 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center justify-between text-xs font-semibold shadow-xs animate-in fade-in slide-in-from-top-2 duration-200 shrink-0">
                <div class="flex items-center gap-2">
                    <Icon name="alert" size="16" class="text-rose-600" />
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-6 w-full">
                <slot />
            </main>

        </div>
    </div>
</template>