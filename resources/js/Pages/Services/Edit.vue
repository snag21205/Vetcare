<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    service: Object,
})

const form = useForm({
    name:        props.service.name,
    price:       Number(props.service.price),
    description: props.service.description ?? '',
})

function submit() {
    form.put(route('services.update', props.service.id))
}
</script>

<template>
    <Head :title="`Sửa dịch vụ: ${service.name}`" />

    <AuthenticatedLayout>
        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('services.index')" class="hover:text-sky-600 transition-colors">Bảng giá dịch vụ</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-600 truncate max-w-xs">{{ service.name }}</span>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Chỉnh sửa</span>
            </nav>

            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Chỉnh sửa Dịch vụ kỹ thuật</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Cập nhật tên và đơn giá niêm yết của {{ service.name }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 text-xs">

                        <!-- Tên dịch vụ -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Tên dịch vụ kỹ thuật <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Đơn giá -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Đơn giá niêm yết (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.price" type="number" min="0" step="1000"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.price }" />
                            <p v-if="form.errors.price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.price }}</p>
                        </div>

                        <!-- Mô tả -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Mô tả / Hướng dẫn chỉ định</label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"></textarea>
                            <p v-if="form.errors.description" class="text-rose-500 text-[11px] mt-1">{{ form.errors.description }}</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="14" />
                                <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
                            </button>
                            <Link :href="route('services.index')"
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
                            <Icon name="service" size="16" class="text-sky-600" />
                            <span>Thông tin Dịch vụ</span>
                        </div>

                        <div class="space-y-2 text-slate-600">
                            <p><strong>Mã dịch vụ:</strong> #DV-{{ service.id.toString().padStart(3, '0') }}</p>
                            <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">
                                Khi cập nhật giá, đơn giá mới sẽ áp dụng cho các phiếu khám và hóa đơn tạo từ thời điểm này trở đi.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
