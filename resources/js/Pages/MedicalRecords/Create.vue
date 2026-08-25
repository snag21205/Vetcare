<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import AiDiagnosisModal from '@/Components/AiDiagnosisModal.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const showAiModal = ref(false)

const props = defineProps({
    appointment: Object,
    pets: Array,
    medicines: Array,
    services: {
        type: Array,
        default: () => []
    },
    initialPetHistory: {
        type: Array,
        default: () => []
    }
})

const page = usePage()
const currentUserId = page.props.auth?.user?.id

const defaultPetId = props.appointment ? props.appointment.pet_id : ''
const defaultDoctorId = props.appointment && props.appointment.doctor_id 
    ? props.appointment.doctor_id 
    : currentUserId

const form = useForm({
    appointment_id:       props.appointment ? props.appointment.id : null,
    pet_id:               defaultPetId,
    doctor_id:            defaultDoctorId,
    symptoms:             '',
    diagnosis:            '',
    weight_at_visit:      props.appointment?.pet?.weight ?? '',
    temperature_at_visit: '',
    note:                 '',
    status:               'completed',
    services:             [],
    prescriptions:        [],
})

// Quản lý Timeline lịch sử khám bệnh (Tuần 7)
const petHistory = ref(props.initialPetHistory ?? [])
const isLoadingHistory = ref(false)
const showHistory = ref(true)

watch(() => form.pet_id, async (newPetId) => {
    if (!newPetId) {
        petHistory.value = []
        return
    }

    // Tự động điền cân nặng nếu vật nuôi đã có
    const selectedPet = props.pets.find(p => p.id === Number(newPetId))
    if (selectedPet && selectedPet.weight && !form.weight_at_visit) {
        form.weight_at_visit = selectedPet.weight
    }

    try {
        isLoadingHistory.value = true
        const response = await fetch(route('pets.history', newPetId), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        if (response.ok) {
            petHistory.value = await response.json()
        }
    } catch (e) {
        console.error('Lỗi khi tải lịch sử khám bệnh:', e)
    } finally {
        isLoadingHistory.value = false
    }
})

const examService = computed(() => {
    return props.services.find(s => s.name.toLowerCase().includes('khám tổng quát'))
})
const examFee = computed(() => examService.value ? Number(examService.value.price) : 150000)

const totalServiceCost = computed(() => {
    let sum = examFee.value
    form.services.forEach(id => {
        const s = props.services.find(item => item.id === id)
        if (s && !s.name.toLowerCase().includes('khám tổng quát')) {
            sum += Number(s.price)
        }
    })
    return sum
})

const totalMedicineCost = computed(() => {
    return form.prescriptions.reduce((sum, item) => {
        if (!item.medicine_id || !item.quantity) return sum
        const med = props.medicines.find(m => m.id === item.medicine_id)
        const price = med ? Number(med.sell_price) : 0
        return sum + (price * Number(item.quantity))
    }, 0)
})

const estimatedTotal = computed(() => totalServiceCost.value + totalMedicineCost.value)

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

function addPrescription() {
    form.prescriptions.push({
        medicine_id: '',
        dosage: '',
        quantity: 1,
        days: null,
        note: '',
    })
}

function handleApplyAi(aiData) {
    if (aiData.diagnosis) {
        form.diagnosis = aiData.diagnosis
    }
    if (aiData.advice && !form.note) {
        form.note = aiData.advice
    }
    // Tự động tìm và tick các dịch vụ trùng khớp nếu có (hỗ trợ ID và Name)
    if (aiData.suggestedServices?.length > 0) {
        aiData.suggestedServices.forEach(item => {
            const svcId = typeof item === 'object' ? item.service_id : null
            const svcName = typeof item === 'object' ? (item.name || '') : item

            const matched = props.services.find(s => 
                (svcId && s.id === svcId) ||
                (svcName && s.name.toLowerCase().includes(svcName.toLowerCase())) ||
                (svcName && svcName.toLowerCase().includes(s.name.toLowerCase()))
            )
            if (matched && !form.services.includes(matched.id)) {
                form.services.push(matched.id)
            }
        })
    }
    // Tự động thêm thuốc gợi ý nếu tìm thấy trong kho (hỗ trợ ID và Name)
    if (aiData.suggestedMedicines?.length > 0) {
        aiData.suggestedMedicines.forEach(m => {
            const medId = m.medicine_id ?? null
            const medName = m.name ?? ''

            const matchedMed = props.medicines.find(med => 
                (medId && med.id === medId) ||
                (medName && med.name.toLowerCase().includes(medName.toLowerCase())) || 
                (medName && medName.toLowerCase().includes(med.name.toLowerCase()))
            )
            if (matchedMed) {
                const exists = form.prescriptions.some(p => p.medicine_id === matchedMed.id)
                if (!exists) {
                    const days = m.days ? Number(m.days) : 5
                    const qty = m.quantity ? Number(m.quantity) : days
                    if (form.prescriptions.length === 1 && !form.prescriptions[0].medicine_id) {
                        form.prescriptions[0].medicine_id = matchedMed.id
                        form.prescriptions[0].dosage = m.dosage || '1 viên/lần, ngày 2 lần'
                        form.prescriptions[0].days = days
                        form.prescriptions[0].quantity = qty
                        form.prescriptions[0].note = m.purpose || ''
                    } else {
                        form.prescriptions.push({
                            medicine_id: matchedMed.id,
                            dosage: m.dosage || '1 viên/lần, ngày 2 lần',
                            days: days,
                            quantity: qty,
                            note: m.purpose || '',
                        })
                    }
                }
            }
        })
    }
}

function submit() {
    form.post(route('medical-records.store'))
}
</script>

<template>
    <Head title="Lập phiếu khám bệnh" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 font-medium">
                <Link :href="route('medical-records.index')" class="hover:text-indigo-600 transition-colors">Phiếu khám bệnh</Link>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-900">Lập phiếu mới</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                <!-- Cột chính: Form nhập liệu (Chiếm 2 cột) -->
                <form @submit.prevent="submit" class="lg:col-span-2 space-y-5">
                    <!-- Card 1: Thông tin chung & Tiếp nhận -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4">
                        <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                    <Icon name="stethoscope" size="15" />
                                </div>
                                <h2 class="text-sm font-bold text-gray-900">Thông tin Tiếp nhận & Chỉ số sinh tồn</h2>
                            </div>
                            <span v-if="appointment" class="text-[11px] font-mono bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full font-medium">
                                Từ lịch hẹn #{{ appointment.id }}
                            </span>
                        </div>

                        <!-- Bệnh nhân -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">
                                    Bệnh nhân (Thú cưng) <span class="text-rose-500">*</span>
                                </label>
                                <select v-model="form.pet_id" :disabled="!!appointment"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-50 transition-all"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.pet_id }">
                                    <option value="">-- Chọn thú cưng --</option>
                                    <option v-for="p in pets" :key="p.id" :value="p.id">
                                        {{ p.name }} ({{ p.species }}) - Chủ: {{ p.customer?.name }} ({{ p.customer?.phone }})
                                    </option>
                                </select>
                                <p v-if="form.errors.pet_id" class="text-rose-500 text-[11px] mt-1">{{ form.errors.pet_id }}</p>
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 mb-1">
                                    Bác sĩ phụ trách khám <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" :value="`BS. ${page.props.auth.user?.name}`" disabled
                                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-600 font-medium cursor-not-allowed" />
                            </div>
                        </div>

                        <!-- Chỉ số sinh tồn -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-1">
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Cân nặng khi khám (kg)</label>
                                <input v-model="form.weight_at_visit" type="number" step="0.01" min="0.1" max="100"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                                    placeholder="3.5" />
                            </div>
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Thân nhiệt (°C)</label>
                                <input v-model="form.temperature_at_visit" type="number" step="0.1" min="30" max="45"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                                    placeholder="38.5" />
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Khám lâm sàng & Chẩn đoán -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4 text-xs">
                        <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                    <Icon name="document" size="15" />
                                </div>
                                <h2 class="text-sm font-bold text-gray-900">Khám lâm sàng & Chẩn đoán</h2>
                            </div>

                            <!-- Nút AI Gợi ý chẩn đoán -->
                            <button type="button" @click="showAiModal = true"
                                class="inline-flex items-center gap-1.5 bg-gradient-to-r from-sky-50 to-indigo-50 hover:from-sky-100 hover:to-indigo-100 text-sky-700 border border-sky-200 px-3 py-1.5 rounded-lg font-semibold text-xs transition-all shadow-xs">
                                <Icon name="sparkles" size="14" class="text-indigo-600" />
                                <span>AI Gợi ý chẩn đoán</span>
                            </button>
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Triệu chứng lâm sàng <span class="text-rose-500">*</span>
                            </label>
                            <textarea v-model="form.symptoms" rows="2"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.symptoms }"
                                placeholder="Mô tả triệu chứng bệnh: sốt, bỏ ăn, ngứa da, nôn mửa..."></textarea>
                            <p v-if="form.errors.symptoms" class="text-rose-500 text-[11px] mt-1">{{ form.errors.symptoms }}</p>
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Kết luận & Chẩn đoán <span class="text-rose-500">*</span>
                            </label>
                            <textarea v-model="form.diagnosis" rows="2"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.diagnosis }"
                                placeholder="Chẩn đoán của bác sĩ: Viêm ruột cấp, Viêm da do nấm..."></textarea>
                            <p v-if="form.errors.diagnosis" class="text-rose-500 text-[11px] mt-1">{{ form.errors.diagnosis }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Lời dặn / Ghi chú điều trị</label>
                                <input v-model="form.note" type="text"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                    placeholder="Uống nhiều nước, giữ ấm, hẹn tái khám sau 3 ngày..." />
                            </div>
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Trạng thái bệnh án</label>
                                <select v-model="form.status"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono">
                                    <option value="completed">Đã hoàn thành khám & kê đơn</option>
                                    <option value="in_progress">Đang điều trị nhiều ngày / Theo dõi</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Dịch vụ & Thủ thuật chỉ định -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4 text-xs">
                        <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                    <Icon name="service" size="15" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-gray-900">Dịch vụ & Thủ thuật chỉ định</h2>
                                    <p class="text-[11px] text-gray-500">Tích chọn các dịch vụ kỹ thuật cần thực hiện cho thú cưng</p>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded border border-indigo-100">
                                Tiền dịch vụ: {{ formatCurrency(totalServiceCost) }}
                            </span>
                        </div>

                        <!-- Phí khám mặc định -->
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Icon name="check-circle" size="16" class="text-indigo-600" />
                                <div>
                                    <span class="font-semibold text-gray-900">Phí khám & Tư vấn tổng quát</span>
                                    <span class="text-[10px] text-indigo-600 font-mono ml-2">(Mặc định)</span>
                                </div>
                            </div>
                            <span class="font-mono font-semibold text-gray-900">{{ formatCurrency(examFee) }}</span>
                        </div>

                        <!-- Danh sách dịch vụ bổ sung -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <template v-for="s in services" :key="s.id">
                                <label v-if="!s.name.toLowerCase().includes('khám tổng quát')"
                                    class="flex items-start gap-2.5 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors"
                                    :class="{ 'bg-indigo-50/50 border-indigo-300': form.services.includes(s.id) }">
                                    <input type="checkbox" :value="s.id" v-model="form.services"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5" />
                                    <div class="flex-1">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-gray-900">{{ s.name }}</span>
                                            <span class="font-mono font-semibold text-indigo-600 ml-2">{{ formatCurrency(s.price) }}</span>
                                        </div>
                                        <p v-if="s.description" class="text-[11px] text-gray-500 mt-0.5">{{ s.description }}</p>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Card 4: Kê đơn thuốc -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 space-y-4 text-xs">
                        <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                                    <Icon name="pill" size="15" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-gray-900">Kê đơn thuốc & Trừ kho tự động</h2>
                                    <p class="text-[11px] text-gray-500">Thuốc kê sẽ tự động trừ tồn kho và tính vào hóa đơn</p>
                                </div>
                            </div>
                            <button type="button" @click="addPrescription"
                                class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors">
                                <Icon name="plus" size="14" />
                                <span>Thêm thuốc</span>
                            </button>
                        </div>

                        <div v-if="form.prescriptions.length === 0"
                            class="p-6 text-center border-2 border-dashed border-gray-200 rounded-lg text-gray-400">
                            <Icon name="pill" size="24" class="mx-auto text-gray-300 mb-1" />
                            <p class="text-xs">Chưa kê thuốc nào. Bấm <strong>"+ Thêm thuốc"</strong> nếu cần chỉ định thuốc.</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="(item, idx) in form.prescriptions" :key="idx"
                                class="p-3 bg-gray-50 border border-gray-200 rounded-lg grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center">
                                
                                <!-- Tên thuốc -->
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-medium text-gray-600 mb-0.5">Thuốc</label>
                                    <select v-model="item.medicine_id"
                                        class="w-full bg-white border border-gray-200 rounded px-2.5 py-1.5 text-xs text-gray-900 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="">-- Chọn thuốc --</option>
                                        <option v-for="m in medicines" :key="m.id" :value="m.id">
                                            {{ m.name }} (Kho: {{ m.stock_quantity }} {{ m.unit }} - {{ formatCurrency(m.sell_price) }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Liều dùng -->
                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] font-medium text-gray-600 mb-0.5">Liều dùng</label>
                                    <input v-model="item.dosage" type="text"
                                        class="w-full bg-white border border-gray-200 rounded px-2.5 py-1.5 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        placeholder="1 viên x 2 lần/ngày" />
                                </div>

                                <!-- Số lượng -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-medium text-gray-600 mb-0.5">Số lượng</label>
                                    <input v-model="item.quantity" type="number" min="1"
                                        class="w-full bg-white border border-gray-200 rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                </div>

                                <!-- Số ngày -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-medium text-gray-600 mb-0.5">Số ngày</label>
                                    <input v-model="item.days" type="number" min="1"
                                        class="w-full bg-white border border-gray-200 rounded px-2.5 py-1.5 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        placeholder="5" />
                                </div>

                                <!-- Nút xóa -->
                                <div class="sm:col-span-1 flex sm:justify-end pt-3 sm:pt-0">
                                    <button type="button" @click="removePrescription(idx)"
                                        class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors"
                                        title="Xóa dòng này">
                                        <Icon name="trash" size="14" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Tổng kết chi phí & Sinh Hóa đơn -->
                    <div class="bg-gradient-to-br from-indigo-50/70 to-slate-50 border border-indigo-100 rounded-lg p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm text-xs">
                        <div>
                            <span class="text-[11px] font-mono uppercase font-semibold text-indigo-700 tracking-wider">Ước tính chi phí tự động</span>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-gray-600 mt-1">
                                <span>Dịch vụ & Khám: <strong class="text-gray-900 font-mono">{{ formatCurrency(totalServiceCost) }}</strong></span>
                                <span>·</span>
                                <span>Tiền thuốc: <strong class="text-gray-900 font-mono">{{ formatCurrency(totalMedicineCost) }}</strong></span>
                            </div>
                        </div>

                        <div class="sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-indigo-100">
                            <span class="text-[11px] text-gray-500 font-medium block">TỔNG TẠM TÍNH</span>
                            <span class="text-xl font-bold font-mono text-indigo-600 block">
                                {{ formatCurrency(estimatedTotal) }}
                            </span>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-between pt-2">
                        <Link :href="route('medical-records.index')"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 text-xs font-medium transition-colors">
                            ← Hủy bỏ
                        </Link>

                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold px-6 py-2.5 rounded-lg text-xs shadow-sm transition-colors">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang xử lý...' : 'Lưu phiếu khám & Sinh hóa đơn' }}</span>
                        </button>
                    </div>
                </form>

                <!-- Cột phụ: TIMELINE LỊCH SỬ KHÁM BỆNH (Tuần 7) -->
                <div class="lg:col-span-1 bg-white rounded-lg border border-gray-200 shadow-sm p-5 space-y-4 text-xs sticky top-6">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <Icon name="pet" size="15" class="text-indigo-600" />
                            <h3 class="font-bold text-gray-900 uppercase font-mono text-[11px]">Tiền sử bệnh án</h3>
                        </div>
                        <span class="text-[10px] font-mono bg-indigo-50 text-indigo-700 border border-indigo-100 px-2 py-0.5 rounded-full font-medium">
                            {{ petHistory.length }} lần khám
                        </span>
                    </div>

                    <div v-if="!form.pet_id" class="p-6 text-center text-gray-400">
                        <Icon name="pet" size="28" class="mx-auto text-gray-300 mb-2" />
                        <p class="text-xs">Chọn vật nuôi để xem lại lịch sử các lần khám trước.</p>
                    </div>

                    <div v-else-if="isLoadingHistory" class="p-6 text-center text-gray-400">
                        <p class="text-xs">Đang tải lịch sử khám...</p>
                    </div>

                    <div v-else-if="petHistory.length === 0" class="p-6 text-center text-gray-400 bg-gray-50 rounded-lg border border-gray-100">
                        <Icon name="check-circle" size="24" class="mx-auto text-emerald-500 mb-1" />
                        <p class="text-xs font-medium text-gray-700">Khám lần đầu</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Thú cưng này chưa có bệnh án trước đó.</p>
                    </div>

                    <!-- Danh sách timeline -->
                    <div v-else class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                        <div v-for="h in petHistory" :key="h.id"
                            class="p-3 rounded-lg border border-gray-200 bg-gray-50/75 hover:bg-gray-50 transition-colors space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-indigo-600 font-mono text-[11px]">
                                    #KB-{{ h.id.toString().padStart(4, '0') }}
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono">
                                    {{ formatDate(h.created_at) }}
                                </span>
                            </div>

                            <div>
                                <p class="text-[10px] text-gray-400 font-mono uppercase font-semibold">Chẩn đoán:</p>
                                <p class="font-semibold text-gray-900 text-xs">{{ h.diagnosis }}</p>
                            </div>

                            <p class="text-[11px] text-gray-600"><strong class="text-gray-700">Triệu chứng:</strong> {{ h.symptoms }}</p>

                            <!-- Đơn thuốc cũ -->
                            <div v-if="h.prescriptions && h.prescriptions.length > 0" class="pt-1 border-t border-gray-200/60">
                                <p class="text-[10px] text-gray-400 font-mono uppercase font-semibold">Thuốc đã dùng:</p>
                                <div class="flex flex-wrap gap-1 mt-0.5">
                                    <span v-for="p in h.prescriptions" :key="p.id"
                                        class="bg-white border border-gray-200 text-[10px] px-1.5 py-0.5 rounded text-gray-700 font-mono">
                                        {{ p.medicine?.name }} ({{ p.quantity }})
                                    </span>
                                </div>
                            </div>

                            <div class="pt-1 flex items-center justify-between text-[10px] text-gray-400 font-mono">
                                <span>BS. {{ h.doctor?.name ?? '—' }}</span>
                                <Link :href="route('medical-records.show', h.id)" target="_blank"
                                    class="text-indigo-600 hover:underline font-semibold">
                                    Chi tiết →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal AI Gợi ý chẩn đoán -->
        <AiDiagnosisModal
            :show="showAiModal"
            :species="selectedPet?.species ?? 'chó'"
            :symptoms="form.symptoms"
            :weight="form.weight_at_visit"
            :temperature="form.temperature_at_visit"
            @close="showAiModal = false"
            @apply="handleApplyAi"
        />
    </AuthenticatedLayout>
</template>
