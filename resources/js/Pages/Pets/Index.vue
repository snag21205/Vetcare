<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

function deletePet(id, name) {
    if (!confirm(`Bạn chắc chắn muốn xóa "${name}"?`)) return
    router.delete(route('pets.destroy', id))
}

defineProps({
    pets: Object,
})

// Map species → emoji
const speciesIcon = (species) => ({ 'chó': '🐕', 'mèo': '🐈' }[species] ?? '🐾')
const genderLabel = (g) => ({ male: '♂ Đực', female: '♀ Cái', unknown: '— Chưa rõ' }[g])
</script>

<template>

    <Head title="Vật nuôi" />
    <AuthenticatedLayout>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">🐾 Vật nuôi</h1>
            <Link :href="route('pets.create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                + Thêm vật nuôi
            </Link>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Thú cưng</th>
                        <th class="px-6 py-3 text-left">Loài / Giống</th>
                        <th class="px-6 py-3 text-left">Giới tính</th>
                        <th class="px-6 py-3 text-left">Chủ nhân</th>
                        <th class="px-6 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="pet in pets.data" :key="pet.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ speciesIcon(pet.species) }}</span>
                                <span class="font-medium">{{ pet.name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <span class="capitalize">{{ pet.species }}</span>
                            <span v-if="pet.breed" class="text-gray-400"> · {{ pet.breed }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ genderLabel(pet.gender) }}</td>
                        <td class="px-6 py-4">
                            <Link :href="route('customers.show', pet.customer_id)"
                                class="text-blue-600 hover:underline">
                                {{ pet.customer?.name }}
                            </Link>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <Link :href="route('pets.edit', pet.id)"
                                    class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200">
                                    Sửa
                                </Link>
                                <button
                                    @click="deletePet(pet.id, pet.name)"
                                    class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">
                                    Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="pets.data.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            Chưa có vật nuôi nào
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>