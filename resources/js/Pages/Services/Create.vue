<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name:        '',
    price:       '',
    description: '',
})

function submit() {
    form.post(route('services.store'))
}
</script>

<template>
    <Head title="Thêm dịch vụ mới" />

    <AuthenticatedLayout>
        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('services.index')" class="hover:text-sky-600 transition-colors">Bảng giá dịch vụ</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Thêm dịch vụ</span>
            </nav>

            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Thêm Dịch vụ / Thủ thuật mới</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Khai báo dịch vụ kỹ thuật và đơn giá niêm yết của phòng khám</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 text-xs">

                        <!-- Tên dịch vụ -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Tên dịch vụ kỹ thuật <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }"
                                placeholder="Xét nghiệm sinh hóa máu, Siêu âm màu ổ bụng, Tiêm vaccine dại..." />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Đơn giá -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Đơn giá niêm yết (VNĐ) <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.price" type="number" min="0" step="1000"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.price }"
                                placeholder="150000" />
                            <p v-if="form.errors.price" class="text-rose-500 text-[11px] mt-1">{{ form.errors.price }}</p>
                        </div>

                        <!-- Mô tả -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Mô tả / Hướng dẫn chỉ định</label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                placeholder="Ghi chú chi tiết về quy trình kỹ thuật, thời gian trả kết quả..."></textarea>
                            <p v-if="form.errors.description" class="text-rose-500 text-[11px] mt-1">{{ form.errors.description }}</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="14" />
                                <span>{{ form.processing ? 'Đang lưu...' : 'Lưu dịch vụ' }}</span>
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
                            <span>Lưu ý về Dịch vụ Kỹ thuật</span>
                        </div>

                        <div class="space-y-3 text-slate-600">
                            <div class="p-3 bg-sky-50/60 rounded-xl border border-sky-100">
                                <span class="font-bold text-sky-900 block mb-0.5">Tự động tích hợp Bệnh án & AI</span>
                                <p class="text-[11px] text-sky-700">Các dịch vụ được thêm vào sẽ xuất hiện ngay trong danh sách chọn thủ thuật khi bác sĩ lập phiếu khám và được AI gợi ý chẩn đoán.</p>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                <span class="font-bold text-slate-800 block mb-0.5">Đơn giá niêm yết</span>
                                <p class="text-[11px] text-slate-500">Đơn giá này sẽ là giá mặc định khi sinh hóa đơn thanh toán cho khách hàng.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
