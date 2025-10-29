<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { Tenant } from '@/types';
import { router } from '@inertiajs/vue3';
import { Building2, ChevronDown } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    currentTenant: Tenant | null;
    availableTenants: Tenant[];
}

const props = defineProps<Props>();

const tenantDisplayName = computed(() => {
    if (!props.currentTenant) return 'No Tenant';
    return props.currentTenant.name || props.currentTenant.code || 'Unknown Tenant';
});

const canSwitchTenant = computed(() => {
    return props.availableTenants.length > 1;
});

const switchTenant = (tenantId: string) => {
    router.post('/tenant/switch', { tenant_id: tenantId }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <TooltipProvider :delay-duration="0">
        <Tooltip>
            <TooltipTrigger>
                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <Button
                            variant="outline"
                            class="flex h-9 items-center gap-2 px-3 text-sm font-medium bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600"
                        >
                            <Building2 class="h-4 w-4" />
                            <span>{{ tenantDisplayName }}</span>
                            <ChevronDown class="h-3 w-3" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" class="w-56">
                        <template v-if="availableTenants.length > 0">
                            <DropdownMenuItem
                                v-for="tenant in availableTenants"
                                :key="tenant.id"
                                @click="switchTenant(tenant.id)"
                                :class="{
                                    'bg-accent': currentTenant?.id === tenant.id,
                                }"
                            >
                                <Building2 class="mr-2 h-4 w-4" />
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ tenant.name }}</span>
                                    <span class="text-xs text-muted-foreground">{{ tenant.code }}</span>
                                </div>
                            </DropdownMenuItem>
                        </template>
                        <template v-else>
                            <DropdownMenuItem disabled>
                                <Building2 class="mr-2 h-4 w-4" />
                                <span class="text-muted-foreground">No tenants available</span>
                            </DropdownMenuItem>
                        </template>
                    </DropdownMenuContent>
                </DropdownMenu>
            </TooltipTrigger>
            <TooltipContent>
                <p>{{ canSwitchTenant ? 'Switch Tenant' : 'Current Tenant' }}</p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
