<script setup>
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { useAuth } from '@/Composables/useAuth'

const { user, isAdmin, isVet, isReceptionist } = useAuth()

const props = defineProps({
    appointments: Array,
    doctors: {
        type: Array,
        default: () => [],
    },
    filters: Object,
    statuses: Object,
})

const date = ref(props.filters.date ?? new Date().toISOString().split('T')[0])
const status = ref(props.filters.status ?? '')
const doctorId = ref(props.filters.doctor_id ?? '')

// Kiểm tra quyền được khám ca này
function canExamine(apt) {
    if (apt.status !== 'checked_in' || apt.medical_record) return false
    // Admin có toàn quyền khám bất kỳ ca nào
    if (isAdmin.value) return true
    // Bác sĩ chỉ được khám ca của chính mình hoặc ca chưa chỉ định bác sĩ
    if (isVet.value) {
        if (!apt.doctor_id) return true
        return Number(apt.doctor_id) === Number(user.value?.id)
    }
    return false
}

watch([date, status, doctorId], ([newDate, newStatus, newDoc]) => {
    router.get(route('appointments.index'), {
        date: newDate,
        status: newStatus,
        doctor_id: newDoc,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['appointments', 'filters'],
    })
})

function changeDay(delta) {
    const d = new Date(date.value)
    d.setDate(d.getDate() + delta)
    date.value = d.toISOString().split('T')[0]
}

// Modal Check-in state & handlers
const checkInModalOpen = ref(false)
const selectedApt = ref(null)
const checkInDoctorId = ref('')
const checkInNote = ref('')
const isCheckingIn = ref(false)
const checkInError = ref('')

function openCheckInModal(apt) {
    selectedApt.value = apt
    checkInDoctorId.value = apt.doctor_id ?? ''
    checkInNote.value = ''
    checkInError.value = ''
    checkInModalOpen.value = true
}

function closeCheckInModal() {
    checkInModalOpen.value = false
    selectedApt.value = null
    checkInError.value = ''
}

function submitCheckIn() {
    if (!checkInDoctorId.value) {
        checkInError.value = 'Vui lòng chỉ định Bác sĩ tiếp nhận ca khám này!'
        return
    }
    checkInError.value = ''
    isCheckingIn.value = true

    router.post(route('appointments.check-in', selectedApt.value.id), {
        doctor_id: checkInDoctorId.value,
        checkin_note: checkInNote.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            closeCheckInModal()
        },
        onFinish: () => {
            isCheckingIn.value = false
        }
    })
}

function confirmAppointment(id) {
    router.post(route('appointments.confirm', id), {}, {
        preserveScroll: true,
        preserveState: true,
    })
}

function cancelAppointment(id) {
    const reason = prompt('Lý do hủy lịch hẹn (tùy chọn):')
    if (reason === null) return
    router.delete(route('appointments.destroy', id), {
        preserveScroll: true,
        preserveState: true,
    })
}

const statusConfig = {
    pending: { label: 'Chờ xác nhận', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    confirmed: { label: 'Đã xác nhận', class: 'bg-sky-50 text-sky-700 border-sky-200' },
    checked_in: { label: 'Đã check-in', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    completed: { label: 'Hoàn thành', class: 'bg-slate-100 text-slate-600 border-slate-200' },
    cancelled: { label: 'Đã hủy', class: 'bg-rose-50 text-rose-700 border-rose-200' },
    no_show: { label: 'Không đến', class: 'bg-orange-50 text-orange-700 border-orange-200' },
}
</script>

<template>
    <Head title="Lịch hẹn" />

    <div class="space-y-4">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Quản lý Lịch hẹn & Tiếp đón</h1>
                <p class="text-xs text-slate-500 mt-0.5">Theo dõi lịch khám bệnh, phân công bác sĩ và điều phối tiếp đón bệnh nhân</p>
            </div>
            <!-- Chỉ Lễ tân & Admin được tạo lịch hẹn mới -->
            <Link v-if="isReceptionist || isAdmin" :href="route('appointments.create')"
                class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0 cursor-pointer">
                <Icon name="plus" size="16" />
                <span>Đặt lịch hẹn</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-xs text-xs">
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Điều hướng ngày -->
                <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50/60 shadow-2xs">
                    <button @click="changeDay(-1)"
                        class="px-2.5 py-2 hover:bg-slate-100 text-slate-600 border-r border-slate-200 transition-colors cursor-pointer"
                        title="Hôm trước">
                        <Icon name="chevron-left" size="14" />
                    </button>

                    <input type="date" v-model="date"
                        class="px-3 py-1.5 text-xs text-slate-900 font-semibold border-none focus:outline-none focus:ring-0 bg-transparent" />

                    <button @click="changeDay(1)"
                        class="px-2.5 py-2 hover:bg-slate-100 text-slate-600 border-l border-slate-200 transition-colors cursor-pointer"
                        title="Hôm sau">
                        <Icon name="chevron-right" size="14" />
                    </button>
                </div>

                <button @click="date = new Date().toISOString().split('T')[0]"
                    class="px-3.5 py-2 bg-slate-100 text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-200 font-semibold transition-colors cursor-pointer">
                    Hôm nay
                </button>

                <!-- Lọc trạng thái -->
                <select v-model="status"
                    class="border border-slate-200 rounded-xl pr-9 px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium">
                    <option value="">Tất cả trạng thái</option>
                    <option v-for="(label, key) in statuses" :key="key" :value="key">
                        {{ label }}
                    </option>
                </select>

                <!-- Nút chuyển nhanh: Ca của tôi vs Tất cả (Chỉ cho Bác sĩ) -->
                <div v-if="isVet" class="inline-flex rounded-xl border border-slate-200 p-0.5 bg-slate-50/80 shrink-0">
                    <button type="button" @click="doctorId = ''"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer"
                        :class="!doctorId ? 'bg-white text-sky-700 shadow-xs' : 'text-slate-500 hover:text-slate-700'">
                        Tất cả bác sĩ
                    </button>
                    <button type="button" @click="doctorId = String(user?.id)"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer"
                        :class="doctorId == user?.id ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-700'">
                        Ca của tôi
                    </button>
                </div>

                <!-- Lọc bác sĩ phụ trách -->
                <select v-model="doctorId"
                    class="border border-slate-200 rounded-xl pr-9 px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium">
                    <option value="">Tất cả bác sĩ</option>
                    <option v-for="d in doctors" :key="d.id" :value="d.id">
                        BS. {{ d.name }} {{ (isVet && d.id === user?.id) ? '(Tôi)' : '' }}
                    </option>
                </select>
            </div>

            <span class="text-xs text-slate-500 font-bold tabular-nums">
                {{ appointments.length }} lịch hẹn
            </span>
        </div>

        <!-- Danh sách lịch hẹn (Cuộn nội bộ bên trong) -->
        <div class="space-y-3 max-h-[calc(100vh-250px)] min-h-[380px] overflow-y-auto pr-1">

            <!-- Empty state -->
            <div v-if="appointments.length === 0"
                class="bg-white rounded-2xl border border-slate-200 shadow-xs p-12 text-center text-slate-400">
                <Icon name="calendar" size="40" class="mx-auto text-slate-300 mb-2" />
                <p class="text-xs font-semibold text-slate-600">Không có lịch hẹn nào trong ngày đã chọn</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Chọn ngày khác hoặc tạo lịch hẹn mới cho khách hàng.</p>
            </div>

            <!-- Card từng lịch hẹn -->
            <div v-for="apt in appointments" :key="apt.id"
                class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-slate-300 transition-all">

                <div class="flex items-start gap-4">
                    <!-- Giờ hẹn -->
                    <div class="w-16 h-14 rounded-xl bg-sky-50/60 border border-sky-100 flex flex-col items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-sky-700 tabular-nums">
                            {{ new Date(apt.scheduled_at).toLocaleTimeString('vi-VN', {
                                hour: '2-digit',
                                minute: '2-digit'
                            }) }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">Giờ hẹn</span>
                    </div>

                    <!-- Thông tin thú cưng & chủ -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-900 text-sm">{{ apt.pet?.name }}</span>
                            <span class="text-xs text-slate-500 font-normal">({{ apt.pet?.species }})</span>
                            <span class="text-xs text-slate-300">·</span>
                            <span class="text-xs text-slate-700 font-semibold">{{ apt.pet?.customer?.name }}</span>
                            <span class="text-xs text-slate-400 tabular-nums">({{ apt.pet?.customer?.phone }})</span>
                        </div>
                        <p class="text-xs text-slate-600">{{ apt.reason }}</p>

                        <!-- Bác sĩ phụ trách -->
                        <p v-if="apt.doctor" class="text-[11px] font-semibold flex items-center gap-1.5"
                            :class="isVet && apt.doctor_id == user?.id ? 'text-emerald-700' : 'text-sky-700'">
                            <Icon name="stethoscope" size="13" />
                            <span>BS. {{ apt.doctor.name }}</span>
                            <span v-if="isVet && apt.doctor_id == user?.id"
                                class="text-[9px] bg-emerald-100 text-emerald-800 border border-emerald-200 px-1.5 py-0.2 rounded-md font-bold uppercase">
                                Của tôi
                            </span>
                        </p>
                        <p v-else class="text-[11px] text-amber-600 font-medium flex items-center gap-1">
                            <Icon name="alert" size="13" />
                            <span>Chưa chỉ định bác sĩ</span>
                        </p>
                    </div>
                </div>

                <!-- Trạng thái & Action Buttons -->
                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    <!-- Badges -->
                    <div class="flex items-center gap-1.5 flex-wrap sm:flex-nowrap">
                        <span :class="statusConfig[apt.status]?.class"
                            class="whitespace-nowrap inline-flex items-center justify-center text-[11px] px-2.5 py-0.5 rounded-full font-semibold border shrink-0">
                            {{ statusConfig[apt.status]?.label }}
                        </span>
                        <span v-if="apt.medical_record && apt.medical_record.status === 'in_progress'"
                            class="whitespace-nowrap inline-flex items-center justify-center text-[11px] bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full font-semibold shrink-0">
                            Đang khám
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1.5">
                        <!-- Xác nhận lịch (Lễ tân & Admin) -->
                        <button v-if="apt.status === 'pending' && (isReceptionist || isAdmin)" @click="confirmAppointment(apt.id)"
                            class="inline-flex items-center gap-1 text-xs bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 px-3 py-1.5 rounded-xl font-semibold transition-colors cursor-pointer">
                            <Icon name="check" size="13" />
                            <span>Xác nhận</span>
                        </button>

                        <!-- Check-in tiếp nhận (Lễ tân & Admin: Mở modal chọn/đổi Bác sĩ) -->
                        <button v-if="['pending', 'confirmed'].includes(apt.status) && (isReceptionist || isAdmin)" @click="openCheckInModal(apt)"
                            class="inline-flex items-center gap-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="13" />
                            <span>Check-in</span>
                        </button>

                        <!-- Khám bệnh: CHỈ BÁC SĨ ĐƯỢC PHÂN CÔNG (HOẶC ADMIN) MỚI CÓ QUYỀN KHÁM -->
                        <Link v-if="canExamine(apt)"
                            :href="route('medical-records.create', { appointment_id: apt.id })"
                            class="inline-flex items-center gap-1 text-xs bg-sky-600 hover:bg-sky-700 text-white px-3.5 py-1.5 rounded-xl font-semibold shadow-xs transition-colors cursor-pointer"
                            title="Tiến hành lập phiếu khám và kê đơn">
                            <Icon name="stethoscope" size="13" />
                            <span>Khám bệnh</span>
                        </Link>

                        <!-- Nhãn hiển thị cho Bác sĩ khác khi ca này đã giao cho đồng nghiệp -->
                        <span v-else-if="apt.status === 'checked_in' && !apt.medical_record && isVet && apt.doctor_id && apt.doctor_id != user?.id"
                            class="inline-flex items-center gap-1 text-[11px] bg-slate-50 text-slate-500 border border-slate-200 px-2.5 py-1 rounded-xl font-medium"
                            :title="`Ca khám này do BS. ${apt.doctor?.name ?? 'khác'} phụ trách`">
                            <Icon name="clock" size="12" class="text-slate-400" />
                            <span>Chờ BS. {{ apt.doctor?.name }}</span>
                        </span>

                        <!-- Xem bệnh án & Hóa đơn (Tất cả vai trò có quyền xem) -->
                        <template v-if="apt.medical_record">
                            <Link :href="route('medical-records.show', apt.medical_record.id)"
                                class="inline-flex items-center gap-1 text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 px-2.5 py-1.5 rounded-xl font-semibold transition-colors">
                                <Icon name="document" size="13" />
                                <span>Bệnh án</span>
                            </Link>
                            <Link v-if="apt.medical_record.invoice"
                                :href="route('invoices.show', apt.medical_record.invoice.id)"
                                class="inline-flex items-center gap-1 text-[11px] bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 px-2.5 py-1.5 rounded-xl font-semibold transition-colors">
                                <Icon name="invoice" size="13" />
                                <span>Hóa đơn</span>
                            </Link>
                        </template>

                        <!-- Sửa lịch (Chỉ Lễ tân & Admin) -->
                        <Link v-if="!['completed', 'cancelled'].includes(apt.status) && (isReceptionist || isAdmin)"
                            :href="route('appointments.edit', apt.id)"
                            class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                            title="Chỉnh sửa">
                            <Icon name="edit" size="14" />
                        </Link>

                        <!-- Hủy lịch (Chỉ Lễ tân & Admin) -->
                        <button v-if="!['completed', 'cancelled', 'no_show'].includes(apt.status) && (isReceptionist || isAdmin)"
                            @click="cancelAppointment(apt.id)"
                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                            title="Hủy lịch hẹn">
                            <Icon name="x" size="14" />
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ================= MODAL CHECK-IN & ĐIỀU PHỐI BÁC SĨ ================= -->
    <div v-if="checkInModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs animate-in fade-in duration-150">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden animate-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center gap-2 font-bold text-slate-900 text-sm">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <Icon name="check" size="15" />
                    </div>
                    <span>Tiếp nhận & Check-in Ca khám</span>
                </div>
                <button @click="closeCheckInModal" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                    <Icon name="x" size="16" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 space-y-4 text-xs">
                <!-- Tóm tắt ca khám -->
                <div class="bg-sky-50/50 border border-sky-100 rounded-xl p-3.5 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 text-sm">{{ selectedApt?.pet?.name }}</span>
                        <span class="text-sky-700 font-semibold text-[11px] bg-sky-100/70 px-2 py-0.5 rounded-md">
                            {{ selectedApt?.pet?.species }}
                        </span>
                    </div>
                    <p class="text-slate-600">
                        Chủ nhân: <span class="font-semibold text-slate-800">{{ selectedApt?.pet?.customer?.name }}</span> 
                        ({{ selectedApt?.pet?.customer?.phone }})
                    </p>
                    <p class="text-slate-500 text-[11px] flex items-center gap-1">
                        <Icon name="calendar" size="12" />
                        <span>Lý do: {{ selectedApt?.reason }}</span>
                    </p>
                </div>

                <!-- Lựa chọn Bác sĩ khám -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Bác sĩ tiếp nhận khám <span class="text-rose-500">*</span>
                    </label>
                    <select v-model="checkInDoctorId"
                        class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold"
                        :class="{ 'border-rose-400 ring-1 ring-rose-400': checkInError }">
                        <option value="">-- Chọn Bác sĩ phụ trách --</option>
                        <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                            BS. {{ doc.name }}
                        </option>
                    </select>
                    <p v-if="checkInError" class="text-rose-600 text-[11px] font-semibold mt-1 flex items-center gap-1">
                        <Icon name="alert" size="12" />
                        <span>{{ checkInError }}</span>
                    </p>
                    <p v-else class="text-[11px] text-slate-400 mt-1">
                        Có thể chuyển đổi bác sĩ nếu bác sĩ hẹn trước đang bận ca phẫu thuật hoặc cấp cứu.
                    </p>
                </div>

                <!-- Ghi chú tiếp nhận -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Ghi chú tiếp nhận lúc đến (Tùy chọn)
                    </label>
                    <textarea v-model="checkInNote" rows="2"
                        class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="Nhiệt độ, cân nặng hiện tại, biểu hiện mệt mỏi khi đến..."></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/80 flex items-center justify-end gap-2.5">
                <button type="button" @click="closeCheckInModal"
                    class="px-4 py-2 text-xs text-slate-600 hover:text-slate-800 hover:bg-slate-200 rounded-xl font-semibold transition-colors cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" @click="submitCheckIn" :disabled="isCheckingIn"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                    <Icon name="check" size="14" />
                    <span>{{ isCheckingIn ? 'Đang xử lý...' : 'Xác nhận Check-in' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
