<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    customer: Object,
})

const form = useForm({
    name:    props.customer.name,
    phone:   props.customer.phone,
    email:   props.customer.email ?? '',
    address: props.customer.address ?? '',
    note:    props.customer.note ?? '',
})

function submit() {
    form.put(route('customers.update', props.customer.id))
}
</script>

<template>
    <Head :title="`Sửa: ${customer.name}`" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 font-medium">
                <Link :href="route('customers.index')" class="hover:text-indigo-600 transition-colors">Khách hàng</Link>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-600 truncate max-w-xs">{{ customer.name }}</span>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-900">Chỉnh sửa</span>
            </nav>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <div class="border-b border-gray-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-gray-900">Chỉnh sửa thông tin khách hàng</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Cập nhật hồ sơ và thông tin liên hệ của {{ customer.name }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Tên khách hàng <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Số điện thoại <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.phone" type="text"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.phone }" />
                        <p v-if="form.errors.phone" class="text-rose-500 text-[11px] mt-1">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.email }" />
                        <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Địa chỉ</label>
                        <input v-model="form.address" type="text"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" />
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.note" rows="2"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"></textarea>
                    </div>

                    <div class="flex items-center gap-2.5 pt-3 border-t border-gray-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold px-5 py-2 rounded-lg text-xs shadow-sm transition-colors">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
                        </button>
                        <Link :href="route('customers.index')"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 text-xs font-medium transition-colors">
                            Hủy
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>