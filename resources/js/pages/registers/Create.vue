<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { index as outletsIndex, store } from '@/routes/outlets';
import { store as registerStore } from '@/routes/registers';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import type { BreadcrumbItem, Outlet } from '@/types';

interface CreateRegisterPageProps {
    outlet_id?: string;
    outlets?: Outlet[];
    isSuperAdmin: boolean;
}

const props = defineProps<CreateRegisterPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Outlets & Registers',
        href: outletsIndex.url(),
    },
    {
        title: 'Create Register',
        href: '#',
    },
];

const form = useForm({
    outlet_id: props.outlet_id || '',
    name: '',
    printer_profile_id: '',
    settings: {},
});

// Group outlets by tenant for superadmins
const groupedOutlets = computed(() => {
    if (!props.isSuperAdmin || !props.outlets) return [];
    
    const groups = new Map();
    props.outlets.forEach(outlet => {
        if (outlet.tenant) {
            if (!groups.has(outlet.tenant.id)) {
                groups.set(outlet.tenant.id, {
                    id: outlet.tenant.id,
                    name: outlet.tenant.name,
                    outlets: []
                });
            }
            groups.get(outlet.tenant.id).outlets.push(outlet);
        }
    });
    
    return Array.from(groups.values());
});

const submit = () => {
    form.post(registerStore.url());
};
</script>

<template>
    <Head title="Create Register" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 px-4">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="outletsIndex.url()">
                    <Button variant="outline" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-semibold">Create Register</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ isSuperAdmin ? 'Add a new register to any outlet' : 'Add a new register to an outlet' }}
                    </p>
                </div>
            </div>

            <!-- Form -->
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Register Details</CardTitle>
                    <CardDescription>
                        Enter the details for your new register
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Outlet Selection -->
                        <div v-if="!outlet_id" class="space-y-2">
                            <Label for="outlet_id">Outlet *</Label>
                            <Select v-model="form.outlet_id">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.outlet_id }">
                                    <SelectValue placeholder="Select an outlet" />
                                </SelectTrigger>
                                <SelectContent>
                                    <template v-if="isSuperAdmin">
                                        <!-- Group outlets by tenant for superadmins -->
                                        <template v-for="tenant in groupedOutlets" :key="tenant.id">
                                            <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground bg-muted/50">
                                                {{ tenant.name }}
                                            </div>
                                            <SelectItem 
                                                v-for="outlet in tenant.outlets" 
                                                :key="outlet.id" 
                                                :value="outlet.id"
                                            >
                                                {{ outlet.name }} ({{ outlet.code }})
                                            </SelectItem>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <!-- Regular users: simple list -->
                                        <SelectItem 
                                            v-for="outlet in outlets" 
                                            :key="outlet.id" 
                                            :value="outlet.id"
                                        >
                                            {{ outlet.name }} ({{ outlet.code }})
                                        </SelectItem>
                                    </template>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.outlet_id" class="text-sm text-red-600">
                                {{ form.errors.outlet_id }}
                            </p>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Register Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g., Register 1, Main Counter"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Printer Profile -->
                        <div class="space-y-2">
                            <Label for="printer_profile_id">Printer Profile</Label>
                            <Input
                                id="printer_profile_id"
                                v-model="form.printer_profile_id"
                                placeholder="Printer profile ID (optional)"
                                :class="{ 'border-red-500': form.errors.printer_profile_id }"
                            />
                            <p v-if="form.errors.printer_profile_id" class="text-sm text-red-600">
                                {{ form.errors.printer_profile_id }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Optional printer configuration for receipts
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Register' }}
                            </Button>
                            <Link :href="outletsIndex.url()">
                                <Button type="button" variant="outline">
                                    Cancel
                                </Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
