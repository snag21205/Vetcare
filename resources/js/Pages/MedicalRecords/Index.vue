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
    in_progress: { label: 'Đang điều trị', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    completed:   { label: 'Đã hoàn thành', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
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
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Hồ sơ Khám bệnh</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Tổng số: {{ records.total }} hồ sơ bệnh án trong hệ thống</p>
                </div>
                <Link :href="route('medical-records.create')"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                    <Icon name="plus" size="16" />
                    <span>Tạo phiếu khám mới</span>
                </Link>
            </div>

            <!-- Search bar -->
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Icon name="search" size="15" />
                    </div>
                    <input v-model="search" type="text" placeholder="Tìm theo tên thú cưng, chủ nhân, chẩn đoán..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50/60 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                </div>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-250px)] min-h-[380px]">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-xs text-left min-w-[750px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5">Mã phiếu</th>
                                <th class="px-5 py-3.5">Thú cưng</th>
                                <th class="px-5 py-3.5">Chủ nhân</th>
                                <th class="px-5 py-3.5">Bác sĩ phụ trách</th>
                                <th class="px-5 py-3.5">Chẩn đoán</th>
                                <th class="px-5 py-3.5 text-center">Trạng thái</th>
                                <th class="px-5 py-3.5 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="record in records.data" :key="record.id"
                                class="hover:bg-slate-50/80 transition-colors">
                                <!-- Mã phiếu -->
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-sky-700 font-mono">#KB-{{ record.id.toString().padStart(4, '0') }}</span>
                                    <p class="text-[11px] text-slate-400 tabular-nums mt-0.5">{{ formatDate(record.created_at) }}</p>
                                </td>

                                <!-- Thú cưng -->
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-900 block">{{ record.pet?.name }}</span>
                                    <p class="text-[11px] text-slate-400 capitalize">
                                        {{ record.pet?.species }}
                                        <span v-if="record.weight_at_visit" class="tabular-nums">· {{ record.weight_at_visit }} kg</span>
                                    </p>
                                </td>

                                <!-- Chủ nhân -->
                                <td class="px-5 py-3.5 text-slate-600">
                                    <p class="font-semibold text-slate-900">{{ record.pet?.customer?.name }}</p>
                                    <p class="text-[11px] text-slate-400 tabular-nums">{{ record.pet?.customer?.phone }}</p>
                                </td>

                                <!-- Bác sĩ -->
                                <td class="px-5 py-3.5 text-slate-700">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-sky-700 bg-sky-50 border border-sky-100 px-2.5 py-0.5 rounded-md">
                                        <Icon name="stethoscope" size="12" />
                                        <span>BS. {{ record.doctor?.name ?? '—' }}</span>
                                    </span>
                                </td>

                                <!-- Chẩn đoán -->
                                <td class="px-5 py-3.5 text-slate-700 max-w-xs truncate font-medium">
                                    {{ record.diagnosis }}
                                </td>

                                <!-- Trạng thái -->
                                <td class="px-5 py-3.5 text-center">
                                    <span :class="statusConfig[record.status]?.class"
                                        class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold border">
                                        {{ statusConfig[record.status]?.label ?? record.status }}
                                    </span>
                                </td>

                                <!-- Thao tác -->
                                <td class="px-5 py-3.5 text-right">
                                    <Link :href="route('medical-records.show', record.id)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                        <Icon name="eye" size="13" />
                                        <span>Chi tiết</span>
                                    </Link>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="records.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="stethoscope" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Chưa có hồ sơ khám bệnh nào</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="records.last_page > 1" class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <p>Trang <span class="font-bold text-slate-800">{{ records.current_page }}</span> / {{ records.last_page }}</p>
                    <div class="flex gap-1.5">
                        <Link v-if="records.prev_page_url" :href="records.prev_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <Icon name="chevron-left" size="14" />
                            <span>Trước</span>
                        </Link>
                        <Link v-if="records.next_page_url" :href="records.next_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <span>Sau</span>
                            <Icon name="chevron-right" size="14" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
