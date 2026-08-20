<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    record: Object,
})

const statusConfig = {
    in_progress: { label: 'Đang điều trị', class: 'bg-yellow-100 text-yellow-800' },
    completed:   { label: 'Đã hoàn thành', class: 'bg-green-100 text-green-800' },
}

const formatDate = (dateStr) => {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function printRecord() {
    window.print()
}
</script>

<template>
    <Head :title="`Phiếu khám #${record.id}`" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-4">
                <Link :href="route('medical-records.index')" class="hover:text-blue-600">Phiếu khám bệnh</Link>
                <span class="mx-2">/</span>
                <span>Chi tiết #{{ record.id }}</span>
            </nav>

            <!-- Card chính -->
            <div class="bg-white rounded-xl shadow p-6 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">
                            🩺 Phiếu khám bệnh #KB-{{ record.id.toString().padStart(4, '0') }}
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">Ngày lập: {{ formatDate(record.created_at) }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span :class="statusConfig[record.status]?.class"
                            class="text-xs px-3 py-1 rounded-full font-medium">
                            {{ statusConfig[record.status]?.label ?? record.status }}
                        </span>
                        <button @click="printRecord"
                            class="text-xs border px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                            🖨️ In
                        </button>
                    </div>
                </div>

                <!-- Thông tin bệnh nhân & Chủ nuôi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg text-sm">
                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Vật nuôi</p>
                        <p class="font-bold text-gray-900 flex items-center gap-1">
                            <span>{{ record.pet?.species === 'chó' ? '🐕' : '🐈' }}</span>
                            <span>{{ record.pet?.name }}</span>
                            <span class="text-gray-400 font-normal text-xs">({{ record.pet?.species }})</span>
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Cân nặng khi khám: <strong class="text-gray-800">{{ record.weight_at_visit ? `${record.weight_at_visit} kg` : '—' }}</strong>
                        </p>
                        <p class="text-xs text-gray-600">
                            Nhiệt độ: <strong class="text-gray-800">{{ record.temperature_at_visit ? `${record.temperature_at_visit} °C` : '—' }}</strong>
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Chủ nuôi & Bác sĩ</p>
                        <p class="font-bold text-gray-900">{{ record.pet?.customer?.name }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">SĐT: {{ record.pet?.customer?.phone }}</p>
                        <p class="text-xs text-blue-600 font-medium mt-1">
                            🩺 BS. {{ record.doctor?.name ?? '—' }}
                        </p>
                    </div>
                </div>

                <!-- Triệu chứng & Chẩn đoán -->
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Triệu chứng lâm sàng:</p>
                        <div class="p-3 bg-gray-50 rounded-lg text-gray-800 border">
                            {{ record.symptoms }}
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Chẩn đoán của bác sĩ:</p>
                        <div class="p-3 bg-blue-50/60 text-blue-900 font-medium rounded-lg border border-blue-100">
                            {{ record.diagnosis }}
                        </div>
                    </div>

                    <div v-if="record.note">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Lời dặn / Ghi chú:</p>
                        <p class="text-gray-600 italic">{{ record.note }}</p>
                    </div>
                </div>

                <!-- Đơn thuốc -->
                <div class="pt-4 border-t">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">💊 Đơn thuốc chỉ định</h3>

                    <div v-if="!record.prescriptions || record.prescriptions.length === 0"
                        class="p-4 text-center bg-gray-50 rounded-lg text-gray-400 text-xs">
                        Không có đơn thuốc.
                    </div>

                    <div v-else class="border rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Tên thuốc</th>
                                    <th class="px-4 py-2.5 text-left">Liều dùng</th>
                                    <th class="px-4 py-2.5 text-center">Số lượng</th>
                                    <th class="px-4 py-2.5 text-center">Số ngày</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="p in record.prescriptions" :key="p.id">
                                    <td class="px-4 py-3 font-medium">
                                        {{ p.medicine?.name }}
                                        <span v-if="p.medicine?.unit" class="text-xs text-gray-400 font-normal">({{ p.medicine?.unit }})</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ p.dosage }}</td>
                                    <td class="px-4 py-3 text-center font-semibold text-blue-600">
                                        {{ p.quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ p.days ? `${p.days} ngày` : '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Nút điều hướng -->
                <div class="flex justify-between items-center pt-4 border-t">
                    <Link :href="route('medical-records.index')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50 text-sm transition-colors">
                        ← Quay lại danh sách
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
