<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import Icon from '@/Components/Icon.vue'
import InputError from '@/Components/InputError.vue'

defineProps({
    canResetPassword: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
        default: null,
    },
})

const showPassword = ref(false)

const form = useForm({
    email: 'admin@vetcare.local',
    password: 'password',
    remember: true,
})

const demoAccounts = [
    { role: 'admin', name: 'Quản trị viên', email: 'admin@vetcare.local', icon: 'shield', color: 'sky' },
    { role: 'vet', name: 'Bác sĩ Thú y', email: 'bacsi@vetcare.local', icon: 'stethoscope', color: 'emerald' },
    { role: 'receptionist', name: 'Lễ tân tiếp đón', email: 'letan@vetcare.local', icon: 'calendar', color: 'amber' },
    { role: 'accountant', name: 'Kế toán & Quỹ', email: 'ketoan@vetcare.local', icon: 'invoice', color: 'indigo' },
]

function fillDemo(acc) {
    form.email = acc.email
    form.password = 'password'
}

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Đăng nhập hệ thống - VetCare Clinic OS" />

    <div class="min-h-screen bg-[#F8FAFC] flex flex-col justify-between selection:bg-sky-500 selection:text-white">
        <!-- Top Simple Bar -->
        <header class="h-16 px-6 sm:px-12 flex items-center justify-between border-b border-slate-200/80 bg-white/80 backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-600 flex items-center justify-center text-white shadow-xs">
                    <Icon name="pet" size="20" />
                </div>
                <div>
                    <span class="text-base font-bold text-slate-900 tracking-tight leading-tight block">VetCare</span>
                    <span class="text-[10px] text-sky-600 font-bold uppercase tracking-wider block">Clinic OS</span>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="hidden sm:inline font-medium">Hệ thống phòng khám hoạt động bình thường</span>
            </div>
        </header>

        <!-- Center Main Content -->
        <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-10">
            <div class="max-w-4xl w-full bg-white border border-slate-200/90 rounded-3xl shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[580px]">
                
                <!-- Left Column: Clinic Hero Panel (5 cols) -->
                <div class="lg:col-span-5 bg-slate-900 p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                    <!-- Subtle Decorative Background Elements -->
                    <div class="absolute -top-12 -right-12 w-48 h-48 bg-sky-600/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-emerald-600/15 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 space-y-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-sky-500/15 border border-sky-400/30 rounded-full text-[11px] font-semibold text-sky-300">
                            <Icon name="shield" size="13" />
                            <span>Hệ thống Quản lý Phòng khám Thú y</span>
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white leading-snug">
                                Chăm sóc toàn diện cho thú cưng
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-300/90 leading-relaxed">
                                Nền tảng điều hành phòng khám chuyên nghiệp: tiếp đón, khám bệnh, kê đơn thuốc, thanh toán và báo cáo doanh thu.
                            </p>
                        </div>

                        <!-- Highlights list -->
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center gap-3 text-xs text-slate-200">
                                <div class="w-6 h-6 rounded-lg bg-sky-600/30 border border-sky-500/30 flex items-center justify-center text-sky-400 shrink-0">
                                    <Icon name="stethoscope" size="13" />
                                </div>
                                <span>Hồ sơ bệnh án điện tử & AI chẩn đoán</span>
                            </div>

                            <div class="flex items-center gap-3 text-xs text-slate-200">
                                <div class="w-6 h-6 rounded-lg bg-emerald-600/30 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0">
                                    <Icon name="medicine" size="13" />
                                </div>
                                <span>Kiểm soát kho thuốc, hạn dùng & cảnh báo tồn</span>
                            </div>

                            <div class="flex items-center gap-3 text-xs text-slate-200">
                                <div class="w-6 h-6 rounded-lg bg-indigo-600/30 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shrink-0">
                                    <Icon name="invoice" size="13" />
                                </div>
                                <span>Thu ngân tự động & Báo cáo tài chính chuẩn xác</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom badge -->
                    <div class="relative z-10 pt-6 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                        <span>VetCare OS v2.5 Enterprise</span>
                        <span class="text-emerald-400 font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Bảo mật 256-bit
                        </span>
                    </div>
                </div>

                <!-- Right Column: Login Form (7 cols) -->
                <div class="lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between">
                    <div class="space-y-6">
                        <!-- Header Form -->
                        <div class="space-y-1">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                                Đăng nhập tài khoản
                            </h1>
                            <p class="text-xs text-slate-500">
                                Vui lòng nhập thông tin đăng nhập được cấp để truy cập vào hệ thống.
                            </p>
                        </div>

                        <!-- Status notification -->
                        <div v-if="status" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl font-medium">
                            {{ status }}
                        </div>

                        <!-- Quick Demo Accounts Selector (Tiện lợi để kiểm tra các vai trò) -->
                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <Icon name="user" size="12" class="text-sky-600" />
                                    Tài khoản mẫu (Nhấp để điền nhanh)
                                </span>
                                <span class="text-[10px] text-slate-400">Pass: password</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button
                                    v-for="acc in demoAccounts"
                                    :key="acc.role"
                                    type="button"
                                    @click="fillDemo(acc)"
                                    :class="[
                                        'px-2.5 py-2 rounded-xl text-[11px] font-semibold border text-left transition-all cursor-pointer flex flex-col justify-center',
                                        form.email === acc.email
                                            ? 'bg-sky-600 text-white border-sky-600 shadow-xs'
                                            : 'bg-white text-slate-700 border-slate-200 hover:border-sky-300 hover:bg-sky-50/50'
                                    ]"
                                >
                                    <span class="truncate">{{ acc.name }}</span>
                                    <span class="text-[9px] opacity-75 truncate font-normal">{{ acc.email.split('@')[0] }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Login Form -->
                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Email Field -->
                            <div class="space-y-1.5">
                                <label for="email" class="block text-xs font-semibold text-slate-700">
                                    Địa chỉ Email
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                        </svg>
                                    </div>
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="admin@vetcare.local"
                                        class="w-full pl-10 pr-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 transition-colors placeholder:text-slate-400"
                                        :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20': form.errors.email }"
                                    />
                                </div>
                                <InputError :message="form.errors.email" />
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label for="password" class="block text-xs font-semibold text-slate-700">
                                        Mật khẩu
                                    </label>
                                    <Link
                                        v-if="canResetPassword"
                                        :href="route('password.request')"
                                        class="text-[11px] font-semibold text-sky-600 hover:text-sky-800 transition-colors"
                                    >
                                        Quên mật khẩu?
                                    </Link>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        v-model="form.password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 transition-colors placeholder:text-slate-400"
                                        :class="{ 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20': form.errors.password }"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none"
                                        title="Ẩn/Hiện mật khẩu"
                                    >
                                        <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </button>
                                </div>
                                <InputError :message="form.errors.password" />
                            </div>

                            <!-- Remember me checkbox -->
                            <div class="flex items-center justify-between pt-1">
                                <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        v-model="form.remember"
                                        class="w-4 h-4 text-sky-600 border-slate-300 rounded focus:ring-sky-500 focus:ring-offset-0 transition-colors"
                                    />
                                    <span>Ghi nhớ phiên đăng nhập</span>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-700 disabled:bg-sky-400 text-white font-semibold text-xs rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span v-if="form.processing">Đang xác thực...</span>
                                    <span v-else class="flex items-center gap-1.5">
                                        <span>Đăng nhập hệ thống</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Card footer support -->
                    <div class="pt-6 mt-4 border-t border-slate-100 text-[11px] text-slate-400 text-center flex items-center justify-center gap-1.5">
                        <Icon name="shield" size="12" class="text-slate-400" />
                        <span>Hỗ trợ kỹ thuật nội bộ: support@vetcare.vn</span>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200/80 bg-white">
            <div class="flex items-center justify-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>VetCare Clinic Management System © {{ new Date().getFullYear() }} • Tất cả quyền được bảo lưu</span>
            </div>
        </footer>
    </div>
</template>
