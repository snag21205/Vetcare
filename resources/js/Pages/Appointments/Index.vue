<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    appointments: Array,
    filters: Object,
    statuses: Object,
})

// ref(): khởi tạo reactive từ filter hiện tại
const date = ref(props.filters.date)
const status = ref(props.filters.status)

// watch() nhiều biến cùng lúc: dùng mảng
watch([date, status], ([newDate, newStatus]) => {
    router.get(route('appointments.index'), {
        date: newDate,
        status: newStatus,
    }, { preserveState: true, replace: true })
})

// Map status → màu badge
const statusConfig = {
    pending: { label: 'Chờ xác nhận', class: 'bg-yellow-100 text-yellow-800' },
    confirmed: { label: 'Đã xác nhận', class: 'bg-blue-100 text-blue-800' },
    checked_in: { label: 'Đã check-in', class: 'bg-green-100 text-green-800' },
    completed: { label: 'Hoàn thành', class: 'bg-gray-100 text-gray-700' },
    cancelled: { label: 'Đã hủy', class: 'bg-red-100 text-red-700' },
    no_show: { label: 'Không đến', class: 'bg-orange-100 text-orange-700' },
}

function confirmAppointment(id) {
    router.patch(route('appointments.confirm', id))
}

function checkIn(id) {
    router.patch(route('appointments.check-in', id))
}

function cancelAppointment(id) {
    if (!confirm('Hủy lịch hẹn này?')) return
    router.delete(route('appointments.destroy', id))
}

// Điều hướng ngày: hôm qua / ngày mai
function changeDay(offset) {
    const d = new Date(date.value)
    d.setDate(d.getDate() + offset)
    date.value = d.toISOString().split('T')[0]
}
</script>

<template>

    <Head title="Lịch hẹn" />
    <AuthenticatedLayout>

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">📅 Lịch hẹn</h1>
            <Link :href="route('appointments.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                + Đặt lịch mới
            </Link>
        </div>

        <!-- Filter bar -->
        <div class="flex flex-wrap items-center gap-3 mb-6 bg-white p-4 rounded-xl shadow">
            <!-- Điều hướng ngày -->
            <button @click="changeDay(-1)" class="px-3 py-2 border rounded-lg hover:bg-gray-50 text-gray-600">
                ← Hôm qua
            </button>

            <input type="date" v-model="date"
                class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />

            <button @click="changeDay(1)" class="px-3 py-2 border rounded-lg hover:bg-gray-50 text-gray-600">
                Ngày mai →
            </button>

            <button @click="date = new Date().toISOString().split('T')[0]"
                class="px-3 py-2 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-100 text-sm">
                Hôm nay
            </button>

            <!-- Filter trạng thái -->
            <select v-model="status"
                class="border rounded-lg px-9 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả trạng thái</option>
                <option v-for="(label, key) in statuses" :key="key" :value="key">
                    {{ label }}
                </option>
            </select>

            <!-- Tổng số -->
            <span class="ml-auto text-sm text-gray-500">
                {{ appointments.length }} lịch hẹn
            </span>
        </div>

        <!-- Danh sách lịch hẹn -->
        <div class="space-y-3">

            <!-- Empty state -->
            <div v-if="appointments.length === 0"
                class="bg-white rounded-xl shadow px-12 py-6 text-center text-gray-400">
                <p class="text-4xl mb-3">📭</p>
                <p>Không có lịch hẹn nào trong ngày này</p>
            </div>

            <!-- Card mỗi lịch hẹn -->
            <div v-for="apt in appointments" :key="apt.id"
                class="bg-white rounded-xl shadow p-5 flex items-center justify-between gap-4">

                <!-- Giờ hẹn -->
                <div class="text-center w-16 flex-shrink-0">
                    <p class="text-2xl font-bold text-blue-600">
                        {{ new Date(apt.scheduled_at).toLocaleTimeString('vi-VN', {
                            hour: '2-digit', minute: '2-digit'
                        }) }}
                    </p>
                </div>

                <!-- Thông tin thú cưng + chủ -->
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">
                        {{ apt.pet?.species === 'chó' ? '🐕' : '🐈' }}
                        {{ apt.pet?.name }}
                        <span class="text-gray-400 font-normal text-sm">
                            ({{ apt.pet?.customer?.name }})
                        </span>
                    </p>
                    <p class="text-sm text-gray-500 mt-1">{{ apt.reason }}</p>
                    <p v-if="apt.doctor" class="text-xs text-blue-500 mt-1">
                        🩺 BS. {{ apt.doctor.name }}
                    </p>
                </div>

                <!-- Badge trạng thái -->
                <span :class="statusConfig[apt.status]?.class"
                    class="text-xs px-3 py-1 rounded-full font-medium flex-shrink-0">
                    {{ statusConfig[apt.status]?.label }}
                </span>

                <!-- Action buttons -->
                <div class="flex gap-2 flex-shrink-0">

                    <!-- Xác nhận (chỉ khi pending) -->
                    <button v-if="apt.status === 'pending'" @click="confirmAppointment(apt.id)"
                        class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                        ✓ Xác nhận
                    </button>

                    <!-- Check-in (chỉ khi confirmed) -->
                    <button v-if="apt.status === 'confirmed'" @click="checkIn(apt.id)"
                        class="text-xs bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                        ✓ Check-in
                    </button>

                    <!-- Khám bệnh (khi đã check-in) -->
                    <Link v-if="apt.status === 'checked_in'"
                        :href="route('medical-records.create', { appointment_id: apt.id })"
                        class="text-xs bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 font-medium flex items-center gap-1 shadow-sm">
                        🩺 Khám bệnh
                    </Link>

                    <!-- Sửa -->
                    <Link :href="route('appointments.edit', apt.id)"
                        class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200">
                        Sửa
                    </Link>

                    <!-- Hủy (không hiện nếu đã completed/cancelled) -->
                    <button v-if="!['completed', 'cancelled', 'no_show'].includes(apt.status)"
                        @click="cancelAppointment(apt.id)"
                        class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
