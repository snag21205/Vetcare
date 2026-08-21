<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
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

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 font-medium">
                <Link :href="route('medicines.index')" class="hover:text-indigo-600 transition-colors">Kho thuốc</Link>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-600 truncate max-w-xs">{{ medicine.name }}</span>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-900">Chỉnh sửa</span>
            </nav>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <div class="border-b border-gray-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-gray-900">Chỉnh sửa thông tin thuốc</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Cập nhật đơn giá, ngưỡng tồn kho và thông tin của {{ medicine.name }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Tên thuốc / Biệt dược <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Đơn vị tính <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.unit" type="text"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.unit }" />
                            <p v-if="form.errors.unit" class="text-rose-500 text-[11px] mt-1">{{ form.errors.unit }}</p>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Tồn kho hiện tại <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.stock_quantity" type="number" min="0"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" />
                            <p v-if="form.errors.stock_quantity" class="text-rose-500 text-[11px] mt-1">{{ form.errors.stock_quantity }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Giá nhập (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.import_price" type="number" min="0" step="100"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" />
                            <p v-if="form.errors.import_price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.import_price }}</p>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Giá bán (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.sell_price" type="number" min="0" step="100"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" />
                            <p v-if="form.errors.sell_price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.sell_price }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Hạn sử dụng</label>
                            <input v-model="form.expiry_date" type="date"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" />
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Ngưỡng cảnh báo tồn ít <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.low_stock_threshold" type="number" min="0"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 pt-3 border-t border-gray-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold px-5 py-2 rounded-lg text-xs shadow-sm transition-colors">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
                        </button>
                        <Link :href="route('medicines.index')"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 text-xs font-medium transition-colors">
                            Hủy
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
