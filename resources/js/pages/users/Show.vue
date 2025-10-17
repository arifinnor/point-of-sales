<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    ArrowLeft, 
    Edit, 
    Mail, 
    Calendar, 
    Shield, 
    CheckCircle, 
    XCircle,
    User as UserIcon
} from 'lucide-vue-next';
import { index, show, edit } from '@/routes/users';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { BreadcrumbItem, User, Role, Permission } from '@/types';

interface UserWithRolesAndPermissions extends User {
    roles: (Role & { permissions: Permission[] })[];
}

interface ShowUserPageProps {
    user: UserWithRolesAndPermissions;
}

const props = defineProps<ShowUserPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: index.url(),
    },
    {
        title: props.user.name,
        href: show.url(props.user.id),
    },
];

// Methods
const getUserInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getAllPermissions = () => {
    const permissions = new Set<string>();
    props.user.roles?.forEach(role => {
        role.permissions?.forEach(permission => {
            permissions.add(permission.name);
        });
    });
    return Array.from(permissions).sort();
};

const formatPermissionName = (permission: string) => {
    return permission
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};
</script>

<template>
    <Head :title="user.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 px-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="index.url()">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </Link>
                    <div class="flex items-center gap-4">
                        <Avatar class="h-12 w-12">
                            <AvatarFallback class="text-lg">
                                {{ getUserInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <h1 class="text-2xl font-semibold">{{ user.name }}</h1>
                            <p class="text-sm text-muted-foreground flex items-center gap-2">
                                <Mail class="h-4 w-4" />
                                {{ user.email }}
                            </p>
                        </div>
                    </div>
                </div>
                <Link :href="edit.url(user.id)">
                    <Button>
                        <Edit class="mr-2 h-4 w-4" />
                        Edit User
                    </Button>
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- User Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <UserIcon class="h-5 w-5" />
                                User Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Full Name</Label>
                                    <p class="text-sm font-medium">{{ user.name }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Email Address</Label>
                                    <p class="text-sm font-medium">{{ user.email }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Email Status</Label>
                                    <div class="flex items-center gap-2">
                                        <Badge 
                                            :variant="user.email_verified_at ? 'default' : 'secondary'"
                                            class="text-xs"
                                        >
                                            <CheckCircle v-if="user.email_verified_at" class="mr-1 h-3 w-3" />
                                            <XCircle v-else class="mr-1 h-3 w-3" />
                                            {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                        </Badge>
                                    </div>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">User ID</Label>
                                    <p class="text-sm font-medium font-mono">#{{ user.id }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Account Timeline -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="h-5 w-5" />
                                Account Timeline
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <div>
                                        <p class="font-medium">Account created</p>
                                        <p class="text-muted-foreground">{{ formatDate(user.created_at) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <div>
                                        <p class="font-medium">Last updated</p>
                                        <p class="text-muted-foreground">{{ formatDate(user.updated_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="user.email_verified_at" class="flex items-center gap-3 text-sm">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <div>
                                        <p class="font-medium">Email verified</p>
                                        <p class="text-muted-foreground">{{ formatDate(user.email_verified_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Permissions -->
                    <Card v-if="user.roles && user.roles.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Shield class="h-5 w-5" />
                                Permissions
                            </CardTitle>
                            <CardDescription>
                                All permissions granted through assigned roles
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div 
                                    v-for="permission in getAllPermissions()" 
                                    :key="permission"
                                    class="flex items-center gap-2 text-sm p-2 border rounded"
                                >
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    {{ formatPermissionName(permission) }}
                                </div>
                            </div>
                            <div v-if="getAllPermissions().length === 0" class="text-center py-8 text-muted-foreground">
                                No permissions assigned
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Roles -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Assigned Roles</CardTitle>
                            <CardDescription>
                                Current user roles and access levels
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div 
                                    v-for="role in user.roles" 
                                    :key="role.id"
                                    class="p-3 border rounded-lg"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <Badge 
                                            :variant="getRoleBadgeVariant(role.name)"
                                            class="text-xs"
                                        >
                                            {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                        </Badge>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        {{ role.permissions?.length || 0 }} permissions
                                    </p>
                                </div>
                                
                                <div v-if="!user.roles || user.roles.length === 0" class="text-center py-4 text-muted-foreground">
                                    <Shield class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                    <p class="text-sm">No roles assigned</p>
                                    <p class="text-xs">User has limited access</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Link :href="edit.url(user.id)" class="block">
                                <Button variant="outline" class="w-full justify-start">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit User
                                </Button>
                            </Link>
                            <Separator />
                            <Link :href="index.url()" class="block">
                                <Button variant="ghost" class="w-full justify-start">
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    Back to Users
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
