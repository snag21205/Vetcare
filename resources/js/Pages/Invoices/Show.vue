<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
    invoice: Object,
})

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

function payInvoice() {
    if (!confirm(`Xác nhận thanh toán ${formatCurrency(props.invoice.total_amount)} cho hóa đơn ${props.invoice.invoice_number}?`)) return
    router.patch(route('invoices.pay', props.invoice.id), {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['invoice', 'flash'],
    })
}

function printInvoice() {
    window.print()
}
</script>

<template>
    <Head :title="`Hóa đơn ${invoice.invoice_number}`" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-5 pb-12">
            <!-- Breadcrumb & Actions -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 font-medium">
                <Link :href="route('invoices.index')" class="hover:text-indigo-600 transition-colors">Hóa đơn</Link>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-900 font-mono">{{ invoice.invoice_number }}</span>
            </nav>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-6 text-xs">
                <!-- Header Hóa đơn -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <span class="text-[10px] text-gray-400 font-mono font-bold uppercase tracking-wider">HỆ THỐNG PHÒNG KHÁM VETCARE</span>
                        <h2 class="text-base font-bold text-gray-900 mt-0.5 flex items-center gap-2">
                            <span>HÓA ĐƠN THANH TOÁN</span>
                            <span class="font-mono text-indigo-600">{{ invoice.invoice_number }}</span>
                        </h2>
                        <p class="text-[11px] text-gray-400 font-mono mt-0.5">Ngày lập: {{ formatDate(invoice.created_at) }}</p>
                    </div>

                    <div class="text-right space-y-2">
                        <span :class="statusConfig[invoice.status]?.class"
                            class="inline-block text-[11px] px-2.5 py-0.5 rounded-full font-mono font-medium">
                            {{ statusConfig[invoice.status]?.label ?? invoice.status }}
                        </span>
                        <div class="flex gap-2 justify-end">
                            <a :href="route('invoices.pdf', invoice.id)" target="_blank"
                                class="inline-flex items-center gap-1 text-xs border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-3 py-1.5 rounded-lg font-medium transition-colors">
                                <Icon name="document" size="13" />
                                <span>Xuất PDF</span>
                            </a>
                            <button @click="printInvoice"
                                class="inline-flex items-center gap-1 text-xs border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                                <Icon name="printer" size="13" />
                                <span>In</span>
                            </button>
                            <button v-if="invoice.status === 'unpaid'"
                                @click="payInvoice"
                                class="inline-flex items-center gap-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-3 py-1.5 rounded-lg shadow-xs transition-colors">
                                <Icon name="check" size="13" />
                                <span>Xác nhận thanh toán</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Thông tin khách hàng & Bệnh nhân -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50/75 p-4 rounded-lg border border-gray-100">
                    <div>
                        <p class="text-[10px] text-gray-400 font-mono uppercase font-semibold mb-1">Khách hàng</p>
                        <p class="font-bold text-gray-900">{{ invoice.customer?.name }}</p>
                        <p class="text-gray-600 font-mono text-[11px] mt-0.5">SĐT: {{ invoice.customer?.phone }}</p>
                        <p v-if="invoice.customer?.address" class="text-gray-500 text-[11px] mt-0.5">Địa chỉ: {{ invoice.customer?.address }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-mono uppercase font-semibold mb-1">Bệnh nhân & Bác sĩ</p>
                        <p class="font-bold text-gray-900 flex items-center gap-1.5">
                            <Icon name="pet" size="14" class="text-indigo-600" />
                            <span>{{ invoice.medical_record?.pet?.name }}</span>
                            <span class="text-gray-400 font-normal text-xs capitalize">({{ invoice.medical_record?.pet?.species }})</span>
                        </p>
                        <p class="text-gray-600 text-[11px] mt-0.5">
                            Bác sĩ: BS. {{ invoice.medical_record?.doctor?.name ?? '—' }}
                        </p>
                        <Link :href="route('medical-records.show', invoice.medical_record_id)"
                            class="text-[11px] text-indigo-600 hover:underline font-mono inline-block mt-1">
                            Xem bệnh án #KB-{{ invoice.medical_record_id?.toString().padStart(4, '0') }} →
                        </Link>
                    </div>
                </div>

                <!-- Chi tiết các khoản thanh toán -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-gray-900 uppercase font-mono border-b border-gray-100 pb-2">
                        Chi tiết dịch vụ & Thuốc kê đơn
                    </h3>

                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-mono border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">Hạng mục</th>
                                    <th class="px-4 py-2 text-center font-semibold">SL</th>
                                    <th class="px-4 py-2 text-right font-semibold">Đơn giá</th>
                                    <th class="px-4 py-2 text-right font-semibold">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <!-- Phí khám -->
                                <tr v-if="Number(invoice.subtotal_exam) > 0">
                                    <td class="px-4 py-2.5 font-medium text-gray-900 flex items-center gap-1.5">
                                        <Icon name="stethoscope" size="13" class="text-indigo-600" />
                                        <span>Phí khám & Tư vấn tổng quát</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-gray-600 font-mono">1</td>
                                    <td class="px-4 py-2.5 text-right text-gray-600 font-mono">{{ formatCurrency(invoice.subtotal_exam) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono font-medium text-gray-900">{{ formatCurrency(invoice.subtotal_exam) }}</td>
                                </tr>

                                <!-- Dịch vụ khác -->
                                <tr v-for="s in invoice.medical_record?.services" :key="s.id">
                                    <template v-if="!s.name.toLowerCase().includes('khám tổng quát')">
                                        <td class="px-4 py-2.5 font-medium text-gray-900">
                                            <span class="text-indigo-600 mr-1.5">🔬</span>
                                            <span>{{ s.name }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-center text-gray-600 font-mono">1</td>
                                        <td class="px-4 py-2.5 text-right text-gray-600 font-mono">{{ formatCurrency(s.pivot?.price ?? s.price) }}</td>
                                        <td class="px-4 py-2.5 text-right font-mono font-medium text-gray-900">{{ formatCurrency(s.pivot?.price ?? s.price) }}</td>
                                    </template>
                                </tr>

                                <!-- Thuốc kê đơn -->
                                <tr v-for="p in invoice.medical_record?.prescriptions" :key="p.id">
                                    <td class="px-4 py-2.5 font-medium text-gray-900 flex items-center gap-1.5">
                                        <Icon name="pill" size="13" class="text-indigo-600" />
                                        <span>{{ p.medicine?.name }}</span>
                                        <span v-if="p.medicine?.unit" class="text-gray-400 font-normal">({{ p.medicine?.unit }})</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-gray-600 font-mono">{{ p.quantity }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-600 font-mono">{{ formatCurrency(p.medicine?.sell_price) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono font-medium text-gray-900">
                                        {{ formatCurrency(Number(p.medicine?.sell_price ?? 0) * Number(p.quantity)) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tổng kết chi phí -->
                <div class="pt-3 border-t border-gray-100 space-y-1.5 text-xs max-w-xs ml-auto">
                    <div class="flex justify-between text-gray-600">
                        <span>Tiền khám:</span>
                        <span class="font-mono">{{ formatCurrency(invoice.subtotal_exam) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Tiền dịch vụ:</span>
                        <span class="font-mono">{{ formatCurrency(invoice.subtotal_service) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Tiền thuốc:</span>
                        <span class="font-mono">{{ formatCurrency(invoice.subtotal_medicine) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-sm text-gray-900 pt-2 border-t border-gray-200">
                        <span>TỔNG THANH TOÁN:</span>
                        <span class="font-mono text-indigo-600">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                    <div v-if="invoice.paid_at" class="text-[11px] text-emerald-700 text-right font-mono pt-1">
                        Đã thanh toán lúc: {{ formatDate(invoice.paid_at) }}
                    </div>
                </div>

                <!-- Footer điều hướng -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                    <Link :href="route('invoices.index')"
                        class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        ← Quay lại danh sách
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
