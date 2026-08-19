<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    appointment: Object,
    pets: Array,
    doctors: Array,
    statuses: Object,
})

// Pre-fill customer từ appointment hiện tại
const selectedCustomerId = ref(props.appointment.pet?.customer_id ?? '')

const filteredPets = computed(() => {
    if (!selectedCustomerId.value) return props.pets
    return props.pets.filter(p => p.customer_id == selectedCustomerId.value)
})

const customers = computed(() => {
    const map = new Map()
    props.pets.forEach(p => {
        if (p.customer && !map.has(p.customer_id)) {
            map.set(p.customer_id, p.customer)
        }
    })
    return Array.from(map.values())
})

// Format datetime-local: cần format 'YYYY-MM-DDTHH:mm'
const scheduledAt = props.appointment.scheduled_at
    ? props.appointment.scheduled_at.replace(' ', 'T').substring(0, 16)
    : ''

const form = useForm({
    pet_id: props.appointment.pet_id,
    doctor_id: props.appointment.doctor_id ?? '',
    scheduled_at: scheduledAt,
    reason: props.appointment.reason,
    status: props.appointment.status,
    notes: props.appointment.notes ?? '',
})

function submit() {
    form.put(route('appointments.update', props.appointment.id))
}
</script>

<template>

    <Head title="Sửa lịch hẹn" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <nav class="text-sm text-gray-500 mb-4">
                <a :href="route('appointments.index')" class="hover:text-blue-600">Lịch hẹn</a>
                <span class="mx-2">/</span>
                <span>Sửa lịch hẹn</span>
            </nav>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-6">Sửa lịch hẹn</h2>

                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chủ nhân</label>
                        <select v-model="selectedCustomerId" @change="form.pet_id = ''"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tất cả --</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Vật nuôi <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.pet_id"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.pet_id }">
                            <option v-for="pet in filteredPets" :key="pet.id" :value="pet.id">
                                {{ pet.name }} ({{ pet.customer?.name }})
                            </option>
                        </select>
                        <p v-if="form.errors.pet_id" class="text-red-500 text-sm mt-1">{{ form.errors.pet_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Ngày giờ hẹn <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.scheduled_at" type="datetime-local"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.scheduled_at" class="text-red-500 text-sm mt-1">{{ form.errors.scheduled_at
                            }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lý do khám <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.reason" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.reason" class="text-red-500 text-sm mt-1">{{ form.errors.reason }}</p>
                    </div>

                    <!-- Trạng thái — chỉ admin/receptionist mới được đổi thủ công -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select v-model="form.status"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option v-for="(label, key) in statuses" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bác sĩ phụ trách</label>
                        <select v-model="form.doctor_id"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Chưa chỉ định --</option>
                            <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                                BS. {{ doc.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.notes" rows="2"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Đang lưu...' : 'Cập nhật' }}
                        </button>
                        <a :href="route('appointments.index')"
                            class="px-6 py-2 rounded-lg border hover:bg-gray-50 transition-colors">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
