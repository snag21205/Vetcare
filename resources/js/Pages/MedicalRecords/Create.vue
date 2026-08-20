<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    appointment: Object,
    pets: Array,
    medicines: Array,
})

const page = usePage()
const currentUser = computed(() => page.props.auth?.user)

const form = useForm({
    appointment_id:       props.appointment?.id ?? null,
    pet_id:               props.appointment?.pet_id ?? '',
    doctor_id:            props.appointment?.doctor_id ?? currentUser.value?.id ?? '',
    symptoms:             props.appointment?.reason ?? '',
    diagnosis:            '',
    weight_at_visit:      props.appointment?.pet?.weight ?? '',
    temperature_at_visit: '',
    note:                 '',
    status:               'completed',
    prescriptions:        [],
})

const selectedCustomerId = ref(props.appointment?.pet?.customer_id ?? '')

const filteredPets = computed(() => {
    if (!selectedCustomerId.value) return props.pets ?? []
    return (props.pets ?? []).filter(p => p.customer_id == selectedCustomerId.value)
})

const customers = computed(() => {
    const map = new Map()
    ;(props.pets ?? []).forEach(p => {
        if (p.customer && !map.has(p.customer_id)) {
            map.set(p.customer_id, p.customer)
        }
    })
    return Array.from(map.values())
})

function addPrescriptionRow() {
    form.prescriptions.push({
        medicine_id: '',
        dosage: '',
        quantity: 1,
        days: 3,
        note: '',
    })
}

function removePrescriptionRow(index) {
    form.prescriptions.splice(index, 1)
}

function getMedicineInfo(medicineId) {
    return props.medicines.find(m => m.id == medicineId)
}

const estimatedMedicineTotal = computed(() => {
    return form.prescriptions.reduce((sum, item) => {
        if (!item.medicine_id || !item.quantity) return sum
        const med = getMedicineInfo(item.medicine_id)
        return sum + (med ? Number(med.sell_price) * Number(item.quantity) : 0)
    }, 0)
})

const formatCurrency = (val) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
}

function submit() {
    form.post(route('medical-records.store'))
}
</script>

<template>
    <Head title="Tạo phiếu khám bệnh" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-4">
                <a :href="route('medical-records.index')" class="hover:text-blue-600">Phiếu khám bệnh</a>
                <span class="mx-2">/</span>
                <span>Tạo mới</span>
            </nav>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-6">🩺 Tạo phiếu khám bệnh</h2>

                <!-- Banner Lịch hẹn liên kết -->
                <div v-if="appointment" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                    <div>
                        <p class="font-medium text-blue-900 text-sm">
                            Khám theo Lịch hẹn #{{ appointment.id }}
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            Thú cưng: <span class="font-semibold">{{ appointment.pet?.name }}</span> 
                            ({{ appointment.pet?.species }}) — Chủ nhân: <span class="font-semibold">{{ appointment.pet?.customer?.name }}</span>
                        </p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-xs px-2.5 py-1 rounded-full font-medium">
                        Đã check-in
                    </span>
                </div>

                <!-- Thông báo lỗi tổng / tồn kho -->
                <div v-if="form.errors.prescriptions" class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <strong>⚠️ Lỗi:</strong> {{ form.errors.prescriptions }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Bước 1: Chọn Chủ nhân (nếu chưa có appointment) -->
                    <div v-if="!appointment">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Chủ nhân <span class="text-red-500">*</span>
                        </label>
                        <select v-model="selectedCustomerId" @change="form.pet_id = ''"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Chọn khách hàng --</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.phone }})
                            </option>
                        </select>
                    </div>

                    <!-- Bước 2: Thú cưng -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Vật nuôi <span class="text-red-500">*</span>
                        </label>
                        <select v-if="!appointment" v-model="form.pet_id"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.pet_id }">
                            <option value="">
                                {{ selectedCustomerId ? '-- Chọn vật nuôi --' : '← Chọn chủ nhân trước' }}
                            </option>
                            <option v-for="pet in filteredPets" :key="pet.id" :value="pet.id">
                                {{ pet.species === 'chó' ? '🐕' : '🐈' }} {{ pet.name }}
                            </option>
                        </select>
                        <div v-else class="px-3 py-2 bg-gray-100 border rounded-lg text-sm font-medium text-gray-800">
                            {{ appointment.pet?.species === 'chó' ? '🐕' : '🐈' }} {{ appointment.pet?.name }}
                        </div>
                        <p v-if="form.errors.pet_id" class="text-red-500 text-sm mt-1">{{ form.errors.pet_id }}</p>
                    </div>

                    <!-- Cân nặng & Nhiệt độ -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cân nặng (kg)</label>
                            <input v-model="form.weight_at_visit" type="number" step="0.1" min="0.1" max="200"
                                placeholder="VD: 4.5"
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nhiệt độ (°C)</label>
                            <input v-model="form.temperature_at_visit" type="number" step="0.1" min="30" max="45"
                                placeholder="VD: 38.5"
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>

                    <!-- Triệu chứng -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Triệu chứng lâm sàng <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="form.symptoms" rows="2"
                            placeholder="Biểu hiện của vật nuôi: nôn mửa, sốt, ho, ngứa..."
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.symptoms }"></textarea>
                        <p v-if="form.errors.symptoms" class="text-red-500 text-sm mt-1">{{ form.errors.symptoms }}</p>
                    </div>

                    <!-- Chẩn đoán -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Chẩn đoán bệnh <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="form.diagnosis" rows="2"
                            placeholder="Kết luận chẩn đoán của bác sĩ..."
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.diagnosis }"></textarea>
                        <p v-if="form.errors.diagnosis" class="text-red-500 text-sm mt-1">{{ form.errors.diagnosis }}</p>
                    </div>

                    <!-- Trạng thái & Ghi chú -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái khám</label>
                            <select v-model="form.status"
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="completed">Đã hoàn thành khám</option>
                                <option value="in_progress">Đang điều trị / Theo dõi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lời dặn / Ghi chú</label>
                            <input v-model="form.note" type="text"
                                placeholder="Dặn dò chủ nuôi..."
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>

                    <!-- Bảng Kê đơn thuốc -->
                    <div class="pt-4 border-t">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700">
                                💊 Đơn thuốc chỉ định
                            </label>
                            <button type="button" @click="addPrescriptionRow"
                                class="text-xs bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-100 font-medium">
                                + Thêm thuốc
                            </button>
                        </div>

                        <div v-if="form.prescriptions.length === 0" class="py-6 text-center bg-gray-50 rounded-lg text-gray-400 text-xs border border-dashed">
                            Chưa có thuốc trong đơn. Bấm <strong>+ Thêm thuốc</strong> nếu cần kê đơn.
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="(item, index) in form.prescriptions" :key="index"
                                class="p-3 bg-gray-50 rounded-lg border flex flex-col md:flex-row gap-3 items-start md:items-center">

                                <div class="flex-1 w-full">
                                    <select v-model="item.medicine_id"
                                        class="w-full border rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        <option value="">-- Chọn thuốc --</option>
                                        <option v-for="m in medicines" :key="m.id" :value="m.id">
                                            {{ m.name }} (Kho: {{ m.stock_quantity }} {{ m.unit }}) - {{ formatCurrency(m.sell_price) }}
                                        </option>
                                    </select>
                                </div>

                                <div class="w-full md:w-44">
                                    <input v-model="item.dosage" type="text" placeholder="Liều dùng (VD: 1 viên/ngày)"
                                        class="w-full border rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" />
                                </div>

                                <div class="w-24">
                                    <input v-model.number="item.quantity" type="number" min="1" placeholder="SL"
                                        class="w-full border rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" />
                                </div>

                                <div class="w-20">
                                    <input v-model.number="item.days" type="number" min="1" placeholder="Số ngày"
                                        class="w-full border rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" />
                                </div>

                                <button type="button" @click="removePrescriptionRow(index)"
                                    class="text-xs bg-red-100 text-red-700 px-2.5 py-1.5 rounded hover:bg-red-200"
                                    title="Xóa thuốc này">
                                    Xóa
                                </button>
                            </div>

                            <div v-if="form.prescriptions.length > 0" class="text-right text-xs text-gray-500 pt-1">
                                Tổng tiền thuốc ước tính: <strong class="text-blue-600 font-semibold">{{ formatCurrency(estimatedMedicineTotal) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4 border-t">
                        <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors text-sm font-medium">
                            {{ form.processing ? 'Đang lưu...' : 'Lưu phiếu khám' }}
                        </button>
                        <a :href="route('medical-records.index')"
                            class="px-6 py-2 rounded-lg border hover:bg-gray-50 transition-colors text-sm">Hủy</a>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
