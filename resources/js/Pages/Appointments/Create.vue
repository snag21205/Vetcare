<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    pets: Array,
    doctors: Array,
})

const selectedCustomerId = ref('')

const filteredPets = computed(() => {
    if (!selectedCustomerId.value) return []
    return props.pets.filter(p => p.customer_id == selectedCustomerId.value)
})

const customers = computed(() => {
    const map = new Map()
    props.pets.forEach(p => {
        if (p.customer && !map.has(p.customer_id)) {
            map.set(p.customer_id, p.customer)
        }
    })
    return Array.from(map.values())
})

const form = useForm({
    pet_id:       '',
    doctor_id:    '',
    scheduled_at: '',
    reason:       '',
    notes:        '',
})

function submit() {
    form.post(route('appointments.store'))
}
</script>

<template>
    <Head title="Đặt lịch hẹn" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-4">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('appointments.index')" class="hover:text-sky-600 transition-colors">Lịch hẹn</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Đặt lịch mới</span>
            </nav>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-slate-900">Đặt lịch hẹn khám bệnh</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tiếp nhận thông tin đặt lịch của khách hàng</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <!-- Bước 1: Chọn chủ nhân -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Chủ nhân <span class="text-rose-500">*</span>
                        </label>
                        <select v-model="selectedCustomerId" @change="form.pet_id = ''"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all">
                            <option value="">-- Chọn khách hàng --</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.phone }})
                            </option>
                        </select>
                    </div>

                    <!-- Bước 2: Chọn thú cưng -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Vật nuôi <span class="text-rose-500">*</span>
                        </label>
                        <select v-model="form.pet_id" :disabled="!selectedCustomerId"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent disabled:bg-slate-100 disabled:text-slate-400 transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.pet_id }">
                            <option value="">
                                {{ selectedCustomerId ? '-- Chọn vật nuôi --' : '← Vui lòng chọn chủ nhân trước' }}
                            </option>
                            <option v-for="pet in filteredPets" :key="pet.id" :value="pet.id">
                                {{ pet.name }} ({{ pet.species }})
                            </option>
                        </select>
                        <p v-if="form.errors.pet_id" class="text-rose-500 text-[11px] mt-1">{{ form.errors.pet_id }}</p>
                    </div>

                    <!-- Ngày giờ hẹn -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Ngày giờ hẹn <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.scheduled_at" type="datetime-local"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.scheduled_at }" />
                        <p v-if="form.errors.scheduled_at" class="text-rose-500 text-[11px] mt-1">
                            {{ form.errors.scheduled_at }}
                        </p>
                    </div>

                    <!-- Lý do khám -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Lý do khám <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.reason" type="text"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.reason }"
                            placeholder="Tiêm vaccine, kiểm tra sức khỏe tổng quát, điều trị da liễu..." />
                        <p v-if="form.errors.reason" class="text-rose-500 text-[11px] mt-1">{{ form.errors.reason }}</p>
                    </div>

                    <!-- Chọn bác sĩ phụ trách -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Bác sĩ phụ trách</label>
                        <select v-model="form.doctor_id"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all">
                            <option value="">-- Chưa chỉ định (Phân bổ khi check-in) --</option>
                            <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                                BS. {{ doc.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Ghi chú -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Ghi chú thêm</label>
                        <textarea v-model="form.notes" rows="2"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            placeholder="Dặn dò thêm của khách hàng..."></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Xác nhận đặt lịch' }}</span>
                        </button>
                        <Link :href="route('appointments.index')"
                            class="px-4 py-2.5 border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                            Hủy
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
