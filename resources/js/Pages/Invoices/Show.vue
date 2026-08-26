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
    cancelled: { label: 'Đã hủy',         class: 'bg-slate-100 text-slate-700 border border-slate-200' },
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
        <div class="max-w-4xl mx-auto space-y-4 pb-12">
            <!-- Breadcrumb & Actions -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('invoices.index')" class="hover:text-sky-600 transition-colors">Hóa đơn</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold font-mono">{{ invoice.invoice_number }}</span>
            </nav>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6 text-xs">
                <!-- Header Hóa đơn -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">HỆ THỐNG PHÒNG KHÁM THÚ Y VETCARE</span>
                        <h2 class="text-lg font-bold text-slate-900 mt-0.5 flex items-center gap-2">
                            <span>HÓA ĐƠN THANH TOÁN</span>
                            <span class="text-sky-700 font-mono">{{ invoice.invoice_number }}</span>
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Ngày lập: {{ formatDate(invoice.created_at) }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 sm:self-start">
                        <span :class="statusConfig[invoice.status]?.class"
                            class="whitespace-nowrap inline-flex items-center justify-center text-[11px] px-3 py-1 rounded-full font-semibold">
                            {{ statusConfig[invoice.status]?.label ?? invoice.status }}
                        </span>
                        
                        <a :href="route('invoices.pdf', invoice.id)" target="_blank"
                            class="inline-flex items-center gap-1.5 text-xs border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100 px-3.5 py-1.5 rounded-xl font-semibold transition-colors">
                            <Icon name="document" size="14" />
                            <span>Xuất PDF</span>
                        </a>
                        <button @click="printInvoice"
                            class="inline-flex items-center gap-1.5 text-xs border border-slate-200 px-3.5 py-1.5 rounded-xl hover:bg-slate-50 text-slate-700 font-semibold transition-colors cursor-pointer">
                            <Icon name="printer" size="14" />
                            <span>In</span>
                        </button>
                        <button v-if="invoice.status === 'unpaid'"
                            @click="payInvoice"
                            class="inline-flex items-center gap-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-1.5 rounded-xl shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="14" />
                            <span>Xác nhận thanh toán</span>
                        </button>
                    </div>
                </div>

                <!-- Thông tin khách hàng & Bệnh nhân -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50/70 p-4 rounded-xl border border-slate-200/80">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Khách hàng</p>
                        <p class="font-bold text-slate-900 text-sm">{{ invoice.customer?.name }}</p>
                        <p class="text-slate-600 tabular-nums text-xs mt-0.5">SĐT: {{ invoice.customer?.phone }}</p>
                        <p v-if="invoice.customer?.address" class="text-slate-500 text-xs mt-0.5">Địa chỉ: {{ invoice.customer?.address }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Bệnh nhân & Bác sĩ</p>
                        <p class="font-bold text-slate-900 flex items-center gap-1.5 text-sm">
                            <Icon name="pet" size="15" class="text-sky-600" />
                            <span>{{ invoice.medical_record?.pet?.name }}</span>
                            <span class="text-slate-400 font-normal text-xs capitalize">({{ invoice.medical_record?.pet?.species }})</span>
                        </p>
                        <p class="text-slate-600 text-xs mt-0.5">
                            Bác sĩ: BS. {{ invoice.medical_record?.doctor?.name ?? '—' }}
                        </p>
                        <Link :href="route('medical-records.show', invoice.medical_record_id)"
                            class="text-xs text-sky-600 hover:underline font-semibold inline-block mt-1">
                            Xem bệnh án #KB-{{ invoice.medical_record_id?.toString().padStart(4, '0') }} →
                        </Link>
                    </div>
                </div>

                <!-- Chi tiết các khoản thanh toán -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Chi tiết dịch vụ & Thuốc kê đơn
                    </h3>

                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-left font-semibold">Hạng mục</th>
                                    <th class="px-4 py-2.5 text-center font-semibold">SL</th>
                                    <th class="px-4 py-2.5 text-right font-semibold">Đơn giá</th>
                                    <th class="px-4 py-2.5 text-right font-semibold">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <!-- Phí khám -->
                                <tr v-if="Number(invoice.subtotal_exam) > 0" class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-semibold text-slate-900 flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-sky-50 text-sky-600 flex items-center justify-center">
                                            <Icon name="stethoscope" size="13" />
                                        </div>
                                        <span>Phí khám & Tư vấn tổng quát</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600 tabular-nums font-medium">1</td>
                                    <td class="px-4 py-3 text-right text-slate-600 tabular-nums font-medium">{{ formatCurrency(invoice.subtotal_exam) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-bold text-slate-900">{{ formatCurrency(invoice.subtotal_exam) }}</td>
                                </tr>

                                <!-- Dịch vụ khác -->
                                <tr v-for="s in invoice.medical_record?.services" :key="s.id" class="hover:bg-slate-50/60">
                                    <template v-if="!s.name.toLowerCase().includes('khám tổng quát')">
                                        <td class="px-4 py-3 font-semibold text-slate-900">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded bg-sky-50 text-sky-600 flex items-center justify-center">
                                                    <Icon name="service" size="13" />
                                                </div>
                                                <span>{{ s.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-slate-600 tabular-nums font-medium">1</td>
                                        <td class="px-4 py-3 text-right text-slate-600 tabular-nums font-medium">{{ formatCurrency(s.pivot?.price ?? s.price) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums font-bold text-slate-900">{{ formatCurrency(s.pivot?.price ?? s.price) }}</td>
                                    </template>
                                </tr>

                                <!-- Thuốc kê đơn -->
                                <tr v-for="p in invoice.medical_record?.prescriptions" :key="p.id" class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                                <Icon name="pill" size="13" />
                                            </div>
                                            <span>{{ p.medicine?.name }}</span>
                                            <span v-if="p.medicine?.unit" class="text-slate-400 font-normal">({{ p.medicine?.unit }})</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600 tabular-nums font-medium">{{ p.quantity }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600 tabular-nums font-medium">{{ formatCurrency(p.medicine?.sell_price) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-bold text-slate-900">
                                        {{ formatCurrency(Number(p.medicine?.sell_price ?? 0) * Number(p.quantity)) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tổng kết chi phí -->
                <div class="pt-3 border-t border-slate-100 space-y-2 text-xs max-w-sm ml-auto">
                    <div class="flex justify-between text-slate-600">
                        <span>Tiền khám:</span>
                        <span class="tabular-nums font-semibold">{{ formatCurrency(invoice.subtotal_exam) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tiền dịch vụ:</span>
                        <span class="tabular-nums font-semibold">{{ formatCurrency(invoice.subtotal_service) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Tiền thuốc:</span>
                        <span class="tabular-nums font-semibold">{{ formatCurrency(invoice.subtotal_medicine) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base text-slate-900 pt-2.5 border-t border-slate-200">
                        <span>TỔNG THANH TOÁN:</span>
                        <span class="tabular-nums text-sky-700">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                    <div v-if="invoice.paid_at" class="text-[11px] text-emerald-700 text-right font-semibold pt-1">
                        Đã thanh toán lúc: {{ formatDate(invoice.paid_at) }}
                    </div>
                </div>

                <!-- Footer điều hướng -->
                <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                    <Link :href="route('invoices.index')"
                        class="px-4 py-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 font-semibold transition-colors">
                        ← Quay lại danh sách
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
