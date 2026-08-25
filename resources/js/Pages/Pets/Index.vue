<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'

function deletePet(id, name) {
    if (!confirm(`Bạn chắc chắn muốn xóa thú cưng "${name}"?`)) return
    router.delete(route('pets.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        only: ['pets', 'flash'],
    })
}

defineProps({
    pets: Object,
})

const genderLabel = (g) => ({ male: 'Đực', female: 'Cái', unknown: 'Chưa rõ' }[g] ?? '—')
</script>

<template>
    <Head title="Vật nuôi" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Hồ sơ Vật nuôi</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Danh sách các thú cưng đang được theo dõi và điều trị</p>
                </div>
                <Link :href="route('pets.create')"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                    <Icon name="plus" size="16" />
                    <span>Thêm vật nuôi</span>
                </Link>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-200px)] min-h-[380px]">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-xs text-left min-w-[650px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5">Tên thú cưng</th>
                                <th class="px-5 py-3.5">Loài / Giống</th>
                                <th class="px-5 py-3.5">Giới tính</th>
                                <th class="px-5 py-3.5">Chủ nhân</th>
                                <th class="px-5 py-3.5 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="pet in pets.data" :key="pet.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-medium border border-sky-100 shrink-0">
                                            <Icon name="pet" size="15" />
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 block truncate">{{ pet.name }}</span>
                                            <p v-if="pet.weight" class="text-[11px] text-slate-400 tabular-nums">{{ pet.weight }} kg</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">
                                    <span class="capitalize font-semibold text-slate-800">{{ pet.species }}</span>
                                    <span v-if="pet.breed" class="text-slate-400"> · {{ pet.breed }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">
                                    <span class="bg-slate-100 text-slate-700 text-[11px] px-2.5 py-0.5 rounded-full font-semibold">
                                        {{ genderLabel(pet.gender) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-slate-900 block">{{ pet.customer?.name }}</span>
                                    <p class="text-[11px] text-slate-400 tabular-nums">{{ pet.customer?.phone }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="route('pets.edit', pet.id)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                            <Icon name="edit" size="13" />
                                            <span>Sửa</span>
                                        </Link>
                                        <button @click="deletePet(pet.id, pet.name)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition-colors font-semibold cursor-pointer">
                                            <Icon name="trash" size="13" />
                                            <span>Xóa</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="pets.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="pet" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Chưa có dữ liệu vật nuôi</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pets.last_page > 1" class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <p>Trang <span class="font-bold text-slate-800">{{ pets.current_page }}</span> / {{ pets.last_page }}</p>
                    <div class="flex gap-1.5">
                        <Link v-if="pets.prev_page_url" :href="pets.prev_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <Icon name="chevron-left" size="14" />
                            <span>Trước</span>
                        </Link>
                        <Link v-if="pets.next_page_url" :href="pets.next_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <span>Sau</span>
                            <Icon name="chevron-right" size="14" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>