<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    customers: Object,
})

const formatCurrency = (val) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val ?? 0)
}
</script>

<template>
    <Head title="Khách hàng thân thiết" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Khách hàng Thân thiết & Doanh số</h1>
                <p class="text-xs text-gray-500 mt-0.5">Xếp hạng khách hàng theo tổng chi tiêu và tần suất đưa thú cưng đến khám</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden text-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/75 text-gray-500 font-mono uppercase text-[11px] border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold text-center w-16">Hạng</th>
                            <th class="px-5 py-3 font-semibold">Khách hàng</th>
                            <th class="px-5 py-3 font-semibold">Số điện thoại</th>
                            <th class="px-5 py-3 font-semibold text-center">Số thú cưng</th>
                            <th class="px-5 py-3 font-semibold text-center">Số lượt khám</th>
                            <th class="px-5 py-3 font-semibold text-right">Tổng chi tiêu</th>
                            <th class="px-5 py-3 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(c, idx) in customers.data" :key="c.id" class="hover:bg-gray-50/80 transition-colors">
                            <!-- Xếp hạng -->
                            <td class="px-5 py-3.5 text-center">
                                <span v-if="(customers.current_page - 1) * 15 + idx === 0"
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 font-bold font-mono text-xs border border-amber-300">
                                    🥇
                                </span>
                                <span v-else-if="(customers.current_page - 1) * 15 + idx === 1"
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-700 font-bold font-mono text-xs border border-slate-300">
                                    🥈
                                </span>
                                <span v-else-if="(customers.current_page - 1) * 15 + idx === 2"
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-50 text-amber-900 font-bold font-mono text-xs border border-amber-200">
                                    🥉
                                </span>
                                <span v-else class="font-mono text-gray-500 font-semibold">
                                    #{{ (customers.current_page - 1) * 15 + idx + 1 }}
                                </span>
                            </td>

                            <!-- Khách hàng -->
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-gray-900">{{ c.name }}</span>
                                <p v-if="c.address" class="text-[11px] text-gray-400 truncate max-w-xs">{{ c.address }}</p>
                            </td>

                            <!-- Điện thoại -->
                            <td class="px-5 py-3.5 text-gray-600 font-mono">
                                {{ c.phone }}
                            </td>

                            <!-- Số thú cưng -->
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-100 text-[11px] font-medium px-2 py-0.5 rounded-full font-mono">
                                    <Icon name="pet" size="12" />
                                    {{ c.pets_count }}
                                </span>
                            </td>

                            <!-- Lượt khám -->
                            <td class="px-5 py-3.5 text-center font-mono font-semibold text-gray-800">
                                {{ c.medical_records_count }} lần
                            </td>

                            <!-- Tổng chi tiêu -->
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-sm text-emerald-600">
                                {{ formatCurrency(c.total_spent) }}
                            </td>

                            <!-- Thao tác -->
                            <td class="px-5 py-3.5 text-right">
                                <Link :href="route('customers.edit', c.id)"
                                    class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded transition-colors font-medium">
                                    <Icon name="user" size="12" />
                                    <span>Hồ sơ</span>
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="customers.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <Icon name="users" size="32" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-xs font-medium text-gray-600">Chưa có dữ liệu khách hàng</p>
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
