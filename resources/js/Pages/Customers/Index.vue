<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
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

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Quản lý Khách hàng</h1>
                <p class="text-xs text-gray-500 mt-0.5">Tổng số: {{ customers.total }} khách hàng trong hệ thống</p>
            </div>
            <Link :href="route('customers.create')"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors">
                <Icon name="plus" size="16" />
                <span>Thêm khách hàng</span>
            </Link>
        </div>

        <!-- Search bar -->
        <div class="mb-4">
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <Icon name="search" size="16" />
                </div>
                <input v-model="search" type="text" placeholder="Tìm theo tên hoặc số điện thoại..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all shadow-sm" />
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50/75 text-gray-500 font-mono uppercase text-[11px] border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Tên khách hàng</th>
                            <th class="px-5 py-3 font-semibold">Số điện thoại</th>
                            <th class="px-5 py-3 font-semibold">Email</th>
                            <th class="px-5 py-3 font-semibold text-center">Vật nuôi</th>
                            <th class="px-5 py-3 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="customer in customers.data" :key="customer.id"
                            class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-gray-900">{{ customer.name }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 font-mono">{{ customer.phone }}</td>
                            <td class="px-5 py-3.5 text-gray-500">{{ customer.email ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-100 text-[11px] font-medium px-2.5 py-0.5 rounded-full font-mono">
                                    <Icon name="pet" size="12" />
                                    {{ customer.pets_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('customers.edit', customer.id)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="edit" size="13" />
                                        <span>Sửa</span>
                                    </Link>
                                    <button @click="deleteCustomer(customer.id, customer.name)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="trash" size="13" />
                                        <span>Xóa</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="customers.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <Icon name="search" size="32" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-xs font-medium text-gray-600">Không tìm thấy khách hàng nào</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="customers.last_page > 1" class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <p>Trang {{ customers.current_page }} / {{ customers.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="customers.prev_page_url" :href="customers.prev_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <Icon name="chevron-left" size="14" />
                        <span>Trước</span>
                    </Link>
                    <Link v-if="customers.next_page_url" :href="customers.next_page_url"
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