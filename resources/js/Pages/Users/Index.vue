<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Icon from '@/Components/Icon.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
})

const search = ref(props.filters.search ?? '')
const role   = ref(props.filters.role ?? '')

let searchTimeout = null
watch([search, role], ([newSearch, newRole]) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('users.index'), {
            search: newSearch,
            role: newRole,
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['users', 'filters'],
            replace: true,
        })
    }, 300)
}, { immediate: false })

function toggleStatus(id, name, isActive) {
    const action = isActive ? 'khóa' : 'mở khóa'
    if (!confirm(`Bạn có chắc muốn ${action} tài khoản của nhân viên "${name}"?`)) return
    router.patch(route('users.toggle-status', id), {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['users', 'flash'],
    })
}

function deleteUser(id, name) {
    if (!confirm(`Bạn có chắc chắn muốn xóa tài khoản "${name}"?`)) return
    router.delete(route('users.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        only: ['users', 'flash'],
    })
}

const roleBadge = (r) => {
    const map = {
        admin:        { label: 'Quản trị viên', class: 'bg-rose-50 text-rose-700 border-rose-200' },
        vet:          { label: 'Bác sĩ Thú y', class: 'bg-sky-50 text-sky-700 border-sky-200' },
        receptionist: { label: 'Lễ tân', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
        accountant:   { label: 'Kế toán', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    }
    return map[r] ?? { label: r, class: 'bg-slate-100 text-slate-700 border-slate-200' }
}
</script>

<template>
    <Head title="Quản lý Nhân viên" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Quản lý Tài khoản & Phân quyền</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Danh sách nhân viên phòng khám, cấp quyền truy cập và kiểm soát trạng thái tài khoản</p>
                </div>
                <Link :href="route('users.create')"
                    class="inline-flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors shrink-0">
                    <Icon name="plus" size="16" />
                    <span>Thêm nhân viên mới</span>
                </Link>
            </div>

            <!-- Filter bar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200/80 shadow-xs text-xs">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Icon name="search" size="15" />
                    </div>
                    <input v-model="search" type="text" placeholder="Tìm theo tên, email, SĐT..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50/60 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all" />
                </div>

                <select v-model="role"
                    class="border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium">
                    <option value="">-- Tất cả vai trò --</option>
                    <option v-for="r in roles" :key="r" :value="r">
                        {{ roleBadge(r).label }}
                    </option>
                </select>
            </div>

            <!-- Table Container (Cuộn nội bộ bên trong) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs flex flex-col overflow-hidden max-h-[calc(100vh-250px)] min-h-[380px] text-xs">
                <div class="flex-1 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-left min-w-[650px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[11px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-5 py-3.5">Nhân viên</th>
                                <th class="px-5 py-3.5">Email & SĐT</th>
                                <th class="px-5 py-3.5 text-center">Vai trò / Quyền</th>
                                <th class="px-5 py-3.5 text-center">Trạng thái</th>
                                <th class="px-5 py-3.5 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-slate-50/80 transition-colors">
                                <!-- Nhân viên -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-600 to-blue-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                            {{ u.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block">{{ u.name }}</span>
                                            <p class="text-[10px] text-slate-400 font-mono">ID: #{{ u.id.toString().padStart(3, '0') }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Email / Phone -->
                                <td class="px-5 py-3.5 text-slate-600">
                                    <p class="font-medium text-slate-900">{{ u.email }}</p>
                                    <p class="text-[11px] text-slate-400 tabular-nums">{{ u.phone ?? 'Chưa cập nhật SĐT' }}</p>
                                </td>

                                <!-- Vai trò -->
                                <td class="px-5 py-3.5 text-center">
                                    <span v-for="r in u.roles" :key="r.name"
                                        class="inline-block border px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                                        :class="roleBadge(r.name).class">
                                        {{ roleBadge(r.name).label }}
                                    </span>
                                </td>

                                <!-- Trạng thái -->
                                <td class="px-5 py-3.5 text-center">
                                    <button @click="toggleStatus(u.id, u.name, u.is_active)"
                                        class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-0.5 rounded-full font-semibold transition-colors cursor-pointer"
                                        :class="u.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'"
                                        :title="u.is_active ? 'Bấm để khóa tài khoản' : 'Bấm để kích hoạt tài khoản'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="u.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                        <span>{{ u.is_active ? 'Hoạt động' : 'Đã khóa' }}</span>
                                    </button>
                                </td>

                                <!-- Thao tác -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="route('users.edit', u.id)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 px-2.5 py-1 rounded-lg transition-colors font-semibold">
                                            <Icon name="edit" size="13" />
                                            <span>Sửa</span>
                                        </Link>
                                        <button @click="deleteUser(u.id, u.name)"
                                            class="inline-flex items-center gap-1 text-[11px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition-colors font-semibold cursor-pointer">
                                            <Icon name="trash" size="13" />
                                            <span>Xóa</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <Icon name="users" size="36" class="mx-auto text-slate-300 mb-2" />
                                    <p class="text-xs font-semibold text-slate-600">Không tìm thấy nhân viên nào</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <p>Trang <span class="font-bold text-slate-800">{{ users.current_page }}</span> / {{ users.last_page }}</p>
                    <div class="flex gap-1.5">
                        <Link v-if="users.prev_page_url" :href="users.prev_page_url"
                            preserve-scroll preserve-state
                            class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-white text-slate-700 font-medium transition-colors">
                            <Icon name="chevron-left" size="14" />
                            <span>Trước</span>
                        </Link>
                        <Link v-if="users.next_page_url" :href="users.next_page_url"
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
