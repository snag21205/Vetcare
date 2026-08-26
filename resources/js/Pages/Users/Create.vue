<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    roles: Object,
})

const form = useForm({
    name:      '',
    email:     '',
    phone:     '',
    password:  '',
    role:      'receptionist',
    is_active: true,
})

function submit() {
    form.post(route('users.store'))
}
</script>

<template>
    <Head title="Thêm nhân viên mới" />

    <AuthenticatedLayout>
        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('users.index')" class="hover:text-sky-600 transition-colors">Tài khoản nhân viên</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Thêm nhân viên mới</span>
            </nav>

            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Tạo tài khoản Nhân viên</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Cấp tài khoản đăng nhập và phân quyền truy cập hệ thống phòng khám</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 text-xs">

                        <!-- Họ và tên -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Họ và tên <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }"
                                placeholder="Nguyễn Văn A, BS. Trần Minh..." />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email & SĐT -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Email đăng nhập <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.email" type="email"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.email }"
                                    placeholder="nhanvien@vetcare.vn" />
                                <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Số điện thoại</label>
                                <input v-model="form.phone" type="text"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums"
                                    placeholder="0901234567" />
                            </div>
                        </div>

                        <!-- Mật khẩu khởi tạo -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Mật khẩu khởi tạo <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.password" type="password"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.password }"
                                placeholder="•••••••• (Tối thiểu 6 ký tự)" />
                            <p v-if="form.errors.password" class="text-rose-500 text-[11px] mt-1">{{ form.errors.password }}</p>
                        </div>

                        <!-- Vai trò (Role) -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Vai trò / Phân quyền <span class="text-rose-500">*</span>
                            </label>
                            <select v-model="form.role"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.role }">
                                <option v-for="(label, key) in roles" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                            <p v-if="form.errors.role" class="text-rose-500 text-[11px] mt-1">{{ form.errors.role }}</p>
                        </div>

                        <!-- Trạng thái kích hoạt -->
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" v-model="form.is_active"
                                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
                                <span class="font-semibold text-slate-800">Kích hoạt tài khoản ngay sau khi tạo</span>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="14" />
                                <span>{{ form.processing ? 'Đang tạo...' : 'Tạo tài khoản' }}</span>
                            </button>
                            <Link :href="route('users.index')"
                                class="px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                                Hủy
                            </Link>
                        </div>

                    </form>
                </div>

                <!-- Side Information / Role Guide (1 col) -->
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3.5 text-xs">
                        <div class="flex items-center gap-2 text-slate-900 font-bold border-b border-slate-100 pb-2.5">
                            <Icon name="user" size="16" class="text-sky-600" />
                            <span>Quy định Phân quyền Vai trò</span>
                        </div>

                        <div class="space-y-3 text-slate-600">
                            <div class="p-3 bg-rose-50/60 rounded-xl border border-rose-100">
                                <span class="font-bold text-rose-900 block mb-0.5">Quản trị viên (Admin)</span>
                                <p class="text-[11px] text-rose-700">Toàn quyền kiểm soát hệ thống, quản lý kho thuốc, bảng giá, phân quyền và duyệt chi phí.</p>
                            </div>

                            <div class="p-3 bg-sky-50/60 rounded-xl border border-sky-100">
                                <span class="font-bold text-sky-900 block mb-0.5">Bác sĩ Thú y (Vet)</span>
                                <p class="text-[11px] text-sky-700">Khám bệnh, chỉ định dịch vụ, kê đơn thuốc và sử dụng trợ lý AI chẩn đoán.</p>
                            </div>

                            <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100">
                                <span class="font-bold text-emerald-900 block mb-0.5">Lễ tân (Receptionist)</span>
                                <p class="text-[11px] text-emerald-700">Tiếp đón khách hàng, đăng ký thú cưng, đặt lịch hẹn và thu ngân hóa đơn.</p>
                            </div>

                            <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-100">
                                <span class="font-bold text-amber-900 block mb-0.5">Kế toán (Accountant)</span>
                                <p class="text-[11px] text-amber-700">Theo dõi thu chi, lập phiếu chi sổ quỹ và báo cáo doanh thu tài chính.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
