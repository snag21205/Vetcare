<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    categories: Object,
})

const form = useForm({
    category:     'medicine_import',
    amount:       '',
    description:  '',
    voucher_date: new Date().toISOString().substring(0, 10),
})

function submit() {
    form.post(route('expense-vouchers.store'))
}
</script>

<template>
    <Head title="Lập phiếu chi mới" />

        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('expense-vouchers.index')" class="hover:text-sky-600 transition-colors">Phiếu chi</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Lập phiếu chi mới</span>
            </nav>

            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Lập phiếu chi tiền mới</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Ghi nhận chi phí mua sắm vật tư, tiền điện nước hoặc vận hành phòng khám</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 text-xs">

                        <!-- Danh mục chi -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Danh mục chi phí <span class="text-rose-500">*</span>
                            </label>
                            <select v-model="form.category"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium"
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
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.amount }"
                                    placeholder="500000" />
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

                        <!-- Lý do chi chi tiết -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Lý do chi chi tiết <span class="text-rose-500">*</span>
                            </label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.description }"
                                placeholder="Mô tả khoản chi: Tiền mua cồn y tế, băng gạc, thanh toán tiền điện tháng 8..."></textarea>
                            <p v-if="form.errors.description" class="text-rose-500 text-[11px] mt-1">{{ form.errors.description }}</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="14" />
                                <span>{{ form.processing ? 'Đang lưu...' : 'Lưu phiếu chi' }}</span>
                            </button>
                            <Link :href="route('expense-vouchers.index')"
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
                            <Icon name="document" size="16" class="text-sky-600" />
                            <span>Quản lý Sổ quỹ Chi tiêu</span>
                        </div>

                        <div class="space-y-3 text-slate-600">
                            <div class="p-3 bg-sky-50/60 rounded-xl border border-sky-100">
                                <span class="font-bold text-sky-900 block mb-0.5">Tự động trừ Lợi nhuận</span>
                                <p class="text-[11px] text-sky-700">Các khoản chi được tạo sẽ tự động tổng hợp vào báo cáo tài chính hàng tháng để tính lợi nhuận thuần.</p>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                <span class="font-bold text-slate-800 block mb-0.5">Người lập phiếu</span>
                                <p class="text-[11px] text-slate-500">Tên nhân viên đăng nhập lập phiếu chi sẽ được lưu tự động để phục vụ đối soát sổ sách.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
</template>
