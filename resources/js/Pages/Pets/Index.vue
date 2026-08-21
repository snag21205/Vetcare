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
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Hồ sơ Vật nuôi</h1>
                <p class="text-xs text-gray-500 mt-0.5">Danh sách các thú cưng đang được theo dõi và điều trị</p>
            </div>
            <Link :href="route('pets.create')"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-sm transition-colors">
                <Icon name="plus" size="16" />
                <span>Thêm vật nuôi</span>
            </Link>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50/75 text-gray-500 font-mono uppercase text-[11px] border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Tên thú cưng</th>
                            <th class="px-5 py-3 font-semibold">Loài / Giống</th>
                            <th class="px-5 py-3 font-semibold">Giới tính</th>
                            <th class="px-5 py-3 font-semibold">Chủ nhân</th>
                            <th class="px-5 py-3 font-semibold text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="pet in pets.data" :key="pet.id" class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center font-medium border border-indigo-100">
                                        <Icon name="pet" size="14" />
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-900">{{ pet.name }}</span>
                                        <p v-if="pet.weight" class="text-[11px] text-gray-400 font-mono">{{ pet.weight }} kg</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">
                                <span class="capitalize font-medium text-gray-800">{{ pet.species }}</span>
                                <span v-if="pet.breed" class="text-gray-400"> · {{ pet.breed }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">
                                <span class="bg-gray-100 text-gray-700 text-[11px] px-2 py-0.5 rounded font-medium">
                                    {{ genderLabel(pet.gender) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-gray-900">{{ pet.customer?.name }}</span>
                                <p class="text-[11px] text-gray-400 font-mono">{{ pet.customer?.phone }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('pets.edit', pet.id)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="edit" size="13" />
                                        <span>Sửa</span>
                                    </Link>
                                    <button @click="deletePet(pet.id, pet.name)"
                                        class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded transition-colors font-medium">
                                        <Icon name="trash" size="13" />
                                        <span>Xóa</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="pets.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <Icon name="pet" size="32" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-xs font-medium text-gray-600">Chưa có dữ liệu vật nuôi</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pets.last_page > 1" class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <p>Trang {{ pets.current_page }} / {{ pets.last_page }}</p>
                <div class="flex gap-1.5">
                    <Link v-if="pets.prev_page_url" :href="pets.prev_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <Icon name="chevron-left" size="14" />
                        <span>Trước</span>
                    </Link>
                    <Link v-if="pets.next_page_url" :href="pets.next_page_url"
                        preserve-scroll preserve-state
                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                        <span>Sau</span>
                        <Icon name="chevron-right" size="14" />
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>