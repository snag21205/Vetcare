<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    invoices: Object,
    filters: Object,
    statuses: Object,
})

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')

watch([search, status], ([newSearch, newStatus]) => {
    router.get(route('invoices.index'), {
        search: newSearch,
        status: newStatus,
    }, {
        preserveState: true,
        replace: true,
    })
}, { immediate: false })

const statusConfig = {
    unpaid:    { label: 'Chờ thanh toán', class: 'bg-amber-50 text-amber-700 border border-amber-200' },
    paid:      { label: 'Đã thanh toán',  class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
    cancelled: { label: 'Đã hủy',         class: 'bg-rose-50 text-rose-700 border border-rose-200' },
}

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val ?? 0)
}

function payInvoice(id, invoiceNumber) {
    if (!confirm(`Xác nhận khách hàng đã thanh toán cho hóa đơn ${invoiceNumber}?`)) return
    router.patch(route('invoices.pay', id))
}
</script>

<template>
    <Head title="Quản lý Hóa đơn" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Quản lý Hóa đơn</h1>
                <p class="text-xs text-gray-500 mt-0.5">Tổng số: {{ invoices.total }} hóa đơn dịch vụ & thuốc trong hệ thống</p>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex flex-wrap gap-2.5 mb-4">
            <div class="relative w-full sm:max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <Icon name="search" size="15" />
                </div>
                <input v-model="search" type="text" placeholder="Tìm theo mã HĐ, tên khách, SĐT..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all shadow-sm" />
            </div>

            <select v-model="status"
                class="border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white font-mono shadow-sm">
                <option value="">-- Tất cả trạng thái --</option>
                <option v-for="(label, key) in statuses" :key="key" :value="key">
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
                            <th class="px-5 py-3 font-semibold">Mã hóa đơn</th>
                            <th class="px-5 py-3 font-semibold">Khách hàng</th>
                            <th class="px-5 py-3 font-semibold">Thú cưng / Phiếu khám</th>
                            <th class="px-5 py-3 font-semibold text-right">Tổng tiền</th>
                            <th class="px-5 py-3 font-semibold text-center">Trạng thái</th>
                            <th class="px-5 py-3 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-gray-50/80 transition-colors">
                            <!-- Mã hóa đơn -->
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-indigo-600 font-mono">{{ inv.invoice_number }}</span>
                                <p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ formatDate(inv.created_at) }}</p>
                            </td>

                            <!-- Khách hàng -->
                            <td class="px-5 py-3.5 text-gray-700">
                                <p class="font-semibold text-gray-900">{{ inv.customer?.name }}</p>
                                <p class="text-[11px] text-gray-400 font-mono">{{ inv.customer?.phone }}</p>
                            </td>

                            <!-- Thú cưng -->
                            <td class="px-5 py-3.5 text-gray-600">
                                <p class="font-medium text-gray-900">
                                    {{ inv.medical_record?.pet?.name }}
                                    <span class="text-gray-400 font-normal capitalize">({{ inv.medical_record?.pet?.species }})</span>
                                </p>
                                <Link :href="route('medical-records.show', inv.medical_record_id)"
                                    class="text-[11px] text-indigo-600 hover:underline font-mono">
                                    #KB-{{ inv.medical_record_id?.toString().padStart(4, '0') }}
                                </Link>
                            </td>

                            <!-- Tổng tiền -->
                            <td class="px-5 py-3.5 text-right font-mono">
                                <span class="font-bold text-sm text-gray-900">{{ formatCurrency(inv.total_amount) }}</span>
                            </td>

                            <!-- Trạng thái -->
                            <td class="px-5 py-3.5 text-center">
                                <span :class="statusConfig[inv.status]?.class"
                                    class="text-[11px] px-2.5 py-0.5 rounded-full font-medium font-mono">
                                    {{ statusConfig[inv.status]?.label ?? inv.status }}
                                </span>
                            </td>

                            <!-- Thao tác -->
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button v-if="inv.status === 'unpaid'"
                                        @click="payInvoice(inv.id, inv.invoice_number)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded font-medium shadow-xs transition-colors">
                                        <Icon name="check" size="13" />
                                        <span>Thanh toán</span>
                                    </button>
                                    <Link :href="route('invoices.show', inv.id)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="eye" size="13" />
                                        <span>Chi tiết</span>
                                    </Link>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="invoices.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <Icon name="invoice" size="32" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-xs font-medium text-gray-600">Không tìm thấy hóa đơn nào</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="invoices.last_page > 1" class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <p>Trang {{ invoices.current_page }} / {{ invoices.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="invoices.prev_page_url" :href="invoices.prev_page_url"
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <Icon name="chevron-left" size="14" />
                        <span>Trước</span>
                    </Link>
                    <Link v-if="invoices.next_page_url" :href="invoices.next_page_url"
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <span>Sau</span>
                        <Icon name="chevron-right" size="14" />
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
