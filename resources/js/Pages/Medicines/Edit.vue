<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    medicine: Object,
})

const expiryDate = props.medicine.expiry_date
    ? props.medicine.expiry_date.substring(0, 10)
    : ''

const form = useForm({
    name:                props.medicine.name,
    unit:                props.medicine.unit,
    stock_quantity:      props.medicine.stock_quantity,
    import_price:        Number(props.medicine.import_price),
    sell_price:          Number(props.medicine.sell_price),
    expiry_date:         expiryDate,
    low_stock_threshold: props.medicine.low_stock_threshold,
})

function submit() {
    form.put(route('medicines.update', props.medicine.id))
}
</script>

<template>
    <Head :title="`Sửa thuốc: ${medicine.name}`" />

        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('medicines.index')" class="hover:text-sky-600 transition-colors">Kho thuốc</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-600 truncate max-w-xs">{{ medicine.name }}</span>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Chỉnh sửa</span>
            </nav>

            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Chỉnh sửa thông tin thuốc</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Cập nhật đơn giá, ngưỡng tồn kho và thông tin của {{ medicine.name }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 text-xs">

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Tên thuốc / Biệt dược <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Đơn vị tính <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.unit" type="text"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.unit }" />
                                <p v-if="form.errors.unit" class="text-rose-500 text-[11px] mt-1">{{ form.errors.unit }}</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Tồn kho hiện tại <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.stock_quantity" type="number" min="0"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold" />
                                <p v-if="form.errors.stock_quantity" class="text-rose-500 text-[11px] mt-1">{{ form.errors.stock_quantity }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Giá nhập (VNĐ) <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.import_price" type="number" min="0" step="100"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold" />
                                <p v-if="form.errors.import_price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.import_price }}</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Giá bán (VNĐ) <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.sell_price" type="number" min="0" step="100"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold" />
                                <p v-if="form.errors.sell_price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.sell_price }}</p>
                            </div>
                        </div>

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
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold" />
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="14" />
                                <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
                            </button>
                            <Link :href="route('medicines.index')"
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
                            <Icon name="medicine" size="16" class="text-sky-600" />
                            <span>Thông tin Dược phẩm</span>
                        </div>

                        <div class="space-y-2 text-slate-600">
                            <p><strong>Mã thuốc:</strong> #MED-{{ medicine.id.toString().padStart(4, '0') }}</p>
                            <p><strong>Ngày cập nhật:</strong> {{ new Date(medicine.updated_at).toLocaleDateString('vi-VN') }}</p>
                            <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">
                                Nếu nhập thêm lô hàng mới, bạn nên dùng chức năng "Nhập kho" ngoài trang danh sách để quản lý đa lô theo hạn sử dụng.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
</template>
