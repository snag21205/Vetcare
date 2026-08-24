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
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Quản lý Bảng giá Dịch vụ</h1>
                <p class="text-xs text-gray-500 mt-0.5">Danh mục phí khám, xét nghiệm, siêu âm, tiêm vaccine và phẫu thuật</p>
            </div>
            <Link :href="route('services.create')"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors">
                <Icon name="plus" size="16" />
                <span>Thêm dịch vụ mới</span>
            </Link>
        </div>

        <!-- Search bar -->
        <div class="mb-4">
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <Icon name="search" size="15" />
                </div>
                <input v-model="search" type="text" placeholder="Tìm theo tên dịch vụ, mô tả..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all shadow-sm" />
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden text-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/75 text-gray-500 font-mono uppercase text-[11px] border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Tên dịch vụ kỹ thuật</th>
                            <th class="px-5 py-3 font-semibold">Mô tả / Ghi chú</th>
                            <th class="px-5 py-3 font-semibold text-right">Đơn giá niêm yết</th>
                            <th class="px-5 py-3 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="svc in services.data" :key="svc.id" class="hover:bg-gray-50/80 transition-colors">
                            <!-- Tên dịch vụ -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 shrink-0">
                                        <Icon name="service" size="14" />
                                    </div>
                                    <span class="font-bold text-gray-900">{{ svc.name }}</span>
                                </div>
                            </td>

                            <!-- Mô tả -->
                            <td class="px-5 py-3.5 text-gray-600 max-w-sm">
                                {{ svc.description ?? '—' }}
                            </td>

                            <!-- Đơn giá -->
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-sm text-indigo-600">
                                {{ formatCurrency(svc.price) }}
                            </td>

                            <!-- Thao tác -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('services.edit', svc.id)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="edit" size="13" />
                                        <span>Sửa</span>
                                    </Link>
                                    <button @click="deleteService(svc.id, svc.name)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="trash" size="13" />
                                        <span>Xóa</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="services.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <Icon name="service" size="32" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-xs font-medium text-gray-600">Không tìm thấy dịch vụ nào</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="services.last_page > 1" class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <p>Trang {{ services.current_page }} / {{ services.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="services.prev_page_url" :href="services.prev_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <Icon name="chevron-left" size="14" />
                        <span>Trước</span>
                    </Link>
                    <Link v-if="services.next_page_url" :href="services.next_page_url"
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
