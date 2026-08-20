<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

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
    <Head :title="`Sửa: ${pet.name}`" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 font-medium">
                <Link :href="route('pets.index')" class="hover:text-indigo-600 transition-colors">Vật nuôi</Link>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-600 truncate max-w-xs">{{ pet.name }}</span>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-900">Chỉnh sửa</span>
            </nav>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <div class="border-b border-gray-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-gray-900">Chỉnh sửa thông tin vật nuôi</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Cập nhật hồ sơ bệnh án và thông tin của {{ pet.name }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <!-- Chủ nhân -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Chủ nhân <span class="text-rose-500">*</span>
                        </label>
                        <select v-model="form.customer_id"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.customer_id }">
                            <option value="">-- Chọn khách hàng --</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.phone }})
                            </option>
                        </select>
                        <p v-if="form.errors.customer_id" class="text-rose-500 text-[11px] mt-1">
                            {{ form.errors.customer_id }}
                        </p>
                    </div>

                    <!-- Tên -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Tên thú cưng <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Loài & Giống -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Loài <span class="text-rose-500">*</span>
                            </label>
                            <select v-model="form.species"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <option value="chó">Chó</option>
                                <option value="mèo">Mèo</option>
                                <option value="khác">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Giống</label>
                            <input v-model="form.breed" type="text"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" />
                        </div>
                    </div>

                    <!-- Giới tính & Ngày sinh -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Giới tính <span class="text-rose-500">*</span>
                            </label>
                            <select v-model="form.gender"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <option value="male">Đực</option>
                                <option value="female">Cái</option>
                                <option value="unknown">Chưa rõ</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Ngày sinh</label>
                            <input v-model="form.date_of_birth" type="date"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" />
                        </div>
                    </div>

                    <!-- Cân nặng -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Cân nặng (kg)</label>
                        <input v-model="form.weight" type="number" step="0.1" min="0.1" max="100"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" />
                        <p v-if="form.errors.weight" class="text-rose-500 text-[11px] mt-1">{{ form.errors.weight }}</p>
                    </div>

                    <!-- Ghi chú -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Ghi chú sức khỏe</label>
                        <textarea v-model="form.note" rows="2"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"></textarea>
                    </div>

                    <div class="flex items-center gap-2.5 pt-3 border-t border-gray-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold px-5 py-2 rounded-lg text-xs shadow-sm transition-colors">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
                        </button>
                        <Link :href="route('pets.index')"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 text-xs font-medium transition-colors">
                            Hủy
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
