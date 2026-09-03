<script setup>
import Icon from '@/Components/Icon.vue'
import AiDiagnosisModal from '@/Components/AiDiagnosisModal.vue'
import MedicineSearchSelect from '@/Components/MedicineSearchSelect.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const showAiModal = ref(false)
const serviceSearch = ref('')

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

// Quản lý Timeline lịch sử khám bệnh
const selectedPet = computed(() => props.pets.find(p => p.id === Number(form.pet_id)))
const petHistory = ref(props.initialPetHistory ?? [])
const isLoadingHistory = ref(false)
const showHistory = ref(true)

watch(() => form.pet_id, async (newPetId) => {
    if (!newPetId) {
        petHistory.value = []
        return
    }

    // Tự động điền cân nặng nếu vật nuôi đã có
    if (selectedPet.value && selectedPet.value.weight && !form.weight_at_visit) {
        form.weight_at_visit = selectedPet.value.weight
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

// Quản lý danh sách dịch vụ đã chọn & còn lại
const selectedServicesList = computed(() => {
    return props.services.filter(s => form.services.includes(s.id) && !s.name.toLowerCase().includes('khám tổng quát'))
})

const availableServicesList = computed(() => {
    const q = serviceSearch.value.trim().toLowerCase()
    return props.services.filter(s => {
        if (s.name.toLowerCase().includes('khám tổng quát')) return false
        if (form.services.includes(s.id)) return false
        if (!q) return true
        return s.name.toLowerCase().includes(q) || (s.description && s.description.toLowerCase().includes(q))
    })
})

function toggleService(id) {
    const idx = form.services.indexOf(id)
    if (idx > -1) {
        form.services.splice(idx, 1)
    } else {
        form.services.push(id)
    }
}

function removeService(id) {
    const idx = form.services.indexOf(id)
    if (idx > -1) {
        form.services.splice(idx, 1)
    }
}

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
        const med = props.medicines.find(m => String(m.id) === String(item.medicine_id))
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

function getMedicine(id) {
    if (!id) return null
    return props.medicines.find(m => String(m.id) === String(id)) || null
}

function onSelectMedicine(prescriptionItem, medicine) {
    prescriptionItem.medicine_id = medicine.id
    if (!prescriptionItem.dosage) {
        prescriptionItem.dosage = '1 viên/lần, ngày 2 lần'
    }
    if (!prescriptionItem.days) {
        prescriptionItem.days = 5
    }
    if (!prescriptionItem.quantity || prescriptionItem.quantity <= 0) {
        prescriptionItem.quantity = 5
    }
}

function addPrescription() {
    form.prescriptions.push({
        medicine_id: '',
        dosage: '1 viên/lần, ngày 2 lần',
        quantity: 5,
        days: 5,
        note: '',
    })
}

function removePrescription(index) {
    form.prescriptions.splice(index, 1)
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

        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('medical-records.index')" class="hover:text-sky-600 transition-colors">Phiếu khám bệnh</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Lập phiếu mới</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

                <!-- Cột chính: Form nhập liệu (Chiếm 2 cột) -->
                <form @submit.prevent="submit" class="lg:col-span-2 space-y-4">
                    
                    <!-- Card 1: Thông tin chung & Tiếp nhận -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4 text-xs">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                                    <Icon name="stethoscope" size="16" />
                                </div>
                                <h2 class="text-sm font-bold text-slate-900">Thông tin Tiếp nhận & Chỉ số sinh tồn</h2>
                            </div>
                            <span v-if="appointment" class="text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full">
                                Từ lịch hẹn #{{ appointment.id }}
                            </span>
                        </div>

                        <!-- Bệnh nhân -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Bệnh nhân (Thú cưng) <span class="text-rose-500">*</span>
                                </label>
                                <select v-model="form.pet_id" :disabled="!!appointment"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent disabled:bg-slate-100 transition-all font-medium"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.pet_id }">
                                    <option value="">-- Chọn thú cưng --</option>
                                    <option v-for="p in pets" :key="p.id" :value="p.id">
                                        {{ p.name }} ({{ p.species }}) - Chủ: {{ p.customer?.name }} ({{ p.customer?.phone }})
                                    </option>
                                </select>
                                <p v-if="form.errors.pet_id" class="text-rose-500 text-[11px] mt-1">{{ form.errors.pet_id }}</p>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Bác sĩ phụ trách khám <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" :value="`BS. ${page.props.auth.user?.name}`" disabled
                                    class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-600 font-semibold cursor-not-allowed" />
                            </div>
                        </div>

                        <!-- Chỉ số sinh tồn -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Cân nặng khi khám (kg)</label>
                                <input v-model="form.weight_at_visit" type="number" step="0.01" min="0.1" max="100"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                    placeholder="3.5" />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Thân nhiệt (°C)</label>
                                <input v-model="form.temperature_at_visit" type="number" step="0.1" min="30" max="45"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                    placeholder="38.5" />
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Khám lâm sàng & Chẩn đoán -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4 text-xs">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                                    <Icon name="document" size="16" />
                                </div>
                                <h2 class="text-sm font-bold text-slate-900">Khám lâm sàng & Chẩn đoán</h2>
                            </div>

                            <!-- Nút AI Gợi ý chẩn đoán -->
                            <button type="button" @click="showAiModal = true"
                                class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 text-white px-3.5 py-1.5 rounded-xl font-bold text-xs transition-colors shadow-xs cursor-pointer">
                                <Icon name="sparkles" size="14" />
                                <span>AI Gợi ý chẩn đoán</span>
                            </button>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Triệu chứng lâm sàng <span class="text-rose-500">*</span>
                            </label>
                            <textarea v-model="form.symptoms" rows="2"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.symptoms }"
                                placeholder="Mô tả triệu chứng bệnh: sốt, bỏ ăn, ngứa da, nôn mửa..."></textarea>
                            <p v-if="form.errors.symptoms" class="text-rose-500 text-[11px] mt-1">{{ form.errors.symptoms }}</p>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Kết luận & Chẩn đoán <span class="text-rose-500">*</span>
                            </label>
                            <textarea v-model="form.diagnosis" rows="2"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.diagnosis }"
                                placeholder="Chẩn đoán của bác sĩ: Viêm ruột cấp, Viêm da do nấm..."></textarea>
                            <p v-if="form.errors.diagnosis" class="text-rose-500 text-[11px] mt-1">{{ form.errors.diagnosis }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Lời dặn / Ghi chú điều trị</label>
                                <input v-model="form.note" type="text"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                    placeholder="Uống nhiều nước, giữ ấm, hẹn tái khám sau 3 ngày..." />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Trạng thái bệnh án</label>
                                <select v-model="form.status"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium">
                                    <option value="completed">Đã hoàn thành khám & kê đơn</option>
                                    <option value="in_progress">Đang điều trị nhiều ngày / Theo dõi</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Dịch vụ & Thủ thuật chỉ định (Cải tiến UX gọn gàng) -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4 text-xs">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100">
                                    <Icon name="service" size="16" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-slate-900">Dịch vụ & Thủ thuật chỉ định</h2>
                                    <p class="text-[11px] text-slate-500">Tích chọn các dịch vụ kỹ thuật cần thực hiện cho thú cưng</p>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-sky-700 bg-sky-50 px-3 py-1 rounded-xl border border-sky-100 tabular-nums">
                                Tiền dịch vụ: {{ formatCurrency(totalServiceCost) }}
                            </span>
                        </div>

                        <!-- Phí khám mặc định -->
                        <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <Icon name="check-circle" size="17" class="text-sky-600" />
                                <div>
                                    <span class="font-bold text-slate-900">Phí khám & Tư vấn tổng quát</span>
                                    <span class="text-[10px] text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full font-bold ml-2">Mặc định</span>
                                </div>
                            </div>
                            <span class="font-bold tabular-nums text-slate-900">{{ formatCurrency(examFee) }}</span>
                        </div>

                        <!-- Dịch vụ ĐÃ CHỌN (Hiển thị nổi bật phía trên) -->
                        <div v-if="selectedServicesList.length > 0" class="space-y-2">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>Dịch vụ bổ sung đã chọn ({{ selectedServicesList.length }}):</span>
                                <span class="text-slate-400 font-normal">Bấm nút (✕) để bỏ chọn</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div v-for="s in selectedServicesList" :key="s.id"
                                    class="p-3 bg-sky-50/70 border border-sky-200 rounded-xl flex items-center justify-between gap-2 transition-all">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-6 h-6 rounded-lg bg-sky-600 text-white flex items-center justify-center shrink-0">
                                            <Icon name="check" size="13" />
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 block truncate">{{ s.name }}</span>
                                            <span class="text-sky-700 font-bold tabular-nums text-[11px]">{{ formatCurrency(s.price) }}</span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeService(s.id)"
                                        class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer shrink-0"
                                        title="Bỏ dịch vụ này">
                                        <Icon name="x" size="15" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Danh sách Dịch vụ khả dụng (Kèm ô tìm kiếm & giới hạn độ cao cuộn) -->
                        <div class="space-y-2.5 pt-2 border-t border-slate-100">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <span class="font-semibold text-slate-700">Thêm dịch vụ khác:</span>
                                <!-- Search input -->
                                <div class="relative max-w-xs w-full">
                                    <input v-model="serviceSearch" type="text" placeholder="Tìm tên dịch vụ, xét nghiệm..."
                                        class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500" />
                                    <Icon name="search" size="13" class="absolute left-2.5 top-2.5 text-slate-400" />
                                </div>
                            </div>

                            <!-- Scroll container for available services -->
                            <div class="max-h-56 overflow-y-auto pr-1 space-y-1.5 custom-scrollbar border border-slate-100 rounded-xl p-2 bg-slate-50/40">
                                <div v-for="s in availableServicesList" :key="s.id"
                                    @click="toggleService(s.id)"
                                    class="p-2.5 rounded-xl border border-slate-200 bg-white hover:border-sky-300 hover:bg-sky-50/40 cursor-pointer transition-all flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <span class="font-semibold text-slate-900 block truncate">{{ s.name }}</span>
                                        <p v-if="s.description" class="text-[11px] text-slate-400 truncate">{{ s.description }}</p>
                                    </div>
                                    <div class="flex items-center gap-2.5 shrink-0">
                                        <span class="tabular-nums font-bold text-sky-700">{{ formatCurrency(s.price) }}</span>
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 text-slate-600 hover:bg-sky-600 hover:text-white transition-colors">
                                            <Icon name="plus" size="13" />
                                        </span>
                                    </div>
                                </div>

                                <div v-if="availableServicesList.length === 0" class="py-6 text-center text-slate-400">
                                    <p class="text-xs font-semibold">Không còn dịch vụ nào phù hợp</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Kê đơn thuốc & Trừ kho -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4 text-xs">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                                    <Icon name="pill" size="16" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-slate-900">Kê đơn thuốc & Trừ kho tự động</h2>
                                    <p class="text-[11px] text-slate-500">Thuốc kê sẽ tự động trừ tồn kho theo FEFO và tính vào hóa đơn</p>
                                </div>
                            </div>
                            <button type="button" @click="addPrescription"
                                class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-semibold px-3 py-1.5 rounded-xl text-xs transition-colors cursor-pointer">
                                <Icon name="plus" size="14" />
                                <span>Thêm loại thuốc</span>
                            </button>
                        </div>

                        <!-- Danh sách các dòng kê đơn thuốc -->
                        <div class="space-y-3.5">
                            <div v-for="(p, index) in form.prescriptions" :key="index"
                                class="p-4 bg-slate-50/90 border border-slate-200/90 rounded-2xl space-y-3.5 relative shadow-2xs transition-all hover:border-slate-300">
                                
                                <!-- HÀNG 1: CHỌN THUỐC (CHIẾM TRỌN 100% CHIỀU RỘNG - RỘNG RÃI & RÕ RÀNG TÊN THUỐC) -->
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <label class="font-semibold text-slate-700 flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-md bg-sky-600 text-white flex items-center justify-center text-[10px] font-bold shadow-xs">
                                                {{ index + 1 }}
                                            </span>
                                            <span>Thuốc điều trị <span class="text-rose-500">*</span></span>
                                        </label>
                                        
                                        <!-- Nút xóa dòng thuốc -->
                                        <button type="button" @click="removePrescription(index)"
                                            class="px-2 py-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer text-[11px] font-semibold flex items-center gap-1"
                                            title="Xóa loại thuốc này">
                                            <Icon name="trash" size="13" />
                                            <span>Xóa thuốc</span>
                                        </button>
                                    </div>
                                    
                                    <MedicineSearchSelect
                                        v-model="p.medicine_id"
                                        :medicines="medicines"
                                        @select="med => onSelectMedicine(p, med)"
                                        placeholder="Gõ tên thuốc để tìm nhanh trong kho (vd: Amox, Dexa, Bio-Rumi...)"
                                    />
                                </div>

                                <!-- HÀNG 2: LIỀU LƯỢNG, SỐ NGÀY, SỐ LƯỢNG, THÀNH TIỀN DÒNG -->
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end pt-1">
                                    <!-- Liều lượng -->
                                    <div class="sm:col-span-6 lg:col-span-5">
                                        <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Liều lượng dùng</label>
                                        <input v-model="p.dosage" type="text"
                                            class="w-full h-[38px] bg-white border border-slate-200 rounded-xl px-3 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 font-medium placeholder-slate-400 shadow-2xs"
                                            placeholder="vd: 1 viên/lần, ngày 2 lần sau ăn" />
                                    </div>

                                    <!-- Số ngày -->
                                    <div class="sm:col-span-3 lg:col-span-2">
                                        <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Số ngày uống</label>
                                        <input v-model="p.days" type="number" min="1" max="90"
                                            class="w-full h-[38px] bg-white border border-slate-200 rounded-xl px-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 tabular-nums font-semibold text-center shadow-2xs"
                                            placeholder="5" />
                                    </div>

                                    <!-- Số lượng -->
                                    <div class="sm:col-span-3 lg:col-span-2">
                                        <label class="block font-semibold text-slate-700 mb-1 text-[11px] flex items-center justify-between">
                                            <span>Số lượng <span class="text-rose-500">*</span></span>
                                            <span v-if="getMedicine(p.medicine_id)" class="text-[10px] text-slate-400 font-normal">
                                                ({{ getMedicine(p.medicine_id).unit }})
                                            </span>
                                        </label>
                                        <input v-model="p.quantity" type="number" min="1"
                                            class="w-full h-[38px] bg-white border rounded-xl px-3 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 tabular-nums font-semibold shadow-2xs"
                                            :class="getMedicine(p.medicine_id) && Number(p.quantity) > Number(getMedicine(p.medicine_id).stock_quantity) ? 'border-rose-400 bg-rose-50/40 text-rose-700' : 'border-slate-200'"
                                            placeholder="5" />
                                    </div>

                                    <!-- Thành tiền dòng -->
                                    <div class="sm:col-span-12 lg:col-span-3">
                                        <label class="block font-semibold text-slate-500 mb-1 text-[11px]">Thành tiền dòng</label>
                                        <div class="h-[38px] px-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between text-xs font-bold tabular-nums text-slate-900 shadow-2xs">
                                            <span class="text-[11px] text-slate-400 font-normal">Tạm tính:</span>
                                            <span class="text-sky-700">{{ formatCurrency((Number(p.quantity) || 0) * (Number(getMedicine(p.medicine_id)?.sell_price) || 0)) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cảnh báo nếu số lượng vượt quá tồn kho -->
                                <div v-if="getMedicine(p.medicine_id) && Number(p.quantity) > Number(getMedicine(p.medicine_id).stock_quantity)"
                                    class="p-2.5 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2 text-[11px] text-rose-700 font-medium">
                                    <Icon name="alert" size="14" class="text-rose-600 shrink-0" />
                                    <span>Cảnh báo: Số lượng kê (<strong>{{ p.quantity }} {{ getMedicine(p.medicine_id).unit }}</strong>) vượt quá tồn kho thực tế (Hiện chỉ còn <strong>{{ getMedicine(p.medicine_id).stock_quantity }} {{ getMedicine(p.medicine_id).unit }}</strong>).</span>
                                </div>

                                <!-- HÀNG 3: LỜI DẶN UỐNG THUỐC -->
                                <div class="pt-1 border-t border-slate-200/60">
                                    <input v-model="p.note" type="text"
                                        class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 shadow-2xs"
                                        placeholder="Lời dặn uống thuốc: uống sau ăn no, hòa tan vào nước, bảo quản mát..." />
                                </div>
                            </div>

                            <div v-if="form.prescriptions.length === 0" class="py-6 text-center text-slate-400 border border-dashed border-slate-200 rounded-xl">
                                <Icon name="pill" size="28" class="mx-auto text-slate-300 mb-1.5" />
                                <p class="font-semibold text-xs">Chưa có thuốc nào được kê</p>
                                <button type="button" @click="addPrescription" class="mt-2 text-xs font-semibold text-sky-600 hover:underline cursor-pointer">
                                    + Thêm thuốc điều trị
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Action Bar -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-bold px-6 py-3 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="16" />
                            <span>{{ form.processing ? 'Đang lưu phiếu khám...' : 'Hoàn tất & Sinh hóa đơn' }}</span>
                        </button>
                        <Link :href="route('medical-records.index')"
                            class="px-5 py-3 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                            Hủy bỏ
                        </Link>
                    </div>

                </form>

                <!-- Cột phụ: Tóm tắt chi phí & Lịch sử bệnh án (1 cột - Cố định đi theo khi cuộn trang) -->
                <div class="space-y-4 lg:sticky lg:top-4">
                    
                    <!-- Card Tóm tắt chi phí dự tính -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3 text-xs">
                        <h3 class="font-bold text-slate-900 uppercase tracking-wider text-xs border-b border-slate-100 pb-2 flex items-center justify-between">
                            <span>Tóm tắt chi phí dự tính</span>
                            <Icon name="invoice" size="14" class="text-sky-600" />
                        </h3>

                        <div class="space-y-2 text-slate-600">
                            <div class="flex justify-between">
                                <span>Tiền khám & Dịch vụ:</span>
                                <span class="font-semibold text-slate-900 tabular-nums">{{ formatCurrency(totalServiceCost) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tiền thuốc:</span>
                                <span class="font-semibold text-slate-900 tabular-nums">{{ formatCurrency(totalMedicineCost) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-sm text-slate-900 pt-2 border-t border-slate-100">
                                <span>TỔNG DỰ TÍNH:</span>
                                <span class="text-sky-700 tabular-nums">{{ formatCurrency(estimatedTotal) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Lịch sử bệnh án (Timeline) -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <Icon name="stethoscope" size="14" class="text-sky-600" />
                                <h3 class="font-bold text-slate-900">Tiền sử bệnh án</h3>
                            </div>
                            <span v-if="selectedPet" class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-bold tabular-nums">
                                {{ petHistory.length }} lần khám
                            </span>
                        </div>

                        <div v-if="isLoadingHistory" class="py-6 text-center text-slate-400">
                            <div class="w-6 h-6 rounded-full border-2 border-sky-600 border-t-transparent animate-spin mx-auto mb-2"></div>
                            <p class="text-[11px]">Đang tải lịch sử khám...</p>
                        </div>

                        <div v-else-if="petHistory.length > 0" class="max-h-96 overflow-y-auto space-y-3 pr-1 custom-scrollbar">
                            <div v-for="rec in petHistory" :key="rec.id"
                                class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900">{{ formatDate(rec.created_at) }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">#KB-{{ rec.id }}</span>
                                </div>
                                <p class="text-[11px] text-slate-700">
                                    <strong class="text-slate-900">Chẩn đoán:</strong> {{ rec.diagnosis }}
                                </p>
                                <p v-if="rec.doctor" class="text-[10px] text-sky-700 font-medium">
                                    BS. {{ rec.doctor.name }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="py-8 text-center text-slate-400">
                            <Icon name="pet" size="32" class="mx-auto text-slate-300 mb-1" />
                            <p class="text-xs font-semibold text-slate-600">
                                {{ selectedPet ? 'Chưa có tiền sử khám bệnh trước đó' : 'Chọn vật nuôi để xem lại lịch sử các lần khám trước' }}
                            </p>
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
            @apply="handleApplyAi" />
</template>
