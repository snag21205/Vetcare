<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    record: Object,
})

const statusConfig = {
    in_progress: { label: 'Đang điều trị', class: 'bg-amber-50 text-amber-700 border border-amber-200' },
    completed:   { label: 'Đã hoàn thành', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
}

const invoiceStatusConfig = {
    unpaid:    { label: 'Chờ thanh toán', class: 'bg-amber-50 text-amber-700 border border-amber-200' },
    paid:      { label: 'Đã thanh toán', class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
    cancelled: { label: 'Đã hủy', class: 'bg-rose-50 text-rose-700 border border-rose-200' },
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

function printRecord() {
    window.print()
}
</script>

<template>
    <Head :title="`Phiếu khám #KB-${record.id}`" />

        <div class="max-w-4xl mx-auto space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('medical-records.index')" class="hover:text-sky-600 transition-colors">Hồ sơ khám bệnh</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold font-mono">#KB-{{ record.id.toString().padStart(4, '0') }}</span>
            </nav>

            <!-- Card Hóa đơn nháp sinh tự động -->
            <div v-if="record.invoice" class="bg-white border border-sky-200 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                            <Icon name="invoice" size="15" />
                        </div>
                        <span class="font-bold text-slate-900 text-xs font-mono">Hóa đơn: {{ record.invoice.invoice_number }}</span>
                        <span :class="invoiceStatusConfig[record.invoice.status]?.class"
                            class="whitespace-nowrap text-[11px] px-2.5 py-0.5 rounded-full font-bold">
                            {{ invoiceStatusConfig[record.invoice.status]?.label ?? record.invoice.status }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 flex flex-wrap gap-x-3 tabular-nums">
                        <span>Phí khám: <strong class="text-slate-700">{{ formatCurrency(record.invoice.subtotal_exam) }}</strong></span>
                        <span>Dịch vụ: <strong class="text-slate-700">{{ formatCurrency(record.invoice.subtotal_service) }}</strong></span>
                        <span>Thuốc: <strong class="text-slate-700">{{ formatCurrency(record.invoice.subtotal_medicine) }}</strong></span>
                    </div>
                </div>

                <div class="sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Tổng tiền thanh toán</span>
                    <span class="text-lg font-bold tabular-nums text-sky-700 block">
                        {{ formatCurrency(record.invoice.total_amount) }}
                    </span>
                    <Link :href="route('invoices.show', record.invoice.id)"
                        class="inline-flex items-center gap-1 mt-1 text-xs bg-sky-600 hover:bg-sky-700 text-white font-semibold px-3 py-1.5 rounded-xl transition-colors shadow-xs">
                        <span>Xem hóa đơn</span>
                        <Icon name="arrow-right" size="12" />
                    </Link>
                </div>
            </div>

            <!-- Card chính Phiếu khám -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6 text-xs">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">
                                Phiếu khám bệnh <span class="font-mono text-sky-700">#KB-{{ record.id.toString().padStart(4, '0') }}</span>
                            </h2>
                            <span :class="statusConfig[record.status]?.class"
                                class="whitespace-nowrap text-[11px] px-2.5 py-0.5 rounded-full font-bold">
                                {{ statusConfig[record.status]?.label ?? record.status }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Ngày lập phiếu: {{ formatDate(record.created_at) }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Nút Xuất PDF -->
                        <a :href="route('medical-records.pdf', record.id)" target="_blank"
                            class="inline-flex items-center gap-1.5 text-xs bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 px-3.5 py-1.5 rounded-xl font-semibold transition-colors shadow-xs">
                            <Icon name="document" size="14" />
                            <span>Xuất PDF</span>
                        </a>

                        <!-- Nút In trực tiếp trình duyệt -->
                        <button @click="printRecord"
                            class="inline-flex items-center gap-1.5 text-xs border border-slate-200 px-3.5 py-1.5 rounded-xl hover:bg-slate-50 text-slate-700 font-semibold transition-colors cursor-pointer">
                            <Icon name="printer" size="14" />
                            <span>In</span>
                        </button>
                    </div>
                </div>

                <!-- Thông tin bệnh nhân & Chủ nuôi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50/70 p-4 rounded-xl border border-slate-200/80">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Vật nuôi</p>
                        <p class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                            <Icon name="pet" size="15" class="text-sky-600" />
                            <span>{{ record.pet?.name }}</span>
                            <span class="text-slate-400 font-normal text-xs capitalize">({{ record.pet?.species }})</span>
                        </p>
                        <div class="text-slate-600 mt-1.5 space-y-0.5 tabular-nums text-xs">
                            <p>Cân nặng: <strong class="text-slate-900">{{ record.weight_at_visit ? `${record.weight_at_visit} kg` : '—' }}</strong></p>
                            <p>Thân nhiệt: <strong class="text-slate-900">{{ record.temperature_at_visit ? `${record.temperature_at_visit} °C` : '—' }}</strong></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">Chủ nuôi & Bác sĩ</p>
                        <p class="font-bold text-slate-900 text-sm">{{ record.pet?.customer?.name }}</p>
                        <p class="text-slate-600 tabular-nums text-xs mt-0.5">SĐT: {{ record.pet?.customer?.phone }}</p>
                        <p class="text-sky-700 font-semibold mt-1.5 flex items-center gap-1">
                            <Icon name="stethoscope" size="13" />
                            <span>BS. {{ record.doctor?.name ?? '—' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Triệu chứng & Chẩn đoán -->
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Triệu chứng lâm sàng:</p>
                        <div class="p-3.5 bg-slate-50 rounded-xl text-slate-800 border border-slate-200/80 leading-relaxed">
                            {{ record.symptoms }}
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Chẩn đoán của bác sĩ:</p>
                        <div class="p-3.5 bg-sky-50 text-sky-950 font-bold rounded-xl border border-sky-200 leading-relaxed text-sm">
                            {{ record.diagnosis }}
                        </div>
                    </div>

                    <div v-if="record.note">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Lời dặn / Ghi chú:</p>
                        <p class="text-slate-600 italic bg-slate-50/50 p-3 rounded-xl border border-slate-100">{{ record.note }}</p>
                    </div>
                </div>

                <!-- Danh sách dịch vụ chỉ định -->
                <div class="pt-3 border-t border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <Icon name="service" size="14" class="text-sky-600" />
                        <span>Dịch vụ & Thủ thuật chỉ định</span>
                    </h3>

                    <div v-if="!record.services || record.services.length === 0"
                        class="p-4 text-center bg-slate-50 rounded-xl text-slate-400 text-xs">
                        Không có dịch vụ đi kèm.
                    </div>

                    <div v-else class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-left font-semibold">Tên dịch vụ</th>
                                    <th class="px-4 py-2.5 text-right font-semibold">Đơn giá</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="s in record.services" :key="s.id" class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ s.name }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-bold text-sky-700">
                                        {{ formatCurrency(s.pivot?.price ?? s.price) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Đơn thuốc -->
                <div class="pt-3 border-t border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <Icon name="pill" size="14" class="text-emerald-600" />
                        <span>Đơn thuốc chỉ định</span>
                    </h3>

                    <div v-if="!record.prescriptions || record.prescriptions.length === 0"
                        class="p-4 text-center bg-slate-50 rounded-xl text-slate-400 text-xs">
                        Không có đơn thuốc.
                    </div>

                    <div v-else class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-left font-semibold">Tên thuốc</th>
                                    <th class="px-4 py-2.5 text-left font-semibold">Liều dùng</th>
                                    <th class="px-4 py-2.5 text-center font-semibold">Số lượng</th>
                                    <th class="px-4 py-2.5 text-center font-semibold">Số ngày</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="p in record.prescriptions" :key="p.id" class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-semibold text-slate-900">
                                        {{ p.medicine?.name }}
                                        <span v-if="p.medicine?.unit" class="text-slate-400 font-normal">({{ p.medicine?.unit }})</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ p.dosage }}</td>
                                    <td class="px-4 py-3 text-center tabular-nums font-bold text-sky-700">
                                        {{ p.quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-center tabular-nums font-medium text-slate-600">{{ p.days ? `${p.days} ngày` : '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer điều hướng -->
                <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                    <Link :href="route('medical-records.index')"
                        class="px-4 py-2.5 border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-700 font-semibold transition-colors">
                        ← Quay lại danh sách
                    </Link>
                </div>
            </div>
        </div>
</template>
