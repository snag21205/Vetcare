<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    vouchers: Object,
    totalMonthExpense: [Number, String],
    filters: Object,
    categories: Object,
})

const search = ref(props.filters.search ?? '')
const category = ref(props.filters.category ?? '')
const month = ref(props.filters.month ?? new Date().toISOString().substring(0, 7))

let timeout = null
watch([search, category, month], ([newSearch, newCategory, newMonth]) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
        router.get(route('expense-vouchers.index'), {
            search: newSearch,
            category: newCategory,
            month: newMonth,
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['vouchers', 'totalMonthExpense', 'filters'],
            replace: true,
        })
    }, 300)
}, { immediate: false })

function deleteVoucher(id, desc) {
    if (!confirm(`Bạn chắc chắn muốn xóa phiếu chi: "${desc}"?`)) return
    router.delete(route('expense-vouchers.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        only: ['vouchers', 'totalMonthExpense', 'flash'],
    })
}

const categoryConfig = {
    medicine_import: { label: 'Nhập kho thuốc', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    operational: { label: 'Chi vận hành', class: 'bg-sky-50 text-sky-700 border-sky-200' },
    other: { label: 'Chi khác', class: 'bg-slate-100 text-slate-700 border-slate-200' },
}

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val ?? 0)
}
</script>

<template>

    <Head title="Quản lý Phiếu chi" />

        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Quản lý Phiếu chi & Chi phí</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Theo dõi dòng tiền chi ra cho nhập kho thuốc, mặt bằng và
                        vận hành phòng khám</p>
                </div>
                <Link :href="route('expense-vouchers.create')"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                    <Icon name="plus" size="16" />
                    <span>Lập phiếu chi mới</span>
                </Link>
            </div>

            <!-- Banner Tổng chi trong tháng -->
            <div
                class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs text-xs">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-11 h-11 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200 shrink-0">
                        <Icon name="document" size="20" />
                    </div>
                    <div>
                        <span class="text-[11px] uppercase font-bold text-rose-800 tracking-wider">Tổng chi tiêu tháng
                            {{ month.substring(5, 7) }}/{{ month.substring(0, 4) }}</span>
                        <p class="text-xs text-slate-600 mt-0.5">Bao gồm nhập kho thuốc, chi phí điện nước, mặt bằng và
                            các khoản chi khác</p>
                    </div>
                </div>

                <div class="sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-rose-200/60">
                    <span class="text-[11px] text-slate-500 uppercase block font-semibold tracking-wider">Tổng
                        chi</span>
                    <span class="text-2xl font-bold tabular-nums text-rose-600 block">
                        {{ formatCurrency(totalMonthExpense) }}
                    </span>
                </div>
            </div>

            <!-- Filter bar -->
            <div
                class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs text-xs">
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Icon name="search" size="15" />
                    </div>
                    <input v-model="search" type="text" placeholder="Tìm theo lý do chi..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50/60 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Tháng -->
                    <input v-model="month" type="month"
                        class="border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-semibold" />

                    <!-- Danh mục chi -->
                    <select v-model="category"
                        class="border border-slate-200 rounded-xl pr-9 px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium">
                        <option value=""> Tất cả danh mục </option>
                        <option v-for="(label, key) in categories" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong) -->
            <div
                class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-340px)] min-h-[380px]">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-xs text-left min-w-[700px]">
                        <thead
                            class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5">Mã / Ngày lập</th>
                                <th class="px-5 py-3.5">Danh mục</th>
                                <th class="px-5 py-3.5">Lý do chi</th>
                                <th class="px-5 py-3.5 text-right">Số tiền chi</th>
                                <th class="px-5 py-3.5">Người lập phiếu</th>
                                <th class="px-5 py-3.5 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="v in vouchers.data" :key="v.id" class="hover:bg-slate-50/80 transition-colors">
                                <!-- Mã & Ngày -->
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-900 font-mono">#PC-{{ v.id.toString().padStart(4,
                                        '0') }}</span>
                                    <p class="text-[11px] text-slate-400 tabular-nums mt-0.5">{{
                                        formatDate(v.voucher_date) }}</p>
                                </td>

                                <!-- Danh mục -->
                                <td class="px-5 py-3.5">
                                    <span :class="categoryConfig[v.category]?.class"
                                        class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold border">
                                        {{ categoryConfig[v.category]?.label ?? v.category }}
                                    </span>
                                </td>

                                <!-- Lý do chi -->
                                <td class="px-5 py-3.5 font-medium text-slate-900 max-w-sm">
                                    {{ v.description }}
                                </td>

                                <!-- Số tiền chi -->
                                <td class="px-5 py-3.5 text-right tabular-nums font-bold text-rose-600 text-sm">
                                    -{{ formatCurrency(v.amount) }}
                                </td>

                                <!-- Người lập -->
                                <td class="px-5 py-3.5 text-slate-600">
                                    {{ v.creator?.name ?? '—' }}
                                </td>

                                <!-- Thao tác -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="route('expense-vouchers.edit', v.id)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                            <Icon name="edit" size="13" />
                                            <span>Sửa</span>
                                        </Link>
                                        <button @click="deleteVoucher(v.id, v.description)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition-colors font-semibold cursor-pointer">
                                            <Icon name="trash" size="13" />
                                            <span>Xóa</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="vouchers.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="document" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Không có phiếu chi nào trong tháng
                                        này</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="vouchers.last_page > 1"
                    class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <p>Trang <span class="font-bold text-slate-800">{{ vouchers.current_page }}</span> / {{
                        vouchers.last_page }}</p>
                    <div class="flex gap-1.5">
                        <Link v-if="vouchers.prev_page_url" :href="vouchers.prev_page_url" preserve-scroll
                            preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <Icon name="chevron-left" size="14" />
                            <span>Trước</span>
                        </Link>
                        <Link v-if="vouchers.next_page_url" :href="vouchers.next_page_url" preserve-scroll
                            preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <span>Sau</span>
                            <Icon name="chevron-right" size="14" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
</template>
