<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    records: Object,
    filters: Object,
})

const search = ref(props.filters.search ?? '')

let searchTimeout = null
watch(search, (value) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('medical-records.index'), {
            search: value
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['records', 'filters'],
            replace: true,
        })
    }, 300)
}, { immediate: false })

const statusConfig = {
    in_progress: { label: 'Đang điều trị', class: 'bg-amber-50 text-amber-700 border border-amber-200' },
    completed:   { label: 'Đã hoàn thành', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
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
    <Head title="Hồ sơ khám bệnh" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Hồ sơ Khám bệnh</h1>
                <p class="text-xs text-gray-500 mt-0.5">Tổng số: {{ records.total }} hồ sơ bệnh án trong hệ thống</p>
            </div>
            <Link :href="route('medical-records.create')"
                class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors">
                <Icon name="plus" size="16" />
                <span>Tạo phiếu khám mới</span>
            </Link>
        </div>

        <!-- Search bar -->
        <div class="mb-4">
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <Icon name="search" size="16" />
                </div>
                <input v-model="search" type="text" placeholder="Tìm theo tên thú cưng, chủ nhân, chẩn đoán..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all shadow-sm" />
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50/80 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Mã phiếu</th>
                            <th class="px-5 py-3 font-semibold">Thú cưng</th>
                            <th class="px-5 py-3 font-semibold">Chủ nhân</th>
                            <th class="px-5 py-3 font-semibold">Bác sĩ phụ trách</th>
                            <th class="px-5 py-3 font-semibold">Chẩn đoán</th>
                            <th class="px-5 py-3 font-semibold text-center">Trạng thái</th>
                            <th class="px-5 py-3 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="record in records.data" :key="record.id"
                            class="hover:bg-gray-50/80 transition-colors">
                            <!-- Mã phiếu -->
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-indigo-600 font-mono">#KB-{{ record.id.toString().padStart(4, '0') }}</span>
                                <p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ formatDate(record.created_at) }}</p>
                            </td>

                            <!-- Thú cưng -->
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-gray-900">{{ record.pet?.name }}</span>
                                <p class="text-[11px] text-gray-400 capitalize">
                                    {{ record.pet?.species }}
                                    <span v-if="record.weight_at_visit" class="font-mono">· {{ record.weight_at_visit }} kg</span>
                                </p>
                            </td>

                            <!-- Chủ nhân -->
                            <td class="px-5 py-3.5 text-gray-600">
                                <p class="font-medium text-gray-900">{{ record.pet?.customer?.name }}</p>
                                <p class="text-[11px] text-gray-400 font-mono">{{ record.pet?.customer?.phone }}</p>
                            </td>

                            <!-- Bác sĩ -->
                            <td class="px-5 py-3.5 text-gray-700">
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded">
                                    <Icon name="stethoscope" size="12" />
                                    <span>BS. {{ record.doctor?.name ?? '—' }}</span>
                                </span>
                            </td>

                            <!-- Chẩn đoán -->
                            <td class="px-5 py-3.5 text-gray-700 max-w-xs truncate font-medium">
                                {{ record.diagnosis }}
                            </td>

                            <!-- Trạng thái -->
                            <td class="px-5 py-3.5 text-center">
                                <span :class="statusConfig[record.status]?.class"
                                    class="text-[11px] px-2.5 py-0.5 rounded-full font-medium font-mono">
                                    {{ statusConfig[record.status]?.label ?? record.status }}
                                </span>
                            </td>

                            <!-- Thao tác -->
                            <td class="px-5 py-3.5 text-right">
                                <Link :href="route('medical-records.show', record.id)"
                                    class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded transition-colors font-medium">
                                    <Icon name="eye" size="13" />
                                    <span>Chi tiết</span>
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="records.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <Icon name="stethoscope" size="32" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-xs font-medium text-gray-600">Chưa có hồ sơ khám bệnh nào</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="records.last_page > 1" class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <p>Trang {{ records.current_page }} / {{ records.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="records.prev_page_url" :href="records.prev_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <Icon name="chevron-left" size="14" />
                        <span>Trước</span>
                    </Link>
                    <Link v-if="records.next_page_url" :href="records.next_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <span>Sau</span>
                        <Icon name="chevron-right" size="14" />
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
