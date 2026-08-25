<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    appointment: Object,
    pets:        Array,
    doctors:     Array,
    statuses:    Object,
})

const selectedCustomerId = ref(props.appointment.pet?.customer_id ?? '')

const filteredPets = computed(() => {
    if (!selectedCustomerId.value) return props.pets
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

const scheduledAt = props.appointment.scheduled_at
    ? props.appointment.scheduled_at.replace(' ', 'T').substring(0, 16)
    : ''

const form = useForm({
    pet_id:       props.appointment.pet_id,
    doctor_id:    props.appointment.doctor_id ?? '',
    scheduled_at: scheduledAt,
    reason:       props.appointment.reason,
    status:       props.appointment.status,
    notes:        props.appointment.notes ?? props.appointment.note ?? '',
})

function submit() {
    form.put(route('appointments.update', props.appointment.id))
}
</script>

<template>
    <Head title="Sửa lịch hẹn" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-4">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <Link :href="route('appointments.index')" class="hover:text-sky-600 transition-colors">Lịch hẹn</Link>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-600 truncate max-w-xs">Lịch #{{ appointment.id }}</span>
                <Icon name="chevron-right" size="12" class="text-slate-400" />
                <span class="text-slate-900 font-semibold">Chỉnh sửa</span>
            </nav>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h2 class="text-base font-bold text-slate-900">Chỉnh sửa lịch hẹn</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Cập nhật thông tin lịch hẹn của {{ appointment.pet?.name }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Chủ nhân</label>
                        <select v-model="selectedCustomerId" @change="form.pet_id = ''"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all">
                            <option value="">-- Tất cả khách hàng --</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Vật nuôi <span class="text-rose-500">*</span>
                        </label>
                        <select v-model="form.pet_id"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"
                            :class="{ 'border-rose-400 focus:ring-rose-500': form.errors.pet_id }">
                            <option v-for="pet in filteredPets" :key="pet.id" :value="pet.id">
                                {{ pet.name }} ({{ pet.customer?.name }})
                            </option>
                        </select>
                        <p v-if="form.errors.pet_id" class="text-rose-500 text-[11px] mt-1">{{ form.errors.pet_id }}</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Ngày giờ hẹn <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.scheduled_at" type="datetime-local"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all tabular-nums font-semibold" />
                        <p v-if="form.errors.scheduled_at" class="text-rose-500 text-[11px] mt-1">{{ form.errors.scheduled_at }}</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Lý do khám <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.reason" type="text"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                        <p v-if="form.errors.reason" class="text-rose-500 text-[11px] mt-1">{{ form.errors.reason }}</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Trạng thái lịch hẹn</label>
                        <select v-model="form.status"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all font-medium">
                            <option v-for="(label, key) in statuses" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Bác sĩ phụ trách</label>
                        <select v-model="form.doctor_id"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all">
                            <option value="">-- Chưa chỉ định --</option>
                            <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                                BS. {{ doc.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Ghi chú</label>
                        <textarea v-model="form.notes" rows="2"
                            class="w-full bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all"></textarea>
                    </div>

                    <div class="flex items-center gap-2.5 pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-xs transition-colors cursor-pointer">
                            <Icon name="check" size="14" />
                            <span>{{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
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
