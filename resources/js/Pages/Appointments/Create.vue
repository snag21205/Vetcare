<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    pets: Array,
    doctors: Array,
})

// Biến riêng để theo dõi chủ nhân đang chọn
// Không phải field của form — chỉ dùng để filter pets
const selectedCustomerId = ref('')

// computed: tự động tính lại khi selectedCustomerId thay đổi
// Lọc pets chỉ thuộc chủ đang chọn
const filteredPets = computed(() => {
    if (!selectedCustomerId.value) return []
    return props.pets.filter(p => p.customer_id == selectedCustomerId.value)
})

// Lấy danh sách customers unique từ pets array
// Set loại bỏ trùng lặp, sau đó convert về array
const customers = computed(() => {
    const map = new Map()
    props.pets.forEach(p => {
        if (p.customer && !map.has(p.customer_id)) {
            map.set(p.customer_id, p.customer)
        }
    })
    return Array.from(map.values())
})

const form = useForm({
    pet_id: '',
    doctor_id: '',
    scheduled_at: '',
    reason: '',
    notes: '',
})

function submit() {
    form.post(route('appointments.store'))
}
</script>

<template>

    <Head title="Đặt lịch hẹn" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <nav class="text-sm text-gray-500 mb-4">
                <a :href="route('appointments.index')" class="hover:text-blue-600">Lịch hẹn</a>
                <span class="mx-2">/</span>
                <span>Đặt lịch mới</span>
            </nav>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-6">📅 Đặt lịch hẹn mới</h2>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Bước 1: Chọn chủ nhân -->
                    <div>
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
                        <!-- Không phải field của form nên không có form.errors -->
                    </div>

                    <!-- Bước 2: Chọn thú cưng (chỉ hiện sau khi chọn chủ) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Vật nuôi <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.pet_id" :disabled="!selectedCustomerId"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
                            :class="{ 'border-red-500': form.errors.pet_id }">
                            <option value="">
                                {{ selectedCustomerId ? '-- Chọn vật nuôi --' : '← Chọn chủ nhân trước' }}
                            </option>
                            <option v-for="pet in filteredPets" :key="pet.id" :value="pet.id">
                                {{ pet.species === 'chó' ? '🐕' : '🐈' }} {{ pet.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.pet_id" class="text-red-500 text-sm mt-1">{{ form.errors.pet_id }}</p>
                    </div>

                    <!-- Ngày giờ hẹn -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Ngày giờ hẹn <span class="text-red-500">*</span>
                        </label>
                        <!-- datetime-local: input chọn cả ngày lẫn giờ -->
                        <input v-model="form.scheduled_at" type="datetime-local"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.scheduled_at }" />
                        <p v-if="form.errors.scheduled_at" class="text-red-500 text-sm mt-1">
                            {{ form.errors.scheduled_at }}
                        </p>
                    </div>

                    <!-- Lý do khám -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lý do khám <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.reason" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.reason }"
                            placeholder="Tiêm vaccine, kiểm tra sức khỏe, điều trị..." />
                        <p v-if="form.errors.reason" class="text-red-500 text-sm mt-1">{{ form.errors.reason }}</p>
                    </div>

                    <!-- Chọn bác sĩ -->
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

                    <!-- Ghi chú -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.notes" rows="2"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Dặn dò thêm..."></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Đang lưu...' : '📅 Xác nhận đặt lịch' }}
                        </button>
                        <a :href="route('appointments.index')"
                            class="px-6 py-2 rounded-lg border hover:bg-gray-50 transition-colors">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
