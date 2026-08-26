<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    invoices: Object,
    stats: Object,
    filters: Object,
    statuses: Object,
})

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')

let searchTimeout = null
watch([search, status], ([newSearch, newStatus]) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('invoices.index'), {
            search: newSearch,
            status: newStatus,
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['invoices', 'stats', 'filters'],
            replace: true,
        })
    }, 300)
}, { immediate: false })

function payInvoice(id, number) {
    if (!confirm(`Xác nhận thanh toán cho hóa đơn ${number}?`)) return
    router.post(route('invoices.pay', id), {
        payment_method: 'cash',
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['invoices', 'stats', 'flash'],
    })
}

const statusConfig = {
    unpaid:    { label: 'Chưa thanh toán', class: 'bg-rose-50 text-rose-700 border-rose-200' },
    paid:      { label: 'Đã thanh toán',   class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    cancelled: { label: 'Đã hủy',          class: 'bg-slate-100 text-slate-600 border-slate-200' },
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val ?? 0)
}

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}
</script>

<template>
    <Head title="Hóa đơn" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Hóa đơn & Thu ngân</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Quản lý hóa đơn dịch vụ và thanh toán tại phòng khám</p>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <StatCard title="Tổng doanh thu" :value="stats.total_revenue" icon="chart" color="green" format="currency" />
                <StatCard title="Hóa đơn chờ thu" :value="stats.unpaid_count" icon="invoice" color="red" />
                <StatCard title="Đã thu hôm nay" :value="stats.today_revenue" icon="check-circle" color="sky" format="currency" />
            </div>

            <!-- Search & Filter Bar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Icon name="search" size="15" />
                    </div>
                    <input v-model="search" type="text" placeholder="Tìm theo mã HĐ, tên khách, SĐT..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50/60 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                </div>

                <select v-model="status"
                    class="border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option v-for="(label, key) in statuses" :key="key" :value="key">
                        {{ label }}
                    </option>
                </select>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-340px)] min-h-[380px]">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-xs text-left min-w-[700px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5">Mã hóa đơn</th>
                                <th class="px-5 py-3.5">Khách hàng</th>
                                <th class="px-5 py-3.5">Thú cưng / Phiếu khám</th>
                                <th class="px-5 py-3.5 text-right">Tổng tiền</th>
                                <th class="px-5 py-3.5 text-center">Trạng thái</th>
                                <th class="px-5 py-3.5 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-50/80 transition-colors">
                                <!-- Mã hóa đơn -->
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-sky-700 font-mono">{{ inv.invoice_number }}</span>
                                    <p class="text-[11px] text-slate-400 tabular-nums mt-0.5">{{ formatDate(inv.created_at) }}</p>
                                </td>

                                <!-- Khách hàng -->
                                <td class="px-5 py-3.5 text-slate-700">
                                    <p class="font-bold text-slate-900">{{ inv.customer?.name }}</p>
                                    <p class="text-[11px] text-slate-400 tabular-nums">{{ inv.customer?.phone }}</p>
                                </td>

                                <!-- Thú cưng -->
                                <td class="px-5 py-3.5 text-slate-600">
                                    <p class="font-semibold text-slate-900">
                                        {{ inv.medical_record?.pet?.name }}
                                        <span class="text-slate-400 font-normal capitalize">({{ inv.medical_record?.pet?.species }})</span>
                                    </p>
                                    <Link :href="route('medical-records.show', inv.medical_record_id)"
                                        class="text-[11px] text-sky-600 hover:underline font-mono">
                                        #KB-{{ inv.medical_record_id?.toString().padStart(4, '0') }}
                                    </Link>
                                </td>

                                <!-- Tổng tiền -->
                                <td class="px-5 py-3.5 text-right">
                                    <span class="font-bold text-sm text-slate-900 tabular-nums">{{ formatCurrency(inv.total_amount) }}</span>
                                </td>

                                <!-- Trạng thái -->
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <span :class="statusConfig[inv.status]?.class"
                                        class="whitespace-nowrap inline-flex items-center justify-center text-[11px] px-2.5 py-0.5 rounded-full font-semibold border shrink-0">
                                        {{ statusConfig[inv.status]?.label ?? inv.status }}
                                    </span>
                                </td>

                                <!-- Thao tác -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button v-if="inv.status === 'unpaid'"
                                            @click="payInvoice(inv.id, inv.invoice_number)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-lg font-semibold shadow-xs transition-colors cursor-pointer">
                                            <Icon name="check" size="13" />
                                            <span>Thanh toán</span>
                                        </button>
                                        <Link :href="route('invoices.show', inv.id)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                            <Icon name="eye" size="13" />
                                            <span>Chi tiết</span>
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="invoices.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="invoice" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Không có hóa đơn nào</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="invoices.last_page > 1" class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <p>Trang <span class="font-bold text-slate-800">{{ invoices.current_page }}</span> / {{ invoices.last_page }}</p>
                    <div class="flex gap-1.5">
                        <Link v-if="invoices.prev_page_url" :href="invoices.prev_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <Icon name="chevron-left" size="14" />
                            <span>Trước</span>
                        </Link>
                        <Link v-if="invoices.next_page_url" :href="invoices.next_page_url"
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
