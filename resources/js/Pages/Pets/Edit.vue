<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    pet: Object,
    customers: Array,
})

const form = useForm({
    customer_id:   props.pet.customer_id,
    name:          props.pet.name,
    species:       props.pet.species,
    breed:         props.pet.breed ?? '',
    gender:        props.pet.gender,
    date_of_birth: props.pet.date_of_birth ?? '',
    weight:        props.pet.weight ?? '',
    note:          props.pet.note ?? '',
})

function submit() {
    form.put(route('pets.update', props.pet.id))
}
</script>

<template>
    <Head title="Sửa vật nuôi" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <nav class="text-sm text-gray-500 mb-4">
                <a :href="route('pets.index')" class="hover:text-blue-600">Vật nuôi</a>
                <span class="mx-2">/</span>
                <span>{{ pet.name }}</span>
                <span class="mx-2">/</span>
                <span>Sửa</span>
            </nav>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-6">Sửa thông tin: {{ pet.name }}</h2>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Chủ nhân -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Chủ nhân <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.customer_id"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.customer_id }">
                            <option value="">-- Chọn khách hàng --</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.phone }})
                            </option>
                        </select>
                        <p v-if="form.errors.customer_id" class="text-red-500 text-sm mt-1">
                            {{ form.errors.customer_id }}
                        </p>
                    </div>

                    <!-- Tên -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tên thú cưng <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Loài & Giống -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Loài <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.species"
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="chó">🐕 Chó</option>
                                <option value="mèo">🐈 Mèo</option>
                                <option value="khác">🐾 Khác</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Giống</label>
                            <input v-model="form.breed" type="text"
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>

                    <!-- Giới tính & Ngày sinh -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Giới tính <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.gender"
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="male">♂ Đực</option>
                                <option value="female">♀ Cái</option>
                                <option value="unknown">Chưa rõ</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày sinh</label>
                            <input v-model="form.date_of_birth" type="date"
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                    </div>

                    <!-- Cân nặng -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cân nặng (kg)</label>
                        <input v-model="form.weight" type="number" step="0.1" min="0.1" max="100"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.weight" class="text-red-500 text-sm mt-1">{{ form.errors.weight }}</p>
                    </div>

                    <!-- Ghi chú -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.note" rows="3"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Đang lưu...' : 'Cập nhật' }}
                        </button>
                        <a :href="route('pets.index')"
                            class="px-6 py-2 rounded-lg border hover:bg-gray-50 transition-colors">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
