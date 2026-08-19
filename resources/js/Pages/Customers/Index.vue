<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    customers: Object,
    filters: Object,
})

const search = ref(props.filters.search ?? '')

watch(search, (value) => {
    router.get(route('customers.index'), {
        search: value
    }, {
        preserveState: true,
        replace: true,
    })
}, { immediate: false })
</script>
<template>

    <Head title="Khách hàng" />
    <AuthenticatedLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"> Khách hàng</h1>
                <p class="text-sm text-gray-500 mt-1">Tổng: {{ customers.total }} khách hàng</p>
            </div>
            <Link :href="route('customers.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-color flex items-center gap-2">
                + Thêm khách hàng
            </Link>
        </div>

        <!-- Search bar -->
        <div class="mb-4">
            <input v-model="search" type="text" placeholder="🔍 Tìm theo tên hoặc SĐT..."
                class="w-full max-w-md border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Tên khách hàng</th>
                        <th class="px-6 py-3 text-left">Số điện thoại</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-center">Thú cưng</th>
                        <th class="px-6 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- v-for: lặp qua mảng, phải luôn có :key để Vue tracking -->
                    <!-- customers.data: vì paginate() trả về object có .data chứa array -->
                    <tr v-for="customer in customers.data" :key="customer.id"
                        class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <Link :href="route('customers.show', customer.id)"
                                class="font-medium text-blue-600 hover:underline">
                                {{ customer.name }}
                            </Link>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ customer.phone }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ customer.email ?? '—' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">
                                {{ customer.pets_count }} thú
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="route('customers.edit', customer.id)"
                                    class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200">
                                    Sửa
                                </Link>
                                <!-- Xóa: dùng Link với method="delete" -->
                                <Link :href="route('customers.destroy', customer.id)" method="delete" as="button"
                                    :onBefore="() => confirm('Xóa khách hàng này?')"
                                    class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">
                                    Xóa
                                </Link>
                            </div>
                        </td>
                    </tr>

                    <!-- Hiển thị khi không có dữ liệu -->
                    <tr v-if="customers.data.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <p class="text-lg">🔍</p>
                            <p>Không tìm thấy khách hàng nào</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="customers.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Trang {{ customers.current_page }} / {{ customers.last_page }}
                </p>
                <div class="flex gap-2">
                    <Link v-if="customers.prev_page_url" :href="customers.prev_page_url"
                        class="px-3 py-1 text-sm border rounded hover:bg-gray-100">
                        ← Trước
                    </Link>
                    <Link v-if="customers.next_page_url" :href="customers.next_page_url"
                        class="px-3 py-1 text-sm border rounded hover:bg-gray-100">
                        Sau →
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>