<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
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

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-4 font-medium">
                <Link :href="route('users.index')" class="hover:text-indigo-600 transition-colors">Nhân viên</Link>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-600 truncate max-w-xs">{{ user.name }}</span>
                <Icon name="chevron-right" size="12" class="text-gray-400" />
                <span class="text-gray-900">Chỉnh sửa</span>
            </nav>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <div class="border-b border-gray-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-gray-900">Chỉnh sửa Tài khoản Nhân viên</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Cập nhật thông tin cá nhân, vai trò phân quyền và trạng thái của {{ user.name }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <!-- Họ và tên -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Họ và tên <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email & SĐT -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">
                                Email đăng nhập <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.email" type="email"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                                :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.email }" />
                            <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Số điện thoại</label>
                            <input v-model="form.phone" type="text"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono" />
                        </div>
                    </div>

                    <!-- Mật khẩu mới (Tùy chọn) -->
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">
                            Mật khẩu mới <span class="text-gray-400 font-normal">(để trống nếu không muốn đổi)</span>
                        </label>
                        <input v-model="form.password" type="password"
                            class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                            placeholder="•••••••• (Chỉ nhập khi cần đặt lại mật khẩu)" />
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
                            <span class="font-medium text-gray-800">Tài khoản đang hoạt động (cho phép đăng nhập)</span>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2.5 pt-3 border-t border-gray-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold px-5 py-2 rounded-lg text-xs shadow-sm transition-colors">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
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
