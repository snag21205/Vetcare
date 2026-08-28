<script setup>
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

        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Khách hàng Thân thiết & Doanh số</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Xếp hạng khách hàng theo tổng chi tiêu và tần suất đưa thú cưng đến khám</p>
                </div>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-200px)] min-h-[380px] text-xs">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-left min-w-[700px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold text-center w-16">Hạng</th>
                                <th class="px-5 py-3.5 font-semibold">Khách hàng</th>
                                <th class="px-5 py-3.5 font-semibold">Số điện thoại</th>
                                <th class="px-5 py-3.5 font-semibold text-center">Số thú cưng</th>
                                <th class="px-5 py-3.5 font-semibold text-center">Số lượt khám</th>
                                <th class="px-5 py-3.5 font-semibold text-right">Tổng chi tiêu</th>
                                <th class="px-5 py-3.5 font-semibold text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(c, idx) in customers.data" :key="c.id" class="hover:bg-slate-50/80 transition-colors">
                                <!-- Xếp hạng -->
                                <td class="px-5 py-3.5 text-center">
                                    <span v-if="(customers.current_page - 1) * 15 + idx === 0"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-bold text-xs border border-amber-300">
                                        🥇
                                    </span>
                                    <span v-else-if="(customers.current_page - 1) * 15 + idx === 1"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-xs border border-slate-300">
                                        🥈
                                    </span>
                                    <span v-else-if="(customers.current_page - 1) * 15 + idx === 2"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-50 text-amber-900 font-bold text-xs border border-amber-200">
                                        🥉
                                    </span>
                                    <span v-else class="text-slate-500 font-bold tabular-nums">
                                        #{{ (customers.current_page - 1) * 15 + idx + 1 }}
                                    </span>
                                </td>

                                <!-- Khách hàng -->
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-900 block">{{ c.name }}</span>
                                    <p v-if="c.address" class="text-[11px] text-slate-400 truncate max-w-xs">{{ c.address }}</p>
                                </td>

                                <!-- Điện thoại -->
                                <td class="px-5 py-3.5 text-slate-600 tabular-nums font-medium">
                                    {{ c.phone }}
                                </td>

                                <!-- Số thú cưng -->
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 bg-sky-50 text-sky-700 border border-sky-100 text-[11px] font-bold px-2.5 py-0.5 rounded-full tabular-nums">
                                        <Icon name="pet" size="12" />
                                        {{ c.pets_count }}
                                    </span>
                                </td>

                                <!-- Lượt khám -->
                                <td class="px-5 py-3.5 text-center font-bold text-slate-800 tabular-nums">
                                    {{ c.medical_records_count }} lần
                                </td>

                                <!-- Tổng chi tiêu -->
                                <td class="px-5 py-3.5 text-right font-bold text-sm text-emerald-600 tabular-nums">
                                    {{ formatCurrency(c.total_spent) }}
                                </td>

                                <!-- Thao tác -->
                                <td class="px-5 py-3.5 text-right">
                                    <Link :href="route('customers.edit', c.id)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                        <Icon name="user" size="12" />
                                        <span>Hồ sơ</span>
                                    </Link>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="customers.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="users" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Chưa có dữ liệu khách hàng</p>
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
