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

const date   = ref(props.filters.date)
const status = ref(props.filters.status)

watch([date, status], ([newDate, newStatus]) => {
    router.get(route('appointments.index'), {
        date:   newDate,
        status: newStatus,
    }, { preserveState: true, replace: true })
})

// Refined status badges matching DESIGN.md
const statusConfig = {
    pending:    { label: 'Chờ xác nhận', class: 'bg-amber-50 text-amber-700 border border-amber-200' },
    confirmed:  { label: 'Đã xác nhận',  class: 'bg-sky-50 text-sky-700 border border-sky-200' },
    checked_in: { label: 'Đã check-in',  class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
    completed:  { label: 'Hoàn thành',   class: 'bg-zinc-100 text-zinc-700 border border-zinc-200' },
    cancelled:  { label: 'Đã hủy',       class: 'bg-rose-50 text-rose-700 border border-rose-200' },
    no_show:    { label: 'Không đến',    class: 'bg-orange-50 text-orange-700 border border-orange-200' },
}

function confirmAppointment(id) {
    router.patch(route('appointments.confirm', id))
}

function checkIn(id) {
    router.patch(route('appointments.check-in', id))
}

function cancelAppointment(id) {
    if (!confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?')) return
    router.delete(route('appointments.destroy', id))
}

function changeDay(offset) {
    const d = new Date(date.value)
    d.setDate(d.getDate() + offset)
    date.value = d.toISOString().split('T')[0]
}
</script>

<template>
    <Head title="Lịch hẹn" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Quản lý Lịch hẹn</h1>
                <p class="text-xs text-gray-500 mt-0.5">Theo dõi lịch khám bệnh và điều phối tiếp đón bệnh nhân</p>
            </div>
            <Link :href="route('appointments.create')"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors">
                <Icon name="plus" size="16" />
                <span>Đặt lịch hẹn</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="flex flex-wrap items-center gap-2.5 mb-6 bg-white p-3.5 rounded-lg border border-gray-200 shadow-sm text-xs">
            <!-- Điều hướng ngày -->
            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs">
                <button @click="changeDay(-1)"
                    class="px-2.5 py-1.5 hover:bg-gray-50 text-gray-600 border-r border-gray-200 transition-colors"
                    title="Hôm trước">
                    <Icon name="chevron-left" size="14" />
                </button>

                <input type="date" v-model="date"
                    class="px-3 py-1.5 text-xs text-gray-900 font-mono border-none focus:outline-none focus:ring-0 bg-transparent" />

                <button @click="changeDay(1)"
                    class="px-2.5 py-1.5 hover:bg-gray-50 text-gray-600 border-l border-gray-200 transition-colors"
                    title="Hôm sau">
                    <Icon name="chevron-right" size="14" />
                </button>
            </div>

            <button @click="date = new Date().toISOString().split('T')[0]"
                class="px-3 py-1.5 bg-gray-100 text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-200 font-medium transition-colors">
                Hôm nay
            </button>

            <!-- Lọc trạng thái -->
            <select v-model="status"
                class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="">Tất cả trạng thái</option>
                <option v-for="(label, key) in statuses" :key="key" :value="key">
                    {{ label }}
                </option>
            </select>

            <span class="ml-auto text-xs text-gray-400 font-mono">
                {{ appointments.length }} lịch hẹn
            </span>
        </div>

        <!-- Danh sách lịch hẹn -->
        <div class="space-y-2.5">

            <!-- Empty state -->
            <div v-if="appointments.length === 0"
                class="bg-white rounded-lg border border-gray-200 shadow-sm p-12 text-center text-gray-400">
                <Icon name="calendar" size="36" class="mx-auto text-gray-300 mb-2" />
                <p class="text-xs font-medium text-gray-600">Không có lịch hẹn nào trong ngày đã chọn</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Chọn ngày khác hoặc tạo lịch hẹn mới cho khách hàng.</p>
            </div>

            <!-- Card từng lịch hẹn -->
            <div v-for="apt in appointments" :key="apt.id"
                class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-gray-300 transition-all">

                <div class="flex items-start gap-4">
                    <!-- Giờ hẹn -->
                    <div class="w-16 h-14 rounded-lg bg-gray-50 border border-gray-100 flex flex-col items-center justify-center shrink-0">
                        <span class="text-sm font-bold font-mono text-indigo-600">
                            {{ new Date(apt.scheduled_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-mono">Giờ hẹn</span>
                    </div>

                    <!-- Thông tin thú cưng & chủ -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900 text-sm">{{ apt.pet?.name }}</span>
                            <span class="text-xs text-gray-500 font-normal">({{ apt.pet?.species }})</span>
                            <span class="text-xs text-gray-400">·</span>
                            <span class="text-xs text-gray-700 font-medium">{{ apt.pet?.customer?.name }}</span>
                            <span class="text-xs text-gray-400 font-mono">({{ apt.pet?.customer?.phone }})</span>
                        </div>
                        <p class="text-xs text-gray-600">{{ apt.reason }}</p>
                        <p v-if="apt.doctor" class="text-[11px] text-indigo-600 font-medium flex items-center gap-1">
                            <Icon name="stethoscope" size="12" />
                            <span>BS. {{ apt.doctor.name }}</span>
                        </p>
                    </div>
                </div>

                <!-- Trạng thái & Action Buttons -->
                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                    <!-- Badges -->
                    <div class="flex items-center gap-1.5">
                        <span :class="statusConfig[apt.status]?.class"
                            class="text-[11px] px-2.5 py-0.5 rounded-full font-medium font-mono">
                            {{ statusConfig[apt.status]?.label }}
                        </span>
                        <span v-if="apt.medical_record && apt.medical_record.status === 'in_progress'"
                            class="text-[11px] bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full font-medium font-mono">
                            Theo dõi
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1.5">
                        <!-- Xác nhận -->
                        <button v-if="apt.status === 'pending'" @click="confirmAppointment(apt.id)"
                            class="inline-flex items-center gap-1 text-xs bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 px-2.5 py-1 rounded-md font-medium transition-colors">
                            <Icon name="check" size="13" />
                            <span>Xác nhận</span>
                        </button>

                        <!-- Check-in -->
                        <button v-if="apt.status === 'confirmed'" @click="checkIn(apt.id)"
                            class="inline-flex items-center gap-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-md font-medium shadow-xs transition-colors">
                            <Icon name="check" size="13" />
                            <span>Check-in</span>
                        </button>

                        <!-- Khám bệnh -->
                        <Link v-if="apt.status === 'checked_in' && !apt.medical_record"
                            :href="route('medical-records.create', { appointment_id: apt.id })"
                            class="inline-flex items-center gap-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md font-semibold shadow-xs transition-colors">
                            <Icon name="stethoscope" size="13" />
                            <span>Khám bệnh</span>
                        </Link>

                        <!-- Xem bệnh án & Hóa đơn -->
                        <template v-if="apt.medical_record">
                            <Link :href="route('medical-records.show', apt.medical_record.id)"
                                class="inline-flex items-center gap-1 text-[11px] bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200 px-2.5 py-1 rounded-md font-medium transition-colors">
                                <Icon name="document" size="13" />
                                <span>Bệnh án</span>
                            </Link>
                            <Link v-if="apt.medical_record.invoice" :href="route('invoices.show', apt.medical_record.invoice.id)"
                                class="inline-flex items-center gap-1 text-[11px] bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-md font-medium transition-colors">
                                <Icon name="invoice" size="13" />
                                <span>Hóa đơn</span>
                            </Link>
                        </template>

                        <!-- Sửa -->
                        <Link v-if="!['completed', 'cancelled'].includes(apt.status)"
                            :href="route('appointments.edit', apt.id)"
                            class="p-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
                            title="Chỉnh sửa">
                            <Icon name="edit" size="14" />
                        </Link>

                        <!-- Hủy -->
                        <button v-if="!['completed', 'cancelled', 'no_show'].includes(apt.status)"
                            @click="cancelAppointment(apt.id)"
                            class="p-1 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors"
                            title="Hủy lịch hẹn">
                            <Icon name="x" size="14" />
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
