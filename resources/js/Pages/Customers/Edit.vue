<script setup>
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

        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('customers.index')" class="hover:text-sky-600 transition-colors">Khách hàng</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-600 truncate max-w-xs">{{ customer.name }}</span>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Chỉnh sửa</span>
            </nav>

            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Chỉnh sửa thông tin khách hàng</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Cập nhật hồ sơ và thông tin liên hệ của {{ customer.name }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 text-xs">

                        <!-- Tên -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Tên khách hàng <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- SĐT & Email -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Số điện thoại <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.phone" type="text"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.phone }" />
                                <p v-if="form.errors.phone" class="text-rose-500 text-[11px] mt-1">{{ form.errors.phone }}</p>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Email</label>
                                <input v-model="form.email" type="email"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.email }" />
                                <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <!-- Địa chỉ -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Địa chỉ</label>
                            <input v-model="form.address" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                        </div>

                        <!-- Ghi chú -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Ghi chú</label>
                            <textarea v-model="form.note" rows="2"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="14" />
                                <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
                            </button>
                            <Link :href="route('customers.index')"
                                class="px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                                Hủy
                            </Link>
                        </div>

                    </form>
                </div>

                <!-- Side Information Card (1 col) -->
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3.5 text-xs">
                        <div class="flex items-center gap-2 text-slate-900 font-bold border-b border-slate-100 pb-2.5">
                            <Icon name="users" size="16" class="text-sky-600" />
                            <span>Thông tin Khách hàng</span>
                        </div>

                        <div class="space-y-2 text-slate-600">
                            <p><strong>Mã khách hàng:</strong> #KH-{{ customer.id.toString().padStart(3, '0') }}</p>
                            <p><strong>Ngày tham gia:</strong> {{ new Date(customer.created_at).toLocaleDateString('vi-VN') }}</p>
                            <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">
                                Mọi thay đổi về số điện thoại và email sẽ được tự động cập nhật vào các hóa đơn và lịch hẹn sắp tới.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
</template>