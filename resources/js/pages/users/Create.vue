<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, UserPlus } from 'lucide-vue-next';
import { ref } from 'vue';
import { index, create, store } from '@/routes/users';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import type { BreadcrumbItem, Role } from '@/types';

interface CreateUserPageProps {
    roles: Role[];
}

const props = defineProps<CreateUserPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: index.url(),
    },
    {
        title: 'Create User',
        href: create.url(),
    },
];

// Form data
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [] as string[],
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

// Methods
const handleRoleChange = (roleName: string, checked: boolean) => {
    if (checked) {
        form.value.roles.push(roleName);
    } else {
        const index = form.value.roles.indexOf(roleName);
        if (index > -1) {
            form.value.roles.splice(index, 1);
        }
    }
};

const submit = () => {
    processing.value = true;
    errors.value = {};

    router.post(store.url(), form.value, {
        onSuccess: () => {
            // Success message will be handled by the controller
        },
        onError: (formErrors) => {
            errors.value = formErrors;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const goBack = () => {
    router.get(index.url());
};

const getRoleDescription = (roleName: string) => {
    switch (roleName) {
        case 'admin':
            return 'Full system access with all permissions';
        case 'supervisor':
            return 'Extended permissions for management tasks';
        case 'cashier':
            return 'Basic permissions for sales and customer service';
        default:
            return 'Standard user role';
    }
};
</script>

<template>
    <Head title="Create User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 px-4">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="outline" size="sm" @click="goBack">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Create User</h1>
                    <p class="text-sm text-muted-foreground">
                        Add a new user to the system
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <UserPlus class="h-5 w-5" />
                                User Information
                            </CardTitle>
                            <CardDescription>
                                Enter the basic information for the new user
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- Name -->
                                <div class="space-y-2">
                                    <Label for="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Enter full name"
                                        required
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <Label for="email">Email Address</Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="Enter email address"
                                        required
                                    />
                                    <InputError :message="errors.email" />
                                </div>

                                <!-- Password -->
                                <div class="space-y-2">
                                    <Label for="password">Password</Label>
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        placeholder="Enter password"
                                        required
                                    />
                                    <InputError :message="errors.password" />
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-2">
                                    <Label for="password_confirmation">Confirm Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        placeholder="Confirm password"
                                        required
                                    />
                                    <InputError :message="errors.password_confirmation" />
                                </div>

                                <!-- Submit Button -->
                                <div class="flex gap-4 pt-4">
                                    <Button 
                                        type="submit" 
                                        :disabled="processing"
                                        class="min-w-32"
                                    >
                                        {{ processing ? 'Creating...' : 'Create User' }}
                                    </Button>
                                    <Button 
                                        type="button" 
                                        variant="outline" 
                                        @click="goBack"
                                        :disabled="processing"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Roles Sidebar -->
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle>Roles & Permissions</CardTitle>
                            <CardDescription>
                                Assign roles to the user
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div 
                                    v-for="role in roles" 
                                    :key="role.id"
                                    class="space-y-2"
                                >
                                    <div class="flex items-start space-x-3">
                                        <Checkbox
                                            :id="`role-${role.id}`"
                                            :checked="form.roles.includes(role.name)"
                                            @update:checked="(checked) => handleRoleChange(role.name, checked)"
                                        />
                                        <div class="grid gap-1.5 leading-none">
                                            <Label 
                                                :for="`role-${role.id}`"
                                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
                                            >
                                                {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                            </Label>
                                            <p class="text-xs text-muted-foreground">
                                                {{ getRoleDescription(role.name) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <InputError :message="errors.roles" />
                                
                                <div v-if="form.roles.length === 0" class="text-xs text-muted-foreground p-3 bg-muted rounded">
                                    No roles selected. User will have limited access.
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Role Info -->
                    <div v-if="form.roles.length > 0" class="mt-4">
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-sm">Selected Roles</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-2">
                                    <div 
                                        v-for="roleName in form.roles" 
                                        :key="roleName"
                                        class="text-sm font-medium p-2 bg-primary/10 rounded"
                                    >
                                        {{ roleName.charAt(0).toUpperCase() + roleName.slice(1) }}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
