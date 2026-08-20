<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    records: Object,
    filters: Object,
})

const search = ref(props.filters.search ?? '')

watch(search, (value) => {
    router.get(route('medical-records.index'), {
        search: value
    }, {
        preserveState: true,
        replace: true,
    })
}, { immediate: false })

const statusConfig = {
    in_progress: { label: 'Đang điều trị', class: 'bg-yellow-100 text-yellow-800' },
    completed:   { label: 'Đã hoàn thành', class: 'bg-green-100 text-green-800' },
}

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <Head title="Phiếu khám bệnh" />

    <AuthenticatedLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">🩺 Phiếu khám bệnh</h1>
                <p class="text-sm text-gray-500 mt-1">Tổng: {{ records.total }} phiếu khám</p>
            </div>
            <Link :href="route('medical-records.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                + Tạo phiếu khám
            </Link>
        </div>

        <!-- Search bar -->
        <div class="mb-4">
            <input v-model="search" type="text" placeholder="🔍 Tìm theo tên thú cưng, chủ nhân, chẩn đoán..."
                class="w-full max-w-md border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Mã phiếu</th>
                        <th class="px-6 py-3 text-left">Thú cưng</th>
                        <th class="px-6 py-3 text-left">Chủ nhân</th>
                        <th class="px-6 py-3 text-left">Bác sĩ</th>
                        <th class="px-6 py-3 text-left">Chẩn đoán</th>
                        <th class="px-6 py-3 text-center">Trạng thái</th>
                        <th class="px-6 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="record in records.data" :key="record.id"
                        class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-blue-600">#KB-{{ record.id.toString().padStart(4, '0') }}</span>
                            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(record.created_at) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 flex items-center gap-1.5">
                                <span>{{ record.pet?.species === 'chó' ? '🐕' : (record.pet?.species === 'mèo' ? '🐈' : '🐾') }}</span>
                                <span>{{ record.pet?.name }}</span>
                            </div>
                            <span v-if="record.weight_at_visit" class="text-xs text-gray-400">
                                {{ record.weight_at_visit }} kg
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <p class="font-medium">{{ record.pet?.customer?.name }}</p>
                            <p class="text-xs text-gray-400">{{ record.pet?.customer?.phone }}</p>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            BS. {{ record.doctor?.name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 max-w-xs truncate">
                            {{ record.diagnosis }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span :class="statusConfig[record.status]?.class"
                                class="text-xs px-2.5 py-1 rounded-full font-medium">
                                {{ statusConfig[record.status]?.label ?? record.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('medical-records.show', record.id)"
                                    class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200 transition-colors">
                                    Chi tiết
                                </Link>
                            </div>
                        </td>
                    </tr>

                    <!-- Hiển thị khi không có dữ liệu -->
                    <tr v-if="records.data.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <p class="text-lg">🔍</p>
                            <p>Không tìm thấy phiếu khám bệnh nào</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="records.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Trang {{ records.current_page }} / {{ records.last_page }}
                </p>
                <div class="flex gap-2">
                    <Link v-if="records.prev_page_url" :href="records.prev_page_url"
                        class="px-3 py-1 text-sm border rounded hover:bg-gray-100 transition-colors">
                        ← Trước
                    </Link>
                    <Link v-if="records.next_page_url" :href="records.next_page_url"
                        class="px-3 py-1 text-sm border rounded hover:bg-gray-100 transition-colors">
                        Sau →
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
