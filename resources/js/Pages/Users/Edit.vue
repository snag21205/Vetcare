<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    currentRole: String,
    roles: Object,
})

const form = useForm({
    name:      props.user.name,
    email:     props.user.email,
    phone:     props.user.phone ?? '',
    password:  '',
    role:      props.currentRole,
    is_active: Boolean(props.user.is_active),
})

function submit() {
    form.put(route('users.update', props.user.id))
}
</script>

<template>
    <Head :title="`Sửa nhân viên: ${user.name}`" />

        <div class="space-y-4 pb-12">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('users.index')" class="hover:text-sky-600 transition-colors">Tài khoản nhân viên</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-600 truncate max-w-xs">{{ user.name }}</span>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Chỉnh sửa</span>
            </nav>

            <!-- Full-width 2-column responsive layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-bold text-slate-900">Chỉnh sửa Tài khoản Nhân viên</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Cập nhật thông tin cá nhân, vai trò phân quyền và trạng thái của {{ user.name }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 text-xs">

                        <!-- Họ và tên -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Họ và tên <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.name" type="text"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email & SĐT -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">
                                    Email đăng nhập <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.email" type="email"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                    :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.email }" />
                                <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Số điện thoại</label>
                                <input v-model="form.phone" type="text"
                                    class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums" />
                            </div>
                        </div>

                        <!-- Mật khẩu mới (Tùy chọn) -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Mật khẩu mới <span class="text-slate-400 font-normal">(để trống nếu không muốn đổi)</span>
                            </label>
                            <input v-model="form.password" type="password"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                                placeholder="•••••••• (Chỉ nhập khi cần đặt lại mật khẩu)" />
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
                                <span class="font-semibold text-slate-800">Tài khoản đang hoạt động (cho phép đăng nhập)</span>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="14" />
                                <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
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
                            <span>Thông tin Tài khoản</span>
                        </div>

                        <div class="space-y-2.5 text-slate-600">
                            <p><strong>Mã nhân viên:</strong> #NV-{{ user.id.toString().padStart(3, '0') }}</p>
                            <p><strong>Ngày tạo:</strong> {{ new Date(user.created_at).toLocaleDateString('vi-VN') }}</p>
                            <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">
                                Lưu ý: Nếu vô hiệu hóa tài khoản, nhân viên sẽ bị đăng xuất và không thể truy cập vào hệ thống.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
</template>
