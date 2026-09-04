<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    notifications: Object,
    unreadCount: Number,
})

const currentTab = ref('all')
const isTesting = ref(false)

const getNotifMeta = (type) => {
    switch (type) {
        case 'new_appointment':
            return {
                icon: 'calendar',
                bg: 'bg-sky-50 text-sky-600 border-sky-200',
                badge: 'bg-sky-100 text-sky-700 border-sky-200',
                label: 'Lịch hẹn mới',
            }
        case 'appointment_checked_in':
            return {
                icon: 'check',
                bg: 'bg-emerald-50 text-emerald-600 border-emerald-200',
                badge: 'bg-emerald-100 text-emerald-700 border-emerald-200',
                label: 'Check-in tiếp đón',
            }
        case 'appointment_reminder':
            return {
                icon: 'clock',
                bg: 'bg-indigo-50 text-indigo-600 border-indigo-200',
                badge: 'bg-indigo-100 text-indigo-700 border-indigo-200',
                label: 'Nhắc lịch hẹn',
            }
        case 'invoice_created':
            return {
                icon: 'credit-card',
                bg: 'bg-amber-50 text-amber-600 border-amber-200',
                badge: 'bg-amber-100 text-amber-700 border-amber-200',
                label: 'Hóa đơn chờ thu',
            }
        case 'invoice_paid':
            return {
                icon: 'check',
                bg: 'bg-teal-50 text-teal-600 border-teal-200',
                badge: 'bg-teal-100 text-teal-700 border-teal-200',
                label: 'Đã thanh toán',
            }
        case 'low_stock':
            return {
                icon: 'alert',
                bg: 'bg-rose-50 text-rose-600 border-rose-200',
                badge: 'bg-rose-100 text-rose-700 border-rose-200',
                label: 'Tồn kho thấp',
            }
        case 'expiring_medicine':
            return {
                icon: 'clock',
                bg: 'bg-orange-50 text-orange-600 border-orange-200',
                badge: 'bg-orange-100 text-orange-700 border-orange-200',
                label: 'Sắp hết hạn',
            }
        default:
            return {
                icon: 'bell',
                bg: 'bg-slate-50 text-slate-600 border-slate-200',
                badge: 'bg-slate-100 text-slate-700 border-slate-200',
                label: 'Thông báo',
            }
    }
}

// Lọc thông báo trên giao diện theo Tab
const filteredNotifications = computed(() => {
    const list = props.notifications.data ?? []
    if (currentTab.value === 'all') return list
    if (currentTab.value === 'unread') return list.filter(n => !n.read_at)
    if (currentTab.value === 'appointments') {
        return list.filter(n => ['new_appointment', 'appointment_checked_in', 'appointment_reminder'].includes(n.data?.type))
    }
    if (currentTab.value === 'invoices') {
        return list.filter(n => ['invoice_created', 'invoice_paid'].includes(n.data?.type))
    }
    if (currentTab.value === 'medicines') {
        return list.filter(n => ['low_stock', 'expiring_medicine'].includes(n.data?.type))
    }
    return list
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

function deleteNotification(id) {
    router.delete(route('notifications.destroy', id), {
        preserveScroll: true,
        preserveState: true,
    })
}

function triggerTestNotification(type = '') {
    isTesting.value = true
    router.post(route('notifications.test'), { type }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isTesting.value = false
        }
    })
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
    <Head title="Trung tâm Thông báo" />

    <div class="space-y-4">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span>Trung tâm Thông báo</span>
                    <span v-if="unreadCount > 0" class="text-xs bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-bold tabular-nums">
                        {{ unreadCount }} chưa đọc
                    </span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Theo dõi cảnh báo hệ thống, nhắc lịch hẹn và cập nhật tiếp nhận bệnh nhân</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
                <!-- Nút test thông báo nhanh -->
                <div class="relative group">
                    <button type="button" @click="triggerTestNotification('')" :disabled="isTesting"
                        class="inline-flex items-center gap-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-xs font-semibold px-3.5 py-2 rounded-xl transition-colors cursor-pointer disabled:opacity-50">
                        <Icon name="bell" size="14" />
                        <span>{{ isTesting ? 'Đang bắn thử...' : 'Bắn thông báo test' }}</span>
                    </button>
                </div>

                <!-- Đánh dấu đã đọc tất cả -->
                <button v-if="unreadCount > 0" @click="markAllAsRead"
                    class="inline-flex items-center justify-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold px-4 py-2 rounded-xl shadow-xs transition-colors cursor-pointer">
                    <Icon name="check" size="14" class="text-sky-600" />
                    <span>Đã đọc tất cả</span>
                </button>
            </div>
        </div>

        <!-- Filter Tabs Bar -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
            <button @click="currentTab = 'all'"
                class="px-3 py-1.5 rounded-xl font-semibold transition-all cursor-pointer shrink-0"
                :class="currentTab === 'all' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                Tất cả ({{ notifications.data?.length ?? 0 }})
            </button>
            <button @click="currentTab = 'unread'"
                class="px-3 py-1.5 rounded-xl font-semibold transition-all cursor-pointer shrink-0"
                :class="currentTab === 'unread' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                Chưa đọc ({{ unreadCount }})
            </button>
            <button @click="currentTab = 'appointments'"
                class="px-3 py-1.5 rounded-xl font-semibold transition-all cursor-pointer shrink-0"
                :class="currentTab === 'appointments' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                Lịch hẹn & Tiếp đón
            </button>
            <button @click="currentTab = 'invoices'"
                class="px-3 py-1.5 rounded-xl font-semibold transition-all cursor-pointer shrink-0"
                :class="currentTab === 'invoices' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                Khám & Viện phí
            </button>
            <button @click="currentTab = 'medicines'"
                class="px-3 py-1.5 rounded-xl font-semibold transition-all cursor-pointer shrink-0"
                :class="currentTab === 'medicines' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                Kho dược (FEFO)
            </button>
        </div>

        <!-- Notifications List Card (Cuộn nội bộ bên trong) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden text-xs divide-y divide-slate-100 max-h-[calc(100vh-270px)] min-h-[380px] overflow-y-auto">
            <div v-for="n in filteredNotifications" :key="n.id"
                class="p-4 hover:bg-slate-50/80 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                :class="{ 'bg-sky-50/25': !n.read_at }">

                <div class="flex items-start gap-3.5">
                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5 border"
                        :class="getNotifMeta(n.data?.type).bg">
                        <Icon :name="getNotifMeta(n.data?.type).icon" size="18" />
                    </div>

                    <!-- Content -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider border"
                                :class="getNotifMeta(n.data?.type).badge">
                                {{ getNotifMeta(n.data?.type).label }}
                            </span>
                            <h3 class="font-bold text-slate-900 text-xs">{{ n.data?.title ?? 'Thông báo' }}</h3>
                            <span v-if="!n.read_at" class="inline-block w-2 h-2 rounded-full bg-sky-600"></span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ n.data?.message }}</p>
                        <span class="text-[11px] text-slate-400 tabular-nums block">
                            {{ new Date(n.created_at).toLocaleDateString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                            • {{ timeAgo(n.created_at) }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                    <button v-if="n.data?.url" @click="markAsRead(n.id, n.data.url)"
                        class="inline-flex items-center gap-1 text-[11px] bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 px-3 py-1.5 rounded-xl font-semibold transition-colors cursor-pointer">
                        <span>Xem chi tiết</span>
                        <Icon name="chevron-right" size="12" />
                    </button>
                    
                    <button v-if="!n.read_at" @click="markAsRead(n.id)"
                        class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1.5 rounded-xl font-semibold transition-colors cursor-pointer"
                        title="Đánh dấu đã đọc">
                        <Icon name="check" size="12" />
                        <span>Đã đọc</span>
                    </button>

                    <button @click="deleteNotification(n.id)"
                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer"
                        title="Xóa thông báo">
                        <Icon name="trash" size="14" />
                    </button>
                </div>

            </div>

            <!-- Empty state -->
            <div v-if="filteredNotifications.length === 0" class="p-12 text-center text-slate-400">
                <Icon name="bell" size="36" class="mx-auto text-slate-300 mb-2" />
                <p class="text-xs font-semibold text-slate-600">Không có thông báo nào trong mục này</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Các thông báo mới về lịch hẹn và viện phí sẽ tự động xuất hiện ở đây.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="notifications.last_page > 1" class="flex items-center justify-between text-xs text-slate-500">
            <p>Trang <span class="font-bold text-slate-800">{{ notifications.current_page }}</span> / {{ notifications.last_page }}</p>
            <div class="flex gap-1.5">
                <Link v-if="notifications.prev_page_url" :href="notifications.prev_page_url"
                    preserve-scroll preserve-state
                    class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 bg-white rounded-lg hover:bg-slate-50 text-slate-700 font-medium transition-colors">
                    <Icon name="chevron-left" size="14" />
                    <span>Trước</span>
                </Link>
                <Link v-if="notifications.next_page_url" :href="notifications.next_page_url"
                    preserve-scroll preserve-state
                    class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 bg-white rounded-lg hover:bg-slate-50 text-slate-700 font-medium transition-colors">
                    <span>Sau</span>
                    <Icon name="chevron-right" size="14" />
                </Link>
            </div>
        </div>
    </div>
</template>
