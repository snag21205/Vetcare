<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    customer: Object,
})

const form = useForm({
    name: props.customer.name,
    phone: props.customer.phone,
    email: props.customer.email ?? '',
    address: props.customer.address ?? '',
    note: props.customer.note ?? '',
})

function submit() {
    form.put(route('customers.update', props.customer.id))
}
</script>

<template>

    <Head title="Sửa khách hàng" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <nav class="text-sm text-gray-500 mb-4">
                <a :href="route('customers.index')" class="hover:text-blue-600">Khách hàng</a>
                <span class="mx-2">/</span>
                <span>{{ customer.name }}</span>
                <span class="mx-2">/</span>
                <span>Sửa</span>
            </nav>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-6">Sửa thông tin: {{ customer.name }}</h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tên khách hàng <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.phone" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.phone }" />
                        <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                        <input v-model="form.address" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

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
                        <a :href="route('customers.index')"
                            class="px-6 py-2 rounded-lg border hover:bg-gray-50 transition-colors">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>