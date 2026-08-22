<script setup>
import Sidebar from '@/Components/Sidebar.vue'
import Icon from '@/Components/Icon.vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const user = computed(() => page.props.auth?.user ?? {})
const role = computed(() => page.props.auth?.role ?? '')
const unreadCount = computed(() => page.props.unreadNotificationsCount ?? 0)
const recentNotifications = computed(() => page.props.recentNotifications ?? [])

const showDropdown = ref(false)
const dropdownRef = ref(null)

function toggleDropdown() {
    showDropdown.value = !showDropdown.value
}

function handleClickOutside(e) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        showDropdown.value = false
    }
}

let pollTimer = null

onMounted(() => {
    document.addEventListener('click', handleClickOutside)

    // Tự động kiểm tra thông báo mới mỗi 10 giây (chỉ nạp cục bộ dữ liệu thông báo, không giật lag màn hình)
    pollTimer = setInterval(() => {
        router.reload({
            only: ['unreadNotificationsCount', 'recentNotifications'],
            preserveScroll: true,
            preserveState: true,
        })
    }, 10000)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    if (pollTimer) clearInterval(pollTimer)
})

function markAsRead(id, url) {
    router.patch(route('notifications.read', id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            if (url) router.visit(url)
        }
    })
}

function markAllAsRead() {
    router.post(route('notifications.mark-all-read'), {}, {
        preserveScroll: true,
        preserveState: true,
    })
}

const roleBadge = (r) => {
    const roles = {
        admin:       { label: 'Admin', class: 'bg-rose-50 text-rose-700 border-rose-200' },
        vet:         { label: 'Bác sĩ', class: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
        receptionist:{ label: 'Lễ tân', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
        accountant:  { label: 'Kế toán', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    }
    return roles[r] ?? { label: r, class: 'bg-gray-100 text-gray-700 border-gray-200' }
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
    <div class="flex min-h-screen bg-[#F9FAFB] text-zinc-900 font-sans antialiased selection:bg-indigo-600 selection:text-white">

        <!-- Sidebar bên trái -->
        <Sidebar />

        <!-- Khu vực nội dung chính bên phải -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">

            <!-- Top Header Bar -->
            <header class="h-14 bg-white border-b border-gray-200 px-6 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
                <!-- Left Status -->
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-medium text-gray-700">Phòng khám VetCare</span>
                    <span class="text-gray-300">|</span>
                    <span class="font-mono text-[11px] text-gray-400">
                        {{ new Date().toLocaleDateString('vi-VN', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                    </span>
                </div>

                <!-- Right Actions: Notification Bell + User Profile -->
                <div class="flex items-center gap-3">

                    <!-- Notification Bell Dropdown -->
                    <div class="relative" ref="dropdownRef">
                        <button @click.stop="toggleDropdown"
                            class="relative p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors focus:outline-none"
                            title="Thông báo">
                            <Icon name="bell" size="18" />
                            <span v-if="unreadCount > 0"
                                class="absolute top-1 right-1 flex items-center justify-center min-w-[16px] h-4 px-1 text-[10px] font-bold font-mono text-white bg-rose-600 rounded-full ring-2 ring-white animate-in zoom-in-50">
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Dropdown Popover -->
                        <div v-if="showDropdown"
                            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-lg border border-gray-200 shadow-xl z-50 text-xs animate-in fade-in zoom-in-95 duration-150">
                            <!-- Dropdown Header -->
                            <div class="p-3 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 font-bold text-gray-900">
                                    <span>Thông báo</span>
                                    <span v-if="unreadCount > 0" class="text-[10px] bg-rose-50 text-rose-700 border border-rose-200 px-1.5 py-0.2 rounded-full font-mono">
                                        {{ unreadCount }} mới
                                    </span>
                                </div>
                                <button v-if="unreadCount > 0" @click="markAllAsRead"
                                    class="text-[11px] text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                                    Đã đọc tất cả
                                </button>
                            </div>

                            <!-- Notifications List -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                                <div v-for="n in recentNotifications" :key="n.id"
                                    @click="markAsRead(n.id, n.data.url)"
                                    class="p-3 hover:bg-gray-50/80 cursor-pointer transition-colors flex gap-2.5 items-start"
                                    :class="{ 'bg-indigo-50/30': !n.read_at }">
                                    <!-- Icon badge -->
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                                        :class="n.data.type === 'low_stock' ? 'bg-rose-50 text-rose-600 border border-rose-100' : (n.data.type === 'new_appointment' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-amber-50 text-amber-600 border border-amber-100')">
                                        <Icon :name="n.data.type === 'low_stock' ? 'alert' : (n.data.type === 'new_appointment' ? 'calendar' : 'clock')" size="13" />
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <p class="font-bold text-gray-900 text-[11px] truncate">{{ n.data.title ?? 'Thông báo' }}</p>
                                            <span v-if="!n.read_at" class="w-1.5 h-1.5 rounded-full bg-indigo-600 shrink-0"></span>
                                        </div>
                                        <p class="text-[11px] text-gray-600 line-clamp-2 mt-0.5">{{ n.data.message }}</p>
                                        <span class="text-[10px] text-gray-400 font-mono mt-1 block">{{ timeAgo(n.created_at) }}</span>
                                    </div>
                                </div>

                                <!-- Empty state -->
                                <div v-if="recentNotifications.length === 0" class="p-8 text-center text-gray-400">
                                    <Icon name="bell" size="24" class="mx-auto text-gray-300 mb-1" />
                                    <p class="text-[11px] text-gray-500 font-medium">Không có thông báo nào</p>
                                </div>
                            </div>

                            <!-- Dropdown Footer -->
                            <div class="p-2.5 border-t border-gray-100 bg-gray-50 text-center rounded-b-lg">
                                <Link :href="route('notifications.index')" @click="showDropdown = false"
                                    class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                                    Xem toàn bộ thông báo →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="h-4 w-px bg-gray-200"></div>

                    <!-- User info -->
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs font-mono">
                            {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <span class="font-semibold text-xs text-gray-900 block truncate max-w-[120px]">{{ user.name }}</span>
                            <span class="text-[10px] font-mono border px-1.5 py-0.2 rounded-full inline-block mt-0.5"
                                :class="roleBadge(role).class">
                                {{ roleBadge(role).label }}
                            </span>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Flash messages thông báo -->
            <div v-if="flash.success" class="mx-6 mt-6 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center justify-between text-xs font-medium shadow-sm animate-in fade-in slide-in-from-top-2 duration-200">
                <div class="flex items-center gap-2">
                    <Icon name="check" size="16" class="text-emerald-600" />
                    <span>{{ flash.success }}</span>
                </div>
            </div>

            <div v-if="flash.error" class="mx-6 mt-6 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg flex items-center justify-between text-xs font-medium shadow-sm animate-in fade-in slide-in-from-top-2 duration-200">
                <div class="flex items-center gap-2">
                    <Icon name="alert" size="16" class="text-rose-600" />
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <!-- Main Content slot -->
            <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>

        </div>
    </div>
</template>