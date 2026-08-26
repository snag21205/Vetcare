<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
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
const recentNotifications = ref([])
const unreadCount = ref(0)
const notifRef = ref(null)

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

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    fetchUnreadCount()
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

async function fetchNotifications() {
    try {
        const res = await fetch('/api/notifications/recent')
        if (res.ok) {
            const data = await res.json()
            recentNotifications.value = data.notifications
            unreadCount.value = data.unread_count
        }
    } catch {
        // silent fail
    }
}

async function fetchUnreadCount() {
    try {
        const res = await fetch('/api/notifications/recent')
        if (res.ok) {
            const data = await res.json()
            unreadCount.value = data.unread_count
        }
    } catch {
        // silent fail
    }
}

async function markAsRead(id, redirectUrl) {
    try {
        await fetch(`/notifications/${id}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
        })
        unreadCount.value = Math.max(0, unreadCount.value - 1)
        showNotifications.value = false
        if (redirectUrl) {
            router.visit(redirectUrl)
        }
    } catch {
        // silent
    }
}

async function markAllAsRead() {
    try {
        await fetch('/notifications/mark-all-as-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
        })
        unreadCount.value = 0
        recentNotifications.value.forEach(n => { n.read_at = new Date().toISOString() })
    } catch {
        // silent
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
    const diff = (new Date() - new Date(dateStr)) / 1000
    if (diff < 60) return 'Vừa xong'
    if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`
    if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`
    return `${Math.floor(diff / 86400)} ngày trước`
}
</script>

<template>
    <div class="flex h-screen w-full overflow-hidden bg-[#F8FAFC]">

        <!-- Sidebar bên trái (Desktop cố định, Mobile trượt slide-out drawer) -->
        <Sidebar :mobile-open="mobileSidebarOpen" @close="mobileSidebarOpen = false" />

        <!-- Khu vực chính bên phải (Topbar cố định, Nội dung cuộn độc lập tràn đều 100%) -->
        <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden w-full">

            <!-- Top Header Bar (Cố định ở đỉnh, không bị trôi) -->
            <header
                class="h-16 shrink-0 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between z-30 shadow-xs">

                <!-- Left: Hamburger button on mobile + Status & Breadcrumb -->
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <!-- Mobile Hamburger Button -->
                    <button @click="mobileSidebarOpen = true"
                        class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-cyan-500 lg:hidden p-2 rounded-xl text-white shadow-sm shadow-sky-500/20 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none"
                        title="Mở menu">
                        <Icon name="pet" size="19" />
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

                <!-- Right Actions: Notification Bell + User Profile & Logout Dropdown -->
                <div class="flex items-center gap-3">

                    <!-- Notification Bell Dropdown -->
                    <div class="relative" ref="notifRef">
                        <button @click.stop="toggleNotifications"
                            class="relative p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors focus:outline-none"
                            title="Thông báo hệ thống">
                            <Icon name="bell" size="18" />
                            <span v-if="unreadCount > 0"
                                class="absolute top-1.5 right-1.5 flex items-center justify-center min-w-[17px] h-[17px] px-1 text-[10px] font-bold text-white bg-rose-600 rounded-full ring-2 ring-white animate-in zoom-in-50">
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Notifications Popover -->
                        <div v-if="showNotifications"
                            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl border border-slate-200 shadow-xl z-50 text-xs animate-in fade-in zoom-in-95 duration-150 overflow-hidden">
                            <!-- Dropdown Header -->
                            <div
                                class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                                <div class="flex items-center gap-1.5 font-bold text-slate-900">
                                    <span>Thông báo</span>
                                    <span v-if="unreadCount > 0"
                                        class="text-[10px] bg-rose-50 text-rose-700 border border-rose-200 px-1.5 py-0.2 rounded-full font-bold">
                                        {{ unreadCount }} mới
                                    </span>
                                </div>
                                <button v-if="unreadCount > 0" @click="markAllAsRead"
                                    class="text-[11px] text-sky-600 hover:text-sky-800 font-semibold transition-colors">
                                    Đã đọc tất cả
                                </button>
                            </div>

                            <!-- Notifications List -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                <div v-for="n in recentNotifications" :key="n.id" @click="markAsRead(n.id, n.data.url)"
                                    class="p-3 hover:bg-slate-50 cursor-pointer transition-colors flex gap-2.5 items-start"
                                    :class="{ 'bg-sky-50/40': !n.read_at }">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                                        :class="n.data.type === 'low_stock' ? 'bg-rose-50 text-rose-600 border border-rose-100' : (n.data.type === 'new_appointment' ? 'bg-sky-50 text-sky-600 border border-sky-100' : 'bg-amber-50 text-amber-600 border border-amber-100')">
                                        <Icon
                                            :name="n.data.type === 'low_stock' ? 'alert' : (n.data.type === 'new_appointment' ? 'calendar' : 'clock')"
                                            size="13" />
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <p class="font-bold text-slate-900 text-[11px] truncate">{{ n.data.title ??
                                                'Thông báo' }}</p>
                                            <span v-if="!n.read_at"
                                                class="w-1.5 h-1.5 rounded-full bg-sky-600 shrink-0"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 line-clamp-2 mt-0.5">{{ n.data.message }}
                                        </p>
                                        <span class="text-[10px] text-slate-400 font-medium mt-1 block">{{
                                            timeAgo(n.created_at) }}</span>
                                    </div>
                                </div>

                                <div v-if="recentNotifications.length === 0" class="p-8 text-center text-slate-400">
                                    <Icon name="bell" size="24" class="mx-auto text-slate-300 mb-1" />
                                    <p class="text-[11px] text-slate-500 font-medium">Không có thông báo nào</p>
                                </div>
                            </div>

                            <!-- Dropdown Footer -->
                            <div class="p-2.5 border-t border-slate-100 bg-slate-50 text-center">
                                <Link :href="route('notifications.index')" @click="showNotifications = false"
                                    class="text-[11px] text-sky-600 hover:text-sky-800 font-bold transition-colors">
                                    Xem toàn bộ thông báo →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="h-5 w-px bg-slate-200"></div>

                    <!-- User Profile & Logout Dropdown Menu -->
                    <div class="relative" ref="userMenuRef">
                        <button @click.stop="toggleUserMenu"
                            class="flex items-center gap-2.5 p-1.5 pr-2.5 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none group">
                            <!-- Avatar -->
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-600 to-blue-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                            </div>
                            <!-- Name & Role -->
                            <div class="hidden sm:block text-left leading-tight">
                                <span
                                    class="font-bold text-xs text-slate-800 block truncate max-w-[130px] group-hover:text-sky-700 transition-colors">{{
                                        user.name }}</span>
                                <span class="text-[10px] font-semibold text-slate-500 block">
                                    {{ roleBadge(role).label }}
                                </span>
                            </div>
                            <!-- Chevron -->
                            <Icon name="chevron-down" size="13"
                                class="text-slate-400 group-hover:text-slate-600 transition-colors" />
                        </button>

                        <!-- User Popover Menu -->
                        <div v-if="showUserMenu"
                            class="absolute right-0 mt-2 w-60 bg-white rounded-xl border border-slate-200 shadow-xl z-50 text-xs animate-in fade-in zoom-in-95 duration-150 overflow-hidden">
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

            <!-- Main Content Area (Dàn đều 100% chiều rộng, không bị co cụm ở giữa khi zoom nhỏ) -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-6 w-full">
                <slot />
            </main>

        </div>
    </div>
</template>