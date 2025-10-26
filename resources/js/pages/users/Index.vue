<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { 
    Search, 
    Plus, 
    Edit, 
    Trash2, 
    Eye, 
    Filter,
    MoreHorizontal 
} from 'lucide-vue-next';
import { index, create, show, edit, destroy } from '@/routes/users';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { 
    Dialog, 
    DialogContent, 
    DialogDescription, 
    DialogFooter, 
    DialogHeader, 
    DialogTitle 
} from '@/components/ui/dialog';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import type { BreadcrumbItem, User, Role } from '@/types';

interface UsersPageProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    roles: Role[];
    filters: {
        search?: string;
        role?: string;
    };
}

const props = defineProps<UsersPageProps>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: index.url(),
    },
];

// Local state
const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');
const showDeleteDialog = ref(false);
const userToDelete = ref<User | null>(null);

// Computed properties
const canManageUsers = computed(() => {
    return page.props.auth.user.permissions?.includes('manage_user') || false;
});

const hasFilters = computed(() => {
    return searchQuery.value || selectedRole.value;
});

// Methods
const search = () => {
    router.get(index.url(), {
        search: searchQuery.value,
        role: selectedRole.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedRole.value = '';
    router.get(index.url(), {}, {
        preserveState: true,
        replace: true,
    });
};

const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteDialog.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(destroy.url(String(userToDelete.value.id)), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                userToDelete.value = null;
            },
        });
    }
};

const getRoleBadgeVariant = (roleName: string) => {
    switch (roleName) {
        case 'admin':
            return 'destructive';
        case 'supervisor':
            return 'default';
        case 'cashier':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getUserInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 px-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Users</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage user accounts and permissions
                    </p>
                </div>
                <Link 
                    v-if="canManageUsers"
                    :href="create.url()" 
                    class="inline-flex"
                >
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Add User
                    </Button>
                </Link>
            </div>

            <!-- Filters Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Filters</CardTitle>
                    <CardDescription>
                        Search and filter users
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 md:flex-row md:items-end">
                        <div class="flex-1">
                            <label for="search" class="text-sm font-medium">Search</label>
                            <div class="relative mt-1">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="search"
                                    v-model="searchQuery"
                                    placeholder="Search by name or email..."
                                    class="pl-10"
                                    @keyup.enter="search"
                                />
                            </div>
                        </div>
                        <div class="md:w-48">
                            <label for="role" class="text-sm font-medium">Role</label>
                            <Select v-model="selectedRole">
                                <SelectTrigger id="role" class="mt-1">
                                    <SelectValue placeholder="All roles" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex gap-2">
                            <Button @click="search">
                                <Filter class="mr-2 h-4 w-4" />
                                Filter
                            </Button>
                            <Button v-if="hasFilters" variant="outline" @click="clearFilters">
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Users</CardTitle>
                            <CardDescription>
                                {{ users.total }} total users
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4 font-medium text-muted-foreground">User</th>
                                    <th class="text-left p-4 font-medium text-muted-foreground">Email</th>
                                    <th class="text-left p-4 font-medium text-muted-foreground">Roles</th>
                                    <th class="text-left p-4 font-medium text-muted-foreground">Joined</th>
                                    <th class="text-left p-4 font-medium text-muted-foreground">Status</th>
                                    <th class="text-right p-4 font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="user in users.data" 
                                    :key="user.id"
                                    class="border-b transition-colors hover:bg-muted/50"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <Avatar class="h-8 w-8">
                                                <AvatarFallback class="text-xs">
                                                    {{ getUserInitials(user.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div>
                                                <div class="font-medium">{{ user.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-muted-foreground">
                                        {{ user.email }}
                                    </td>
                                    <td class="p-4">
                                        <div class="flex gap-1 flex-wrap">
                                            <Badge 
                                                v-for="role in user.roles" 
                                                :key="role.id"
                                                :variant="getRoleBadgeVariant(role.name)"
                                                class="text-xs"
                                            >
                                                {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                            </Badge>
                                            <span v-if="!user.roles?.length" class="text-muted-foreground text-sm">
                                                No roles
                                            </span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-muted-foreground">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="p-4">
                                        <Badge 
                                            :variant="user.email_verified_at ? 'default' : 'secondary'"
                                            class="text-xs"
                                        >
                                            {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="sm">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="show.url(String(user.id))">
                                                            <Eye class="mr-2 h-4 w-4" />
                                                            View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem 
                                                        v-if="canManageUsers"
                                                        as-child
                                                    >
                                                        <Link :href="edit.url(String(user.id))">
                                                            <Edit class="mr-2 h-4 w-4" />
                                                            Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem 
                                                        v-if="canManageUsers && user.id !== page.props.auth.user.id"
                                                        @click="confirmDelete(user)"
                                                        class="text-red-600"
                                                    >
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.last_page > 1" class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in users.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 text-sm rounded-md',
                                    link.active 
                                        ? 'bg-primary text-primary-foreground' 
                                        : 'bg-background border hover:bg-muted',
                                    !link.url ? 'opacity-50 pointer-events-none' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete User</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete {{ userToDelete?.name }}? 
                        This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button 
                        variant="outline" 
                        @click="showDeleteDialog = false"
                    >
                        Cancel
                    </Button>
                    <Button 
                        variant="destructive" 
                        @click="deleteUser"
                    >
                        Delete User
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
