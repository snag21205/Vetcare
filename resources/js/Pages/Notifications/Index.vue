<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
    notifications: Object,
    unreadCount: Number,
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

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <span>Trung tâm Thông báo</span>
                        <span v-if="unreadCount > 0" class="text-xs bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded-full font-mono font-medium">
                            {{ unreadCount }} chưa đọc
                        </span>
                    </h1>
                    <p class="text-xs text-gray-500 mt-0.5">Theo dõi cảnh báo hệ thống, nhắc lịch hẹn và cập nhật tiếp nhận bệnh nhân</p>
                </div>

                <button v-if="unreadCount > 0" @click="markAllAsRead"
                    class="inline-flex items-center justify-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold px-3.5 py-2 rounded-lg shadow-2xs transition-colors">
                    <Icon name="check-check" size="14" class="text-indigo-600" />
                    <span>Đánh dấu tất cả đã đọc</span>
                </button>
            </div>

            <!-- Notifications List Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden text-xs divide-y divide-gray-100">
                <div v-for="n in notifications.data" :key="n.id"
                    class="p-4 hover:bg-gray-50/80 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                    :class="{ 'bg-indigo-50/20': !n.read_at }">

                    <div class="flex items-start gap-3.5">
                        <!-- Icon -->
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                            :class="n.data.type === 'low_stock' ? 'bg-rose-50 text-rose-600 border border-rose-200' : (n.data.type === 'new_appointment' ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' : 'bg-amber-50 text-amber-600 border border-amber-200')">
                            <Icon :name="n.data.type === 'low_stock' ? 'alert' : (n.data.type === 'new_appointment' ? 'calendar' : 'clock')" size="16" />
                        </div>

                        <!-- Content -->
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-gray-900 text-xs">{{ n.data.title ?? 'Thông báo' }}</h3>
                                <span v-if="!n.read_at" class="inline-block w-2 h-2 rounded-full bg-indigo-600"></span>
                            </div>
                            <p class="text-xs text-gray-600">{{ n.data.message }}</p>
                            <span class="text-[11px] text-gray-400 font-mono block">
                                {{ new Date(n.created_at).toLocaleDateString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }) }}
                                ({{ timeAgo(n.created_at) }})
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                        <button v-if="n.data.url" @click="markAsRead(n.id, n.data.url)"
                            class="inline-flex items-center gap-1 text-[11px] bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-2.5 py-1 rounded font-medium transition-colors">
                            <span>Xem chi tiết</span>
                            <Icon name="chevron-right" size="12" />
                        </button>
                        
                        <button v-if="!n.read_at" @click="markAsRead(n.id)"
                            class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded font-medium transition-colors"
                            title="Đánh dấu đã đọc">
                            <Icon name="check" size="12" />
                            <span>Đã đọc</span>
                        </button>

                        <button @click="deleteNotification(n.id)"
                            class="p-1 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors"
                            title="Xóa thông báo">
                            <Icon name="trash" size="13" />
                        </button>
                    </div>

                </div>

                <!-- Empty state -->
                <div v-if="notifications.data.length === 0" class="p-12 text-center text-gray-400">
                    <Icon name="bell" size="36" class="mx-auto text-gray-300 mb-2" />
                    <p class="text-xs font-medium text-gray-600">Bạn không có thông báo nào</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Các thông báo mới về lịch hẹn và cảnh báo kho sẽ xuất hiện ở đây.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.last_page > 1" class="mt-4 flex items-center justify-between text-xs text-gray-500">
                <p>Trang {{ notifications.current_page }} / {{ notifications.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="notifications.prev_page_url" :href="notifications.prev_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 bg-white rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <Icon name="chevron-left" size="14" />
                        <span>Trước</span>
                    </Link>
                    <Link v-if="notifications.next_page_url" :href="notifications.next_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 bg-white rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <span>Sau</span>
                        <Icon name="chevron-right" size="14" />
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
