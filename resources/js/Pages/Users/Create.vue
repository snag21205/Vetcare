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
        <div class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 font-medium">
                <Link :href="route('users.index')" class="hover:text-indigo-600 transition-colors">Nhân viên</Link>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-900">Thêm nhân viên mới</span>
            </nav>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <div class="border-b border-gray-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-gray-900">Tạo tài khoản Nhân viên</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Cấp tài khoản đăng nhập và phân quyền truy cập hệ thống phòng khám</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <!-- Họ và tên -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Họ và tên <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }"
                            placeholder="Nguyễn Văn A, BS. Trần Minh..." />
                        <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email & SĐT -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Email đăng nhập <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.email" type="email"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.email }"
                                placeholder="nhanvien@vetcare.vn" />
                            <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Số điện thoại</label>
                            <input v-model="form.phone" type="text"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                                placeholder="0901234567" />
                        </div>
                    </div>

                    <!-- Mật khẩu khởi tạo -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Mật khẩu khởi tạo <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.password" type="password"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.password }"
                            placeholder="•••••••• (Tối thiểu 6 ký tự)" />
                        <p v-if="form.errors.password" class="text-rose-500 text-[11px] mt-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Vai trò (Role) -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Vai trò / Phân quyền <span class="text-rose-500">*</span>
                        </label>
                        <select v-model="form.role"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.role }">
                            <option v-for="(label, key) in roles" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                        <p v-if="form.errors.role" class="text-rose-500 text-[11px] mt-1">{{ form.errors.role }}</p>
                    </div>

                    <!-- Trạng thái kích hoạt -->
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <span class="font-medium text-gray-800">Kích hoạt tài khoản ngay sau khi tạo</span>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2.5 pt-3 border-t border-gray-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold px-5 py-2 rounded-lg text-xs shadow-sm transition-colors">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang tạo...' : 'Tạo tài khoản' }}</span>
                        </button>
                        <Link :href="route('users.index')"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 text-xs font-medium transition-colors">
                            Hủy
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
