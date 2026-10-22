<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/layouts/PortalLayout.vue';
import { Shield, ShieldAlert, UserCheck, Trash2, UserPlus, Info } from '@lucide/vue';

interface RoleItem {
    role_id: number;
    role_name: string;
    description: string;
}

interface MemberRoleItem {
    role_id: number;
    role_name: string;
    assigned_by: number | null;
    assigned_at: string | null;
}

interface MemberWithRoles {
    member_id: number;
    email: string;
    student_number: string;
    username: string | null;
    roles: MemberRoleItem[];
}

interface AssignableMember {
    member_id: number;
    student_number: string;
    email: string;
    username: string | null;
}

const props = defineProps<{
    roles: RoleItem[];
    membersWithRoles: MemberWithRoles[];
    assignableMembers: AssignableMember[];
}>();

const page = usePage();

const assignForm = useForm({
    member_id: '',
    role_id: '',
});

const handleAssign = () => {
    assignForm.post('/roles', {
        onSuccess: () => {
            assignForm.reset();
        },
    });
};

const handleRemoveRole = (memberId: number, roleId: number, roleName: string) => {
    if (confirm(`Are you sure you want to remove the '${roleName}' role from this member?`)) {
        router.delete(`/members/${memberId}/roles/${roleId}`);
    }
};
</script>

<template>
    <Head title="Role Management" />

    <PortalLayout title="Role Management" section="roles">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">Roles & Permissions</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Manage administrative roles, officer access, and delegation across the association.
                </p>
            </div>

            <!-- Security Policy Notice -->
            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/40 dark:bg-amber-950/20 text-sm text-amber-900 dark:text-amber-200 flex items-start gap-3">
                <ShieldAlert class="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <p class="font-medium">Administrative Guard Notice</p>
                    <p class="text-xs text-amber-800 dark:text-amber-300">
                        At least one member must retain the <code class="font-mono bg-amber-100 dark:bg-amber-900/50 px-1 py-0.5 rounded">Admin</code> role at all times.
                        The system will prevent removal if an admin is the sole administrator remaining.
                    </p>
                </div>
            </div>

            <!-- Error banner if any -->
            <div
                v-if="page.props.errors?.role"
                class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/50 dark:bg-rose-950/30 text-sm text-rose-800 dark:text-rose-300 flex items-center gap-2"
            >
                <ShieldAlert class="h-4 w-4 shrink-0" />
                <span>{{ page.props.errors.role }}</span>
            </div>

            <!-- Assign Role Form Card -->
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2 mb-4">
                    <UserPlus class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    Assign Role to Member
                </h2>
                <form @submit.prevent="handleAssign" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Select Member
                        </label>
                        <select
                            v-model="assignForm.member_id"
                            required
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                            <option value="" disabled>Choose verified member...</option>
                            <option v-for="m in assignableMembers" :key="m.member_id" :value="m.member_id">
                                {{ m.student_number }} - {{ m.username || m.email }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Role to Assign
                        </label>
                        <select
                            v-model="assignForm.role_id"
                            required
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        >
                            <option value="" disabled>Select role...</option>
                            <option v-for="r in roles" :key="r.role_id" :value="r.role_id">
                                {{ r.role_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="assignForm.processing"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-500 disabled:opacity-50 transition"
                        >
                            <Shield class="h-4 w-4" />
                            <span>{{ assignForm.processing ? 'Assigning...' : 'Assign Role' }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Current Role Assignments Table -->
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        Active Role Assignments
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Association members with elevated privileges and governance responsibilities.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-950/60 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6">Member</th>
                                <th scope="col" class="px-3 py-3.5">Assigned Roles</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <tr v-if="membersWithRoles.length === 0">
                                <td colspan="3" class="py-12 text-center text-zinc-500">
                                    No members currently assigned to officer roles.
                                </td>
                            </tr>
                            <tr
                                v-for="m in membersWithRoles"
                                :key="m.member_id"
                                class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition"
                            >
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                                    <div class="font-mono font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ m.student_number }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        {{ m.username ? `${m.username} (${m.email})` : m.email }}
                                    </div>
                                </td>
                                <td class="px-3 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <div
                                            v-for="r in m.roles"
                                            :key="r.role_id"
                                            class="inline-flex items-center gap-2 rounded-lg bg-purple-50 px-3 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-600/20 dark:bg-purple-950/40 dark:text-purple-300"
                                        >
                                            <span class="font-semibold">{{ r.role_name }}</span>
                                            <span v-if="r.assigned_at" class="text-[10px] text-purple-500">
                                                (assigned {{ r.assigned_at }})
                                            </span>
                                            <button
                                                type="button"
                                                class="text-purple-400 hover:text-rose-600 dark:hover:text-rose-400 transition"
                                                title="Remove this role"
                                                @click="handleRemoveRole(m.member_id, r.role_id, r.role_name)"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-xs sm:pr-6">
                                    <!-- Optional secondary action -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>

