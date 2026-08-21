<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    vouchers: Object,
    totalMonthExpense: [Number, String],
    filters: Object,
    categories: Object,
})

const search   = ref(props.filters.search ?? '')
const category = ref(props.filters.category ?? '')
const month    = ref(props.filters.month ?? new Date().toISOString().substring(0, 7))

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
    medicine_import: { label: 'Nhập kho thuốc', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
    operational:     { label: 'Chi vận hành',   class: 'bg-sky-50 text-sky-700 border border-sky-200' },
    other:           { label: 'Chi khác',       class: 'bg-zinc-100 text-zinc-700 border border-zinc-200' },
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

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Quản lý Phiếu chi & Chi phí</h1>
                <p class="text-xs text-gray-500 mt-0.5">Theo dõi dòng tiền chi ra cho nhập kho thuốc, mặt bằng và vận hành phòng khám</p>
            </div>
            <Link :href="route('expense-vouchers.create')"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors">
                <Icon name="plus" size="16" />
                <span>Lập phiếu chi mới</span>
            </Link>
        </div>

        <!-- Banner Tổng chi trong tháng -->
        <div class="bg-gradient-to-r from-rose-50 to-orange-50 border border-rose-200 rounded-lg p-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm text-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200 shrink-0">
                    <Icon name="document" size="20" />
                </div>
                <div>
                    <span class="text-[11px] font-mono uppercase font-semibold text-rose-800">Tổng chi tiêu tháng {{ month.substring(5, 7) }}/{{ month.substring(0, 4) }}</span>
                    <p class="text-xs text-gray-600 mt-0.5">Bao gồm nhập kho thuốc, chi phí điện nước, mặt bằng và các khoản chi khác</p>
                </div>
            </div>

            <div class="sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-rose-200">
                <span class="text-[10px] text-gray-500 font-mono uppercase block font-semibold">Tổng chi</span>
                <span class="text-2xl font-bold font-mono text-rose-600 block">
                    {{ formatCurrency(totalMonthExpense) }}
                </span>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex flex-wrap items-center gap-2.5 mb-4">
            <!-- Search -->
            <div class="relative w-full sm:max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <Icon name="search" size="15" />
                </div>
                <input v-model="search" type="text" placeholder="Tìm theo lý do chi..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all shadow-sm" />
            </div>

            <!-- Tháng -->
            <input v-model="month" type="month"
                class="border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white font-mono shadow-sm" />

            <!-- Danh mục chi -->
            <select v-model="category"
                class="border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-sm">
                <option value="">-- Tất cả danh mục --</option>
                <option v-for="(label, key) in categories" :key="key" :value="key">
                    {{ label }}
                </option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50/75 text-gray-500 font-mono uppercase text-[11px] border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Mã / Ngày lập</th>
                            <th class="px-5 py-3 font-semibold">Danh mục</th>
                            <th class="px-5 py-3 font-semibold">Lý do chi</th>
                            <th class="px-5 py-3 font-semibold text-right">Số tiền chi</th>
                            <th class="px-5 py-3 font-semibold">Người lập phiếu</th>
                            <th class="px-5 py-3 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="v in vouchers.data" :key="v.id" class="hover:bg-gray-50/80 transition-colors">
                            <!-- Mã & Ngày -->
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-gray-900 font-mono">#PC-{{ v.id.toString().padStart(4, '0') }}</span>
                                <p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ formatDate(v.voucher_date) }}</p>
                            </td>

                            <!-- Danh mục -->
                            <td class="px-5 py-3.5">
                                <span :class="categoryConfig[v.category]?.class"
                                    class="text-[11px] px-2.5 py-0.5 rounded-full font-medium font-mono">
                                    {{ categoryConfig[v.category]?.label ?? v.category }}
                                </span>
                            </td>

                            <!-- Lý do chi -->
                            <td class="px-5 py-3.5 font-medium text-gray-900 max-w-sm">
                                {{ v.description }}
                            </td>

                            <!-- Số tiền chi -->
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-rose-600 text-sm">
                                -{{ formatCurrency(v.amount) }}
                            </td>

                            <!-- Người lập -->
                            <td class="px-5 py-3.5 text-gray-600">
                                {{ v.creator?.name ?? '—' }}
                            </td>

                            <!-- Thao tác -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('expense-vouchers.edit', v.id)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="edit" size="13" />
                                        <span>Sửa</span>
                                    </Link>
                                    <button @click="deleteVoucher(v.id, v.description)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="trash" size="13" />
                                        <span>Xóa</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="vouchers.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <Icon name="document" size="32" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-xs font-medium text-gray-600">Không có phiếu chi nào trong tháng này</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="vouchers.last_page > 1" class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <p>Trang {{ vouchers.current_page }} / {{ vouchers.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="vouchers.prev_page_url" :href="vouchers.prev_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <Icon name="chevron-left" size="14" />
                        <span>Trước</span>
                    </Link>
                    <Link v-if="vouchers.next_page_url" :href="vouchers.next_page_url"
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
