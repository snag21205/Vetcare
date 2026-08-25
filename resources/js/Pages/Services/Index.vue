<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    services: Object,
    filters: Object,
})

const search = ref(props.filters.search ?? '')

let searchTimeout = null
watch(search, (value) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('services.index'), {
            search: value
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['services', 'filters'],
            replace: true,
        })
    }, 300)
}, { immediate: false })

function deleteService(id, name) {
    if (!confirm(`Bạn chắc chắn muốn xóa dịch vụ "${name}" khỏi bảng giá?`)) return
    router.delete(route('services.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        only: ['services', 'flash'],
    })
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val ?? 0)
}
</script>

<template>
    <Head title="Bảng giá dịch vụ" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Quản lý Bảng giá Dịch vụ</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Danh mục phí khám, xét nghiệm, siêu âm, tiêm vaccine và phẫu thuật</p>
                </div>
                <Link :href="route('services.create')"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                    <Icon name="plus" size="16" />
                    <span>Thêm dịch vụ mới</span>
                </Link>
            </div>

            <!-- Search bar -->
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Icon name="search" size="15" />
                    </div>
                    <input v-model="search" type="text" placeholder="Tìm theo tên dịch vụ, mô tả..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50/60 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                </div>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-250px)] min-h-[380px] text-xs">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-left min-w-[650px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5">Tên dịch vụ kỹ thuật</th>
                                <th class="px-5 py-3.5">Mô tả / Ghi chú</th>
                                <th class="px-5 py-3.5 text-right">Đơn giá niêm yết</th>
                                <th class="px-5 py-3.5 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="svc in services.data" :key="svc.id" class="hover:bg-slate-50/80 transition-colors">
                                <!-- Tên dịch vụ -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100 shrink-0">
                                            <Icon name="service" size="15" />
                                        </div>
                                        <span class="font-bold text-slate-900">{{ svc.name }}</span>
                                    </div>
                                </td>

                                <!-- Mô tả -->
                                <td class="px-5 py-3.5 text-slate-600 max-w-sm">
                                    {{ svc.description ?? '—' }}
                                </td>

                                <!-- Đơn giá -->
                                <td class="px-5 py-3.5 text-right font-bold text-sm text-sky-700 tabular-nums">
                                    {{ formatCurrency(svc.price) }}
                                </td>

                                <!-- Thao tác -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="route('services.edit', svc.id)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                            <Icon name="edit" size="13" />
                                            <span>Sửa</span>
                                        </Link>
                                        <button @click="deleteService(svc.id, svc.name)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition-colors font-semibold cursor-pointer">
                                            <Icon name="trash" size="13" />
                                            <span>Xóa</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="services.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="service" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Không tìm thấy dịch vụ nào</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="services.last_page > 1" class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <p>Trang <span class="font-bold text-slate-800">{{ services.current_page }}</span> / {{ services.last_page }}</p>
                    <div class="flex gap-1.5">
                        <Link v-if="services.prev_page_url" :href="services.prev_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <Icon name="chevron-left" size="14" />
                            <span>Trước</span>
                        </Link>
                        <Link v-if="services.next_page_url" :href="services.next_page_url"
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
