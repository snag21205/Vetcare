<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name:                '',
    unit:                '',
    stock_quantity:      0,
    import_price:        0,
    sell_price:          0,
    expiry_date:         '',
    low_stock_threshold: 10,
})

function submit() {
    form.post(route('medicines.store'))
}
</script>

<template>
    <Head title="Thêm thuốc mới" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-4">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('medicines.index')" class="hover:text-sky-600 transition-colors">Kho thuốc</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Thêm thuốc mới</span>
            </nav>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-slate-900">Thêm thuốc / Vật tư y tế mới</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Khai báo thông tin thuốc vào danh mục kho phòng khám</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <!-- Tên thuốc -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Tên thuốc / Biệt dược <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }"
                            placeholder="Amoxicillin 250mg, Paravet, Nexgard..." />
                        <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Đơn vị tính & Tồn kho ban đầu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Đơn vị tính <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.unit" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.unit }"
                                placeholder="viên, lọ, tuýp, vỉ, ống..." />
                            <p v-if="form.errors.unit" class="text-rose-500 text-[11px] mt-1">{{ form.errors.unit }}</p>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Tồn kho ban đầu <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.stock_quantity" type="number" min="0"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.stock_quantity }" />
                            <p v-if="form.errors.stock_quantity" class="text-rose-500 text-[11px] mt-1">{{ form.errors.stock_quantity }}</p>
                        </div>
                    </div>

                    <!-- Giá nhập & Giá bán -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Giá nhập (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.import_price" type="number" min="0" step="100"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.import_price }"
                                placeholder="10000" />
                            <p v-if="form.errors.import_price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.import_price }}</p>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Giá bán (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.sell_price" type="number" min="0" step="100"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.sell_price }"
                                placeholder="15000" />
                            <p v-if="form.errors.sell_price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.sell_price }}</p>
                        </div>
                    </div>

                    <!-- Hạn sử dụng & Ngưỡng cảnh báo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Hạn sử dụng</label>
                            <input v-model="form.expiry_date" type="date"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Ngưỡng cảnh báo tồn ít <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.low_stock_threshold" type="number" min="0"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.low_stock_threshold }" />
                            <p v-if="form.errors.low_stock_threshold" class="text-rose-500 text-[11px] mt-1">{{ form.errors.low_stock_threshold }}</p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thuốc' }}</span>
                        </button>
                        <Link :href="route('medicines.index')"
                            class="px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                            Hủy
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
