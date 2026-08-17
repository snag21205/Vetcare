import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth.user);
    const role = computed(() => page.props.auth.role);

    const isAdmin = computed(() => role.value === 'admin');
    const isReceptionist = computed(() => role.value === 'receptionist');
    const isvet = computed(() => role.value === 'vet');
    const isAccountant = computed(() => role.value === 'accountant');

    const hasRole = (roleName) => role.value === roleName;

    return {
        user,
        role,
        isAdmin,
        isReceptionist,
        isvet,
        isAccountant,
        hasRole
    }
}