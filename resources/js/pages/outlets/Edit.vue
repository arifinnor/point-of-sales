<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { index, update } from '@/routes/outlets';
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
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem, Outlet } from '@/types';

interface EditOutletPageProps {
    outlet: Outlet;
    isSuperAdmin: boolean;
}

const props = defineProps<EditOutletPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Outlets & Registers',
        href: index.url(),
    },
    {
        title: 'Edit Outlet',
        href: '#',
    },
];

const form = useForm({
    code: props.outlet.code,
    name: props.outlet.name,
    address: props.outlet.address || '',
    mode: props.outlet.mode,
    settings: props.outlet.settings || {},
});

const submit = () => {
    form.put(update.url(String(props.outlet.id)));
};
</script>

<template>
    <Head title="Edit Outlet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 px-4">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="index.url()">
                    <Button variant="outline" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-semibold">Edit Outlet</h1>
                    <p class="text-sm text-muted-foreground">
                        Update outlet details
                    </p>
                </div>
            </div>

            <!-- Tenant Info for Superadmins -->
            <div v-if="isSuperAdmin && outlet.tenant" class="flex items-center gap-2">
                <span class="text-sm font-medium">Tenant:</span>
                <Badge variant="outline">{{ outlet.tenant.name }}</Badge>
            </div>

            <!-- Form -->
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Outlet Details</CardTitle>
                    <CardDescription>
                        Update the details for {{ outlet.name }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Code -->
                        <div class="space-y-2">
                            <Label for="code">Outlet Code *</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="e.g., OUT001"
                                :class="{ 'border-red-500': form.errors.code }"
                            />
                            <p v-if="form.errors.code" class="text-sm text-red-600">
                                {{ form.errors.code }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                A unique identifier for this outlet
                            </p>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Outlet Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g., Downtown Branch"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Textarea
                                id="address"
                                v-model="form.address"
                                placeholder="Enter the outlet address..."
                                rows="3"
                                :class="{ 'border-red-500': form.errors.address }"
                            />
                            <p v-if="form.errors.address" class="text-sm text-red-600">
                                {{ form.errors.address }}
                            </p>
                        </div>

                        <!-- Mode -->
                        <div class="space-y-2">
                            <Label for="mode">Outlet Mode *</Label>
                            <Select v-model="form.mode">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.mode }">
                                    <SelectValue placeholder="Select outlet mode" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pos">POS (Point of Sale)</SelectItem>
                                    <SelectItem value="restaurant">Restaurant</SelectItem>
                                    <SelectItem value="minimarket">Minimarket</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.mode" class="text-sm text-red-600">
                                {{ form.errors.mode }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Choose the type of business for this outlet
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Outlet' }}
                            </Button>
                            <Link :href="index.url()">
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
