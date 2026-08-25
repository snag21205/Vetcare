<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    note: '',
})

function submit() {
    form.post(route('customers.store'))
}
</script>

<template>
    <Head title="Thêm khách hàng" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-4">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('customers.index')" class="hover:text-sky-600 transition-colors">Khách hàng</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Thêm mới</span>
            </nav>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-slate-900">Thêm khách hàng mới</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Điền thông tin liên hệ của chủ nuôi vật nuôi</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <!-- Field: Tên -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Tên khách hàng <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }"
                            placeholder="Nguyễn Văn A" />
                        <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Field: SĐT -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Số điện thoại <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.phone" type="text"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.phone }"
                            placeholder="0901234567" />
                        <p v-if="form.errors.phone" class="text-rose-500 text-[11px] mt-1">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Field: Email -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Email</label>
                        <input v-model="form.email" type="email"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.email }"
                            placeholder="nguyenvana@gmail.com" />
                        <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Field: Địa chỉ -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Địa chỉ</label>
                        <input v-model="form.address" type="text"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            placeholder="Số nhà, tên đường, phường/xã..." />
                    </div>

                    <!-- Field: Ghi chú -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.note" rows="2"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            placeholder="Ghi chú thêm về khách hàng..."></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu khách hàng' }}</span>
                        </button>
                        <Link :href="route('customers.index')"
                            class="px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                            Hủy
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>