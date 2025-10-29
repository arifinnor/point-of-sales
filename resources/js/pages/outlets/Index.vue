<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { 
    Plus, 
    Edit, 
    Trash2, 
    MoreHorizontal,
    ChevronDown,
    ChevronRight,
    Store,
    CreditCard,
    Filter
} from 'lucide-vue-next';
import { index, create, edit, destroy } from '@/routes/outlets';
import { create as createRegister, edit as editRegister, destroy as destroyRegister } from '@/routes/registers';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Dialog, 
    DialogContent, 
    DialogDescription, 
    DialogFooter, 
    DialogHeader, 
    DialogTitle 
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BreadcrumbItem, Outlet, Register, Tenant } from '@/types';

interface OutletsPageProps {
    outlets: Outlet[];
    isSuperAdmin: boolean;
}

const props = defineProps<OutletsPageProps>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Outlets & Registers',
        href: index.url(),
    },
];

// Local state
const showOutletDeleteDialog = ref(false);
const showRegisterDeleteDialog = ref(false);
const outletToDelete = ref<Outlet | null>(null);
const registerToDelete = ref<Register | null>(null);
const expandedOutlets = ref<Set<string>>(new Set());
const selectedTenantFilter = ref<string>('all');

// Computed properties
const canManageOutlets = computed(() => {
    return page.props.auth.user.permissions?.includes('manage_outlet') || false;
});

const canManageRegisters = computed(() => {
    return page.props.auth.user.permissions?.includes('manage_register') || false;
});

// Get unique tenants from outlets for filter
const availableTenants = computed(() => {
    const tenants = new Map<string, Tenant>();
    props.outlets.forEach(outlet => {
        if (outlet.tenant) {
            tenants.set(outlet.tenant.id, outlet.tenant);
        }
    });
    return Array.from(tenants.values());
});

// Filter outlets based on selected tenant
const filteredOutlets = computed(() => {
    if (!props.isSuperAdmin || selectedTenantFilter.value === 'all') {
        return props.outlets;
    }
    return props.outlets.filter(outlet => outlet.tenant?.id === selectedTenantFilter.value);
});

// Methods
const confirmOutletDelete = (outlet: Outlet) => {
    outletToDelete.value = outlet;
    showOutletDeleteDialog.value = true;
};

const confirmRegisterDelete = (register: Register) => {
    registerToDelete.value = register;
    showRegisterDeleteDialog.value = true;
};

const deleteOutlet = () => {
    if (outletToDelete.value) {
        router.delete(destroy.url(String(outletToDelete.value.id)), {
            onSuccess: () => {
                showOutletDeleteDialog.value = false;
                outletToDelete.value = null;
            },
        });
    }
};

const deleteRegister = () => {
    if (registerToDelete.value) {
        router.delete(destroyRegister.url(String(registerToDelete.value.id)), {
            onSuccess: () => {
                showRegisterDeleteDialog.value = false;
                registerToDelete.value = null;
            },
        });
    }
};

const toggleOutletExpansion = (outletId: string) => {
    if (expandedOutlets.value.has(outletId)) {
        expandedOutlets.value.delete(outletId);
    } else {
        expandedOutlets.value.add(outletId);
    }
};

const getModeBadgeVariant = (mode: string) => {
    switch (mode) {
        case 'pos':
            return 'default';
        case 'restaurant':
            return 'secondary';
        case 'minimarket':
            return 'outline';
        default:
            return 'outline';
    }
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
    <Head title="Outlets & Registers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 px-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Outlets & Registers</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ isSuperAdmin ? 'Manage outlets and registers across all tenants' : 'Manage your outlets and their registers' }}
                    </p>
                </div>
                <Link 
                    v-if="canManageOutlets"
                    :href="create.url()" 
                    class="inline-flex"
                >
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Add Outlet
                    </Button>
                </Link>
            </div>

            <!-- Tenant Filter for Superadmins -->
            <div v-if="isSuperAdmin && availableTenants.length > 0" class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm font-medium">Filter by tenant:</span>
                </div>
                <Select v-model="selectedTenantFilter">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Select tenant" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Tenants</SelectItem>
                        <SelectItem 
                            v-for="tenant in availableTenants" 
                            :key="tenant.id" 
                            :value="tenant.id"
                        >
                            {{ tenant.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Outlets Grid -->
            <div v-if="filteredOutlets.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card 
                    v-for="outlet in filteredOutlets" 
                    :key="outlet.id"
                    class="overflow-hidden"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <CardTitle class="text-lg flex items-center gap-2">
                                    <Store class="h-5 w-5 text-muted-foreground" />
                                    {{ outlet.name }}
                                </CardTitle>
                                <CardDescription class="text-sm">
                                    Code: {{ outlet.code }}
                                    <!-- Show tenant badge for superadmins -->
                                    <Badge v-if="isSuperAdmin && outlet.tenant" variant="outline" class="ml-2">
                                        {{ outlet.tenant.name }}
                                    </Badge>
                                </CardDescription>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge :variant="getModeBadgeVariant(outlet.mode)">
                                    {{ outlet.mode.charAt(0).toUpperCase() + outlet.mode.slice(1) }}
                                </Badge>
                                <DropdownMenu v-if="canManageOutlets">
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="sm">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem as-child>
                                            <Link :href="edit.url(String(outlet.id))">
                                                <Edit class="mr-2 h-4 w-4" />
                                                Edit
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem 
                                            @click="confirmOutletDelete(outlet)"
                                            class="text-red-600"
                                        >
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </CardHeader>
                    
                    <CardContent class="space-y-4">
                        <!-- Outlet Details -->
                        <div v-if="outlet.address" class="text-sm text-muted-foreground">
                            {{ outlet.address }}
                        </div>
                        
                        <div class="text-xs text-muted-foreground">
                            Created {{ formatDate(outlet.created_at) }}
                        </div>

                        <!-- Registers Section -->
                        <div class="space-y-2">
                            <Collapsible>
                                <CollapsibleTrigger 
                                    @click="toggleOutletExpansion(outlet.id)"
                                    class="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm font-medium hover:bg-muted/50"
                                >
                                    <div class="flex items-center gap-2">
                                        <CreditCard class="h-4 w-4" />
                                        <span>Registers ({{ outlet.registers?.length || 0 }})</span>
                                    </div>
                                    <ChevronDown 
                                        v-if="expandedOutlets.has(outlet.id)"
                                        class="h-4 w-4 transition-transform"
                                    />
                                    <ChevronRight 
                                        v-else
                                        class="h-4 w-4 transition-transform"
                                    />
                                </CollapsibleTrigger>
                                
                                <CollapsibleContent class="space-y-2">
                                    <div v-if="outlet.registers && outlet.registers.length > 0" class="space-y-2">
                                        <div 
                                            v-for="register in outlet.registers" 
                                            :key="register.id"
                                            class="flex items-center justify-between rounded-md border p-3"
                                        >
                                            <div class="space-y-1">
                                                <div class="font-medium text-sm">{{ register.name }}</div>
                                                <div class="text-xs text-muted-foreground">
                                                    Created {{ formatDate(register.created_at) }}
                                                </div>
                                            </div>
                                            <DropdownMenu v-if="canManageRegisters">
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="sm">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="editRegister.url(String(register.id))">
                                                            <Edit class="mr-2 h-4 w-4" />
                                                            Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem 
                                                        @click="confirmRegisterDelete(register)"
                                                        class="text-red-600"
                                                    >
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-muted-foreground text-center py-4">
                                        No registers yet
                                    </div>
                                    
                                    <div v-if="canManageRegisters" class="pt-2">
                                        <Link 
                                            :href="createRegister.url({ outlet_id: outlet.id })"
                                            class="inline-flex"
                                        >
                                            <Button variant="outline" size="sm" class="w-full">
                                                <Plus class="mr-2 h-4 w-4" />
                                                Add Register
                                            </Button>
                                        </Link>
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <Store class="mx-auto h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-semibold">
                    {{ isSuperAdmin && selectedTenantFilter !== 'all' ? 'No outlets found for selected tenant' : 'No outlets yet' }}
                </h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ isSuperAdmin && selectedTenantFilter !== 'all' 
                        ? 'Try selecting a different tenant or create a new outlet.' 
                        : 'Get started by creating your first outlet.' }}
                </p>
                <div v-if="canManageOutlets" class="mt-6">
                    <Link :href="create.url()" class="inline-flex">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Outlet
                        </Button>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Outlet Delete Confirmation Dialog -->
        <Dialog v-model:open="showOutletDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Outlet</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete {{ outletToDelete?.name }}? 
                        This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button 
                        variant="outline" 
                        @click="showOutletDeleteDialog = false"
                    >
                        Cancel
                    </Button>
                    <Button 
                        variant="destructive" 
                        @click="deleteOutlet"
                    >
                        Delete Outlet
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Register Delete Confirmation Dialog -->
        <Dialog v-model:open="showRegisterDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Register</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete {{ registerToDelete?.name }}? 
                        This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button 
                        variant="outline" 
                        @click="showRegisterDeleteDialog = false"
                    >
                        Cancel
                    </Button>
                    <Button 
                        variant="destructive" 
                        @click="deleteRegister"
                    >
                        Delete Register
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
