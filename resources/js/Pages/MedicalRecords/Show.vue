<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
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

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-5 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 font-medium">
                <Link :href="route('medical-records.index')" class="hover:text-indigo-600 transition-colors">Hồ sơ khám bệnh</Link>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-900 font-mono">#KB-{{ record.id.toString().padStart(4, '0') }}</span>
            </nav>

            <!-- Card Hóa đơn nháp sinh tự động -->
            <div v-if="record.invoice" class="bg-white border border-indigo-100 rounded-lg p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                            <Icon name="invoice" size="14" />
                        </div>
                        <span class="font-bold text-gray-900 text-xs font-mono">Hóa đơn: {{ record.invoice.invoice_number }}</span>
                        <span :class="invoiceStatusConfig[record.invoice.status]?.class"
                            class="text-[11px] px-2 py-0.5 rounded-full font-mono font-medium">
                            {{ invoiceStatusConfig[record.invoice.status]?.label ?? record.invoice.status }}
                        </span>
                    </div>
                    <div class="text-[11px] text-gray-500 flex flex-wrap gap-x-3 font-mono">
                        <span>Phí khám: <strong>{{ formatCurrency(record.invoice.subtotal_exam) }}</strong></span>
                        <span>Dịch vụ: <strong>{{ formatCurrency(record.invoice.subtotal_service) }}</strong></span>
                        <span>Thuốc: <strong>{{ formatCurrency(record.invoice.subtotal_medicine) }}</strong></span>
                    </div>
                </div>

                <div class="sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-100">
                    <span class="text-[10px] text-gray-400 uppercase font-mono block">Tổng tiền thanh toán</span>
                    <span class="text-lg font-bold font-mono text-indigo-600 block">
                        {{ formatCurrency(record.invoice.total_amount) }}
                    </span>
                    <Link :href="route('invoices.show', record.invoice.id)"
                        class="inline-flex items-center gap-1 mt-1 text-[11px] bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-2.5 py-1 rounded transition-colors shadow-xs">
                        <span>Xem hóa đơn</span>
                        <Icon name="arrow-right" size="11" />
                    </Link>
                </div>
            </div>

            <!-- Card chính Phiếu khám -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-6 text-xs">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-gray-900">
                                Phiếu khám bệnh <span class="font-mono text-indigo-600">#KB-{{ record.id.toString().padStart(4, '0') }}</span>
                            </h2>
                            <span :class="statusConfig[record.status]?.class"
                                class="text-[11px] px-2.5 py-0.5 rounded-full font-mono font-medium">
                                {{ statusConfig[record.status]?.label ?? record.status }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-400 font-mono mt-0.5">Ngày lập phiếu: {{ formatDate(record.created_at) }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Nút Xuất PDF (Tuần 7) -->
                        <a :href="route('medical-records.pdf', record.id)" target="_blank"
                            class="inline-flex items-center gap-1.5 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg font-semibold transition-colors shadow-2xs">
                            <Icon name="document" size="13" />
                            <span>Xuất PDF</span>
                        </a>

                        <!-- Nút In trực tiếp trình duyệt -->
                        <button @click="printRecord"
                            class="inline-flex items-center gap-1 text-xs border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                            <Icon name="printer" size="13" />
                            <span>In</span>
                        </button>
                    </div>
                </div>

                <!-- Thông tin bệnh nhân & Chủ nuôi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50/75 p-4 rounded-lg border border-gray-100">
                    <div>
                        <p class="text-[10px] text-gray-400 font-mono uppercase font-semibold mb-1">Vật nuôi</p>
                        <p class="font-bold text-gray-900 text-sm flex items-center gap-1.5">
                            <Icon name="pet" size="14" class="text-indigo-600" />
                            <span>{{ record.pet?.name }}</span>
                            <span class="text-gray-400 font-normal text-xs capitalize">({{ record.pet?.species }})</span>
                        </p>
                        <div class="text-gray-600 mt-1.5 space-y-0.5 font-mono text-[11px]">
                            <p>Cân nặng: <strong class="text-gray-900">{{ record.weight_at_visit ? `${record.weight_at_visit} kg` : '—' }}</strong></p>
                            <p>Thân nhiệt: <strong class="text-gray-900">{{ record.temperature_at_visit ? `${record.temperature_at_visit} °C` : '—' }}</strong></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-400 font-mono uppercase font-semibold mb-1">Chủ nuôi & Bác sĩ</p>
                        <p class="font-bold text-gray-900">{{ record.pet?.customer?.name }}</p>
                        <p class="text-gray-600 font-mono mt-0.5">SĐT: {{ record.pet?.customer?.phone }}</p>
                        <p class="text-indigo-600 font-medium mt-1.5 flex items-center gap-1">
                            <Icon name="stethoscope" size="12" />
                            <span>BS. {{ record.doctor?.name ?? '—' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Triệu chứng & Chẩn đoán -->
                <div class="space-y-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase font-mono mb-1">Triệu chứng lâm sàng:</p>
                        <div class="p-3 bg-gray-50 rounded-lg text-gray-800 border border-gray-100">
                            {{ record.symptoms }}
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase font-mono mb-1">Chẩn đoán của bác sĩ:</p>
                        <div class="p-3 bg-indigo-50/50 text-indigo-950 font-medium rounded-lg border border-indigo-100">
                            {{ record.diagnosis }}
                        </div>
                    </div>

                    <div v-if="record.note">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase font-mono mb-1">Lời dặn / Ghi chú:</p>
                        <p class="text-gray-600 italic">{{ record.note }}</p>
                    </div>
                </div>

                <!-- Danh sách dịch vụ chỉ định -->
                <div class="pt-3 border-t border-gray-100">
                    <h3 class="text-xs font-bold text-gray-900 uppercase font-mono mb-2.5 flex items-center gap-1.5">
                        <Icon name="service" size="14" class="text-indigo-600" />
                        <span>Dịch vụ & Thủ thuật chỉ định</span>
                    </h3>

                    <div v-if="!record.services || record.services.length === 0"
                        class="p-4 text-center bg-gray-50 rounded-lg text-gray-400 text-xs">
                        Không có dịch vụ đi kèm.
                    </div>

                    <div v-else class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-mono border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">Tên dịch vụ</th>
                                    <th class="px-4 py-2 text-right font-semibold">Đơn giá</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="s in record.services" :key="s.id">
                                    <td class="px-4 py-2.5 font-medium text-gray-900">{{ s.name }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono font-semibold text-indigo-600">
                                        {{ formatCurrency(s.pivot?.price ?? s.price) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Đơn thuốc -->
                <div class="pt-3 border-t border-gray-100">
                    <h3 class="text-xs font-bold text-gray-900 uppercase font-mono mb-2.5 flex items-center gap-1.5">
                        <Icon name="pill" size="14" class="text-indigo-600" />
                        <span>Đơn thuốc chỉ định</span>
                    </h3>

                    <div v-if="!record.prescriptions || record.prescriptions.length === 0"
                        class="p-4 text-center bg-gray-50 rounded-lg text-gray-400 text-xs">
                        Không có đơn thuốc.
                    </div>

                    <div v-else class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-mono border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">Tên thuốc</th>
                                    <th class="px-4 py-2 text-left font-semibold">Liều dùng</th>
                                    <th class="px-4 py-2 text-center font-semibold">Số lượng</th>
                                    <th class="px-4 py-2 text-center font-semibold">Số ngày</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="p in record.prescriptions" :key="p.id">
                                    <td class="px-4 py-2.5 font-medium text-gray-900">
                                        {{ p.medicine?.name }}
                                        <span v-if="p.medicine?.unit" class="text-gray-400 font-normal">({{ p.medicine?.unit }})</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ p.dosage }}</td>
                                    <td class="px-4 py-2.5 text-center font-mono font-semibold text-indigo-600">
                                        {{ p.quantity }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-mono text-gray-600">{{ p.days ? `${p.days} ngày` : '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer điều hướng -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                    <Link :href="route('medical-records.index')"
                        class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        ← Quay lại danh sách
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
