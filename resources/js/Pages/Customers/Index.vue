<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    customers: Object,
    filters: Object,
})

function deleteCustomer(id, name) {
    if (!confirm(`Bạn chắc chắn muốn xóa khách hàng "${name}"?`)) return
    router.delete(route('customers.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        only: ['customers', 'flash'],
    })
}

const search = ref(props.filters.search ?? '')

let searchTimeout = null
watch(search, (value) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('customers.index'), {
            search: value
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['customers', 'filters'],
            replace: true,
        })
    }, 300)
}, { immediate: false })
</script>

<template>
    <Head title="Khách hàng" />

        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Quản lý Khách hàng</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Tổng số: {{ customers.total }} khách hàng trong hệ thống</p>
                </div>
                <Link :href="route('customers.create')"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                    <Icon name="plus" size="16" />
                    <span>Thêm khách hàng</span>
                </Link>
            </div>

            <!-- Search bar -->
            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Icon name="search" size="15" />
                    </div>
                    <input v-model="search" type="text" placeholder="Tìm theo tên hoặc số điện thoại..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50/60 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                </div>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-250px)] min-h-[380px]">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-xs text-left min-w-[650px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5">Tên khách hàng</th>
                                <th class="px-5 py-3.5">Số điện thoại</th>
                                <th class="px-5 py-3.5">Email</th>
                                <th class="px-5 py-3.5 text-center">Vật nuôi</th>
                                <th class="px-5 py-3.5 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="customer in customers.data" :key="customer.id"
                                class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-900">{{ customer.name }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 font-medium tabular-nums">{{ customer.phone }}</td>
                                <td class="px-5 py-3.5 text-slate-500">{{ customer.email ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 bg-sky-50 text-sky-700 border border-sky-100 text-[11px] font-bold px-2.5 py-0.5 rounded-full tabular-nums">
                                        <Icon name="pet" size="12" />
                                        {{ customer.pets_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="route('customers.edit', customer.id)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                            <Icon name="edit" size="13" />
                                            <span>Sửa</span>
                                        </Link>
                                        <button @click="deleteCustomer(customer.id, customer.name)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition-colors font-semibold cursor-pointer">
                                            <Icon name="trash" size="13" />
                                            <span>Xóa</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="customers.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="search" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Không tìm thấy khách hàng nào</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="customers.last_page > 1" class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <p>Trang <span class="font-bold text-slate-800">{{ customers.current_page }}</span> / {{ customers.last_page }}</p>
                    <div class="flex gap-1.5">
                        <Link v-if="customers.prev_page_url" :href="customers.prev_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <Icon name="chevron-left" size="14" />
                            <span>Trước</span>
                        </Link>
                        <Link v-if="customers.next_page_url" :href="customers.next_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <span>Sau</span>
                            <Icon name="chevron-right" size="14" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
</template>