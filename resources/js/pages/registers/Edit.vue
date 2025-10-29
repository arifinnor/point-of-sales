<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { index as outletsIndex } from '@/routes/outlets';
import { update } from '@/routes/registers';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import type { BreadcrumbItem, Register } from '@/types';

interface EditRegisterPageProps {
    register: Register;
}

const props = defineProps<EditRegisterPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Outlets & Registers',
        href: outletsIndex.url(),
    },
    {
        title: 'Edit Register',
        href: '#',
    },
];

const form = useForm({
    name: props.register.name,
    printer_profile_id: props.register.printer_profile_id || '',
    settings: props.register.settings || {},
});

const submit = () => {
    form.put(update.url(String(props.register.id)));
};
</script>

<template>
    <Head title="Edit Register" />

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
                    <h1 class="text-2xl font-semibold">Edit Register</h1>
                    <p class="text-sm text-muted-foreground">
                        Update register details
                    </p>
                </div>
            </div>

            <!-- Form -->
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Register Details</CardTitle>
                    <CardDescription>
                        Update the details for {{ register.name }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <form @submit.prevent="submit" class="space-y-6">
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
                                {{ form.processing ? 'Updating...' : 'Update Register' }}
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
