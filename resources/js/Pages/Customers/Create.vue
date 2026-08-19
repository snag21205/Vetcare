<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    note: '',
})

function submit() {
    form.post(route('customers.store'), {
        onError: () => {
            window.scrollTo(0, 0)
        }
    })
}
</script>

<template>

    <Head title="Thêm khách hàng" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-4">
                <a :href="route('customers.index')" class="hover:text-blue-600">Khách hàng</a>
                <span class="mx-2">/</span>
                <span>Thêm mới</span>
            </nav>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold mb-6">Thêm khách hàng mới</h2>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Field: Tên -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tên khách hàng <span class="text-red-500">*</span>
                        </label>
                        <!-- v-model: two-way binding — nhập gì form.name cập nhật ngay -->
                        <input v-model="form.name" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.name }" placeholder="Nguyễn Văn A" />
                        <!-- form.errors.name: lỗi từ server tự động populate vào đây -->
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Field: SĐT -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.phone" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.phone }" placeholder="0901234567" />
                        <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <!-- Field: Email (không bắt buộc) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="khachhang@email.com (không bắt buộc)" />
                    </div>

                    <!-- Field: Địa chỉ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                        <input v-model="form.address" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Số nhà, đường, quận/huyện" />
                    </div>

                    <!-- Field: Ghi chú -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.note" rows="3"
                            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ghi chú thêm về khách hàng..."></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <!-- form.processing: true khi đang gửi request -->
                            {{ form.processing ? 'Đang lưu...' : 'Lưu khách hàng' }}
                        </button>
                        <a :href="route('customers.index')"
                            class="px-6 py-2 rounded-lg border hover:bg-gray-50 transition-colors">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>