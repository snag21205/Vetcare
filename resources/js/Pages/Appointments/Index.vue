<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    appointments: Array,
    filters: Object,
    statuses: Object,
})

const date = ref(props.filters.date ?? new Date().toISOString().split('T')[0])
const status = ref(props.filters.status ?? '')

watch([date, status], ([newDate, newStatus]) => {
    router.get(route('appointments.index'), {
        date: newDate,
        status: newStatus,
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

function checkIn(id) {
    router.post(route('appointments.check-in', id), {}, {
        preserveScroll: true,
        preserveState: true,
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
    router.post(route('appointments.cancel', id), { reason }, {
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

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Quản lý Lịch hẹn</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Theo dõi lịch khám bệnh và điều phối tiếp đón bệnh nhân</p>
                </div>
                <Link :href="route('appointments.create')"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                    <Icon name="plus" size="16" />
                    <span>Đặt lịch hẹn</span>
                </Link>
            </div>

            <!-- Filter Bar -->
            <div
                class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-xs text-xs">
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Điều hướng ngày -->
                    <div
                        class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50/60 shadow-2xs">
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
                    <p class="text-[11px] text-slate-400 mt-0.5">Chọn ngày khác hoặc tạo lịch hẹn mới cho khách hàng.
                    </p>
                </div>

                <!-- Card từng lịch hẹn -->
                <div v-for="apt in appointments" :key="apt.id"
                    class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-slate-300 transition-all">

                    <div class="flex items-start gap-4">
                        <!-- Giờ hẹn -->
                        <div
                            class="w-16 h-14 rounded-xl bg-sky-50/60 border border-sky-100 flex flex-col items-center justify-center shrink-0">
                            <span class="text-sm font-bold text-sky-700 tabular-nums">
                                {{ new Date(apt.scheduled_at).toLocaleTimeString('vi-VN', {
                                    hour: '2-digit', minute:
                                        '2-digit'
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
                                <span class="text-xs text-slate-400 tabular-nums">({{ apt.pet?.customer?.phone
                                }})</span>
                            </div>
                            <p class="text-xs text-slate-600">{{ apt.reason }}</p>
                            <p v-if="apt.doctor" class="text-[11px] text-sky-700 font-semibold flex items-center gap-1">
                                <Icon name="stethoscope" size="13" />
                                <span>BS. {{ apt.doctor.name }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Trạng thái & Action Buttons -->
                    <div
                        class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
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
                            <!-- Xác nhận -->
                            <button v-if="apt.status === 'pending'" @click="confirmAppointment(apt.id)"
                                class="inline-flex items-center gap-1 text-xs bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 px-3 py-1.5 rounded-xl font-semibold transition-colors cursor-pointer">
                                <Icon name="check" size="13" />
                                <span>Xác nhận</span>
                            </button>

                            <!-- Check-in -->
                            <button v-if="apt.status === 'confirmed'" @click="checkIn(apt.id)"
                                class="inline-flex items-center gap-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">
                                <Icon name="check" size="13" />
                                <span>Check-in</span>
                            </button>

                            <!-- Khám bệnh -->
                            <Link v-if="apt.status === 'checked_in' && !apt.medical_record"
                                :href="route('medical-records.create', { appointment_id: apt.id })"
                                class="inline-flex items-center gap-1 text-xs bg-sky-600 hover:bg-sky-700 text-white px-3.5 py-1.5 rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">
                                <Icon name="stethoscope" size="13" />
                                <span>Khám bệnh</span>
                            </Link>

                            <!-- Xem bệnh án & Hóa đơn -->
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

                            <!-- Sửa -->
                            <Link v-if="!['completed', 'cancelled'].includes(apt.status)"
                                :href="route('appointments.edit', apt.id)"
                                class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                                title="Chỉnh sửa">
                                <Icon name="edit" size="14" />
                            </Link>

                            <!-- Hủy -->
                            <button v-if="!['completed', 'cancelled', 'no_show'].includes(apt.status)"
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
    </AuthenticatedLayout>
</template>
