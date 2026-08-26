<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    customers: Array,
})

const form = useForm({
    customer_id:   '',
    name:          '',
    species:       '',
    breed:         '',
    gender:        '',
    date_of_birth: '',
    weight:        '',
    note:          '',
})

function submit() {
    form.post(route('pets.store'))
}
</script>

<template>
    <Head title="Thêm vật nuôi" />

    <AuthenticatedLayout>
        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('pets.index')" class="hover:text-sky-600 transition-colors">Vật nuôi</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Thêm vật nuôi mới</span>
            </nav>

            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Thêm vật nuôi mới</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Đăng ký thông tin hồ sơ thú cưng cho khách hàng</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 text-xs">

                        <!-- Chủ nhân -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Chủ nhân (Khách hàng) <span class="text-rose-500">*</span>
                            </label>
                            <select v-model="form.customer_id"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.customer_id }">
                                <option value="">-- Chọn khách hàng --</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">
                                    {{ c.name }} ({{ c.phone }})
                                </option>
                            </select>
                            <p v-if="form.errors.customer_id" class="text-rose-500 text-[11px] mt-1">
                                {{ form.errors.customer_id }}
                            </p>
                        </div>

                        <!-- Tên thú cưng -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Tên thú cưng <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }"
                                placeholder="Mimi, Cún, Béo..." />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Loài & Giống -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Loài <span class="text-rose-500">*</span>
                                </label>
                                <select v-model="form.species"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.species }">
                                    <option value="">-- Chọn loài --</option>
                                    <option value="chó">Chó</option>
                                    <option value="mèo">Mèo</option>
                                    <option value="khác">Khác</option>
                                </select>
                                <p v-if="form.errors.species" class="text-rose-500 text-[11px] mt-1">{{ form.errors.species }}</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Giống loài</label>
                                <input v-model="form.breed" type="text"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                    placeholder="Poodle, Mèo Anh, Pug, Golden..." />
                            </div>
                        </div>

                        <!-- Giới tính & Ngày sinh -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Giới tính <span class="text-rose-500">*</span>
                                </label>
                                <select v-model="form.gender"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.gender }">
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="male">Đực</option>
                                    <option value="female">Cái</option>
                                    <option value="unknown">Chưa rõ</option>
                                </select>
                                <p v-if="form.errors.gender" class="text-rose-500 text-[11px] mt-1">{{ form.errors.gender }}</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Ngày sinh</label>
                                <input v-model="form.date_of_birth" type="date"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                            </div>
                        </div>

                        <!-- Cân nặng -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Cân nặng hiện tại (kg)</label>
                            <input v-model="form.weight" type="number" step="0.1" min="0.1" max="100"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                                placeholder="3.5" />
                            <p v-if="form.errors.weight" class="text-rose-500 text-[11px] mt-1">{{ form.errors.weight }}</p>
                        </div>

                        <!-- Ghi chú -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Ghi chú sức khỏe ban đầu</label>
                            <textarea v-model="form.note" rows="2"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                placeholder="Dị ứng thức ăn, đã tiêm phòng mũi 1, tiền sử bệnh án..."></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="14" />
                                <span>{{ form.processing ? 'Đang lưu...' : 'Lưu vật nuôi' }}</span>
                            </button>
                            <Link :href="route('pets.index')"
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
                            <Icon name="pet" size="16" class="text-sky-600" />
                            <span>Hồ sơ Bệnh án Điện tử</span>
                        </div>

                        <div class="space-y-3 text-slate-600">
                            <div class="p-3 bg-sky-50/60 rounded-xl border border-sky-100">
                                <span class="font-bold text-sky-900 block mb-0.5">Theo dõi lịch sử khám</span>
                                <p class="text-[11px] text-sky-700">Mỗi khi vật nuôi đến khám, bác sĩ có thể xem lại toàn bộ tiền sử bệnh án, cân nặng và các đơn thuốc đã kê trước đó.</p>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                                <span class="font-bold text-slate-800 block mb-0.5">Đặt lịch khám nhanh</span>
                                <p class="text-[11px] text-slate-500">Sau khi đăng ký vật nuôi, lễ tân có thể chọn thú cưng để xếp lịch hẹn khám định kỳ hoặc tiêm phòng.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
