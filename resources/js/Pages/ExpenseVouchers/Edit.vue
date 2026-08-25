<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    voucher: Object,
    categories: Object,
})

const voucherDate = props.voucher.voucher_date
    ? props.voucher.voucher_date.substring(0, 10)
    : ''

const form = useForm({
    category:     props.voucher.category,
    amount:       Number(props.voucher.amount),
    description:  props.voucher.description,
    voucher_date: voucherDate,
})

function submit() {
    form.put(route('expense-vouchers.update', props.voucher.id))
}
</script>

<template>
    <Head :title="`Sửa phiếu chi #PC-${voucher.id}`" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-4">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('expense-vouchers.index')" class="hover:text-sky-600 transition-colors">Phiếu chi</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-600">#PC-{{ voucher.id.toString().padStart(4, '0') }}</span>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Chỉnh sửa</span>
            </nav>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-slate-900">Chỉnh sửa phiếu chi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Cập nhật thông tin và số tiền của phiếu chi #PC-{{ voucher.id.toString().padStart(4, '0') }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <!-- Danh mục chi -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Danh mục chi phí <span class="text-rose-500">*</span>
                        </label>
                        <select v-model="form.category"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.category }">
                            <option v-for="(label, key) in categories" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                        <p v-if="form.errors.category" class="text-rose-500 text-[11px] mt-1">{{ form.errors.category }}</p>
                    </div>

                    <!-- Số tiền chi & Ngày chi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Số tiền chi (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.amount" type="number" min="1000" step="1000"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.amount }" />
                            <p v-if="form.errors.amount" class="text-rose-500 text-[11px] mt-1">{{ form.errors.amount }}</p>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Ngày chi tiền <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.voucher_date" type="date"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.voucher_date }" />
                            <p v-if="form.errors.voucher_date" class="text-rose-500 text-[11px] mt-1">{{ form.errors.voucher_date }}</p>
                        </div>
                    </div>

                    <!-- Lý do chi -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Lý do chi chi tiết <span class="text-rose-500">*</span>
                        </label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.description }"></textarea>
                        <p v-if="form.errors.description" class="text-rose-500 text-[11px] mt-1">{{ form.errors.description }}</p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
                        </button>
                        <Link :href="route('expense-vouchers.index')"
                            class="px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                            Hủy
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
