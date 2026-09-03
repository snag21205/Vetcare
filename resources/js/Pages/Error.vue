<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import Icon from '@/Components/Icon.vue'

const props = defineProps({
    status: {
        type: [Number, String],
        default: 404,
    },
    message: {
        type: String,
        default: null,
    },
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user)
const role = computed(() => page.props.auth?.role)

const statusCode = computed(() => Number(props.status) || 404)

const errorData = computed(() => {
    switch (statusCode.value) {
        case 400:
            return {
                code: '400',
                title: 'Yêu cầu không hợp lệ',
                subTitle: 'Bad Request',
                description: 'Dữ liệu gửi lên máy chủ bị thiếu thông tin hoặc sai cấu trúc định dạng yêu cầu.',
                advice: 'Vui lòng kiểm tra lại biểu mẫu dữ liệu hoặc thử thao tác lại từ đầu.',
                badgeColor: 'bg-amber-50 text-amber-700 border-amber-200',
                themeColor: '#d97706',
                icon: 'alert',
            }
        case 401:
            return {
                code: '401',
                title: 'Chưa đăng nhập hệ thống',
                subTitle: 'Unauthorized',
                description: 'Phiên đăng nhập của bạn chưa được xác thực hoặc đã hết hiệu lực truy cập.',
                advice: 'Vui lòng đăng nhập tài khoản nhân viên được cấp để tiếp tục sử dụng hệ thống.',
                badgeColor: 'bg-sky-50 text-sky-700 border-sky-200',
                themeColor: '#0284c7',
                icon: 'user',
            }
        case 403:
            return {
                code: '403',
                title: 'Truy cập bị từ chối',
                subTitle: 'Access Forbidden',
                description: 'Tài khoản của bạn không được cấp quyền hạn để truy cập vào phân hệ chức năng này.',
                advice: 'Nếu đây là sự nhầm lẫn, vui lòng liên hệ Quản trị viên phòng khám để được phân quyền.',
                badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
                themeColor: '#e11d48',
                icon: 'lock',
            }
        case 404:
            return {
                code: '404',
                title: 'Không tìm thấy trang yêu cầu',
                subTitle: 'Page Not Found',
                description: 'Liên kết bạn vừa truy cập không tồn tại, đã bị đổi tên hoặc đã được gỡ bỏ khỏi hệ thống.',
                advice: 'Kiểm tra lại đường dẫn URL hoặc sử dụng các phím tắt điều hướng nhanh bên dưới.',
                badgeColor: 'bg-sky-50 text-sky-700 border-sky-200',
                themeColor: '#0284c7',
                icon: 'pet',
            }
        case 419:
            return {
                code: '419',
                title: 'Phiên làm việc đã hết hạn',
                subTitle: 'Page Expired',
                description: 'Mã token bảo mật CSRF đã hết hiệu lực do bạn không thao tác trong thời gian dài.',
                advice: 'Vui lòng tải lại trang để hệ thống tự động cấp phát phiên làm việc mới.',
                badgeColor: 'bg-amber-50 text-amber-700 border-amber-200',
                themeColor: '#d97706',
                icon: 'calendar',
            }
        case 503:
            return {
                code: '503',
                title: 'Hệ thống đang bảo trì',
                subTitle: 'Service Unavailable',
                description: 'Phòng khám đang tiến hành nâng cấp máy chủ hoặc bảo dưỡng cơ sở dữ liệu định kỳ.',
                advice: 'Quá trình bảo trì thường diễn ra trong vài phút. Xin cảm ơn sự kiên nhẫn của bạn.',
                badgeColor: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                themeColor: '#4f46e5',
                icon: 'stethoscope',
            }
        case 500:
        default:
            return {
                code: '500',
                title: 'Lỗi máy chủ nội bộ',
                subTitle: 'Internal Server Error',
                description: 'Đã xảy ra sự cố không mong muốn trong tiến trình xử lý logic trên máy chủ phòng khám.',
                advice: 'Hệ thống đã ghi lại nhật ký sự cố. Đội ngũ kỹ thuật sẽ sớm kiểm tra và khắc phục.',
                badgeColor: 'bg-rose-50 text-rose-700 border-rose-200',
                themeColor: '#e11d48',
                icon: 'alert',
            }
    }
})

const quickLinks = [
    { name: 'Bảng Tổng quan', href: '/dashboard', icon: 'dashboard', desc: 'Trang chủ điều hành' },
    { name: 'Lịch hẹn khám', href: '/appointments', icon: 'calendar', desc: 'Danh sách tiếp đón' },
    { name: 'Hồ sơ khám bệnh', href: '/medical-records', icon: 'stethoscope', desc: 'Bệnh án điện tử' },
    { name: 'Hóa đơn thu tiền', href: '/invoices', icon: 'invoice', desc: 'Thu ngân & Sổ quỹ' },
]

function goBack() {
    if (window.history.length > 1) {
        window.history.back()
    } else {
        window.location.href = authUser.value ? '/dashboard' : '/login'
    }
}

function reloadPage() {
    window.location.reload()
}
</script>

<template>
    <Head :title="`${errorData.code} - ${errorData.title} | VetCare Clinic OS`" />

    <div class="min-h-screen bg-[#F8FAFC] flex flex-col justify-between selection:bg-sky-500 selection:text-white relative overflow-hidden">
        
        <!-- Background subtle decorative grid & glows -->
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:24px_24px] opacity-40 pointer-events-none"></div>
        <div class="absolute top-1/4 -left-40 w-96 h-96 bg-sky-100/60 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/3 -right-40 w-96 h-96 bg-slate-200/50 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Header Bar -->
        <header class="h-16 px-6 sm:px-12 border-b border-slate-200/80 bg-white/90 backdrop-blur-md flex items-center justify-between relative z-20">
            <Link href="/" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-sky-600 flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition-transform">
                    <Icon name="pet" size="20" />
                </div>
                <div>
                    <span class="text-base font-bold text-slate-900 tracking-tight leading-tight block">VetCare</span>
                    <span class="text-[10px] text-sky-600 font-bold uppercase tracking-wider block">Clinic OS</span>
                </div>
            </Link>

            <div class="flex items-center gap-3">
                <div v-if="authUser" class="hidden sm:flex items-center gap-2 px-3 py-1 bg-slate-100 rounded-full text-xs text-slate-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-semibold">{{ authUser.name }}</span>
                    <span class="text-slate-400">({{ role || 'Nhân viên' }})</span>
                </div>

                <Link
                    v-if="authUser"
                    :href="route('dashboard')"
                    class="px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold transition-colors shadow-xs flex items-center gap-1.5"
                >
                    <Icon name="dashboard" size="14" />
                    <span>Về Tổng quan</span>
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold transition-colors shadow-xs flex items-center gap-1.5"
                >
                    <Icon name="user" size="14" />
                    <span>Đăng nhập</span>
                </Link>
            </div>
        </header>

        <!-- Center Main Container -->
        <main class="flex-1 flex items-center justify-center p-4 sm:p-8 lg:p-12 relative z-10">
            <div class="max-w-3xl w-full space-y-6">
                
                <!-- Main Error Card -->
                <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-10 lg:p-12 shadow-sm relative overflow-hidden">
                    
                    <!-- Large Background Code Watermark -->
                    <div class="absolute right-4 top-2 select-none pointer-events-none text-slate-100/90 font-black text-8xl sm:text-9xl tracking-tighter opacity-80 leading-none">
                        {{ errorData.code }}
                    </div>

                    <div class="relative z-10 space-y-6">
                        
                        <!-- Top Error Badge & Visual Icon -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center shadow-xs shrink-0">
                                    <Icon :name="errorData.icon" size="36" />
                                </div>
                                <div>
                                    <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider border mb-1.5', errorData.badgeColor]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        MÃ LỖI {{ errorData.code }} • {{ errorData.subTitle }}
                                    </span>
                                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                                        {{ errorData.title }}
                                    </h1>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Advice Section -->
                        <div class="space-y-3">
                            <p class="text-sm sm:text-base text-slate-700 leading-relaxed font-medium">
                                {{ errorData.description }}
                            </p>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                                {{ errorData.advice }}
                            </p>

                            <!-- Path / Error snippet if present -->
                            <div v-if="message && message !== errorData.description" class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] font-mono text-slate-600 break-all flex items-start gap-2">
                                <span class="text-slate-400 select-none">Chi tiết:</span>
                                <span>{{ message }}</span>
                            </div>
                        </div>

                        <!-- Primary Action Buttons -->
                        <div class="pt-2 flex flex-wrap items-center gap-3">
                            <button
                                @click="goBack"
                                type="button"
                                class="px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors flex items-center gap-2 cursor-pointer shadow-xs"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>Quay lại trang trước</span>
                            </button>

                            <button
                                v-if="statusCode === 419 || statusCode >= 500"
                                @click="reloadPage"
                                type="button"
                                class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-colors shadow-xs flex items-center gap-2 cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Tải lại trang</span>
                            </button>

                            <Link
                                v-if="statusCode === 401 || statusCode === 419"
                                :href="route('login')"
                                class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs transition-colors shadow-xs flex items-center gap-2"
                            >
                                <Icon name="user" size="14" />
                                <span>Đăng nhập lại</span>
                            </Link>

                            <Link
                                v-else
                                :href="authUser ? route('dashboard') : '/'"
                                class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs transition-colors shadow-xs flex items-center gap-2"
                            >
                                <Icon name="dashboard" size="14" />
                                <span>{{ authUser ? 'Về Trang Tổng quan' : 'Về Trang Chủ' }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Navigation Shortcuts (Hữu ích khi người dùng lạc đường) -->
                <div v-if="authUser" class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <Icon name="shield" size="14" class="text-sky-600" />
                            Các phân hệ điều hướng nhanh
                        </span>
                        <span class="text-[11px] text-slate-400">Truy cập tức thì</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                        <Link
                            v-for="item in quickLinks"
                            :key="item.href"
                            :href="item.href"
                            class="p-3 bg-slate-50 hover:bg-sky-50/70 border border-slate-200/80 hover:border-sky-200 rounded-xl transition-all group block text-left"
                        >
                            <div class="flex items-center gap-2 text-slate-800 font-bold text-xs group-hover:text-sky-700">
                                <Icon :name="item.icon" size="14" class="text-sky-600" />
                                <span class="truncate">{{ item.name }}</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1 truncate">{{ item.desc }}</p>
                        </Link>
                    </div>
                </div>

                <!-- Support information bar -->
                <div class="p-4 bg-slate-100/70 border border-slate-200 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Đường dây nóng hỗ trợ nội bộ phòng khám: <strong>1900 8888</strong></span>
                    </div>
                    <div class="text-[11px] text-slate-400">
                        Email IT: support@vetcare.vn
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200/80 bg-white relative z-10">
            <div class="flex items-center justify-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Hệ thống Quản lý Phòng khám Thú y VetCare © {{ new Date().getFullYear() }} • Tất cả quyền được bảo lưu</span>
            </div>
        </footer>
    </div>
</template>
