import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface TenantContext {
    current: Tenant | null;
    available: Tenant[];
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    tenant: TenantContext;
    sidebarOpen: boolean;
};

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles?: Role[];
    permissions?: string[];
}

export interface Tenant {
    id: string;
    code: string;
    name: string;
    timezone?: string;
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
}

export interface Outlet {
    id: string;
    tenant_id: string;
    code: string;
    name: string;
    address?: string;
    mode: 'pos' | 'restaurant' | 'minimarket';
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
    tenant?: Tenant;
    registers?: Register[];
}

export interface Register {
    id: string;
    outlet_id: string;
    name: string;
    printer_profile_id?: string;
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
    outlet?: Outlet;
    // TODO: Add currentShift when Shift model is implemented
    // currentShift?: Shift;
}

export type BreadcrumbItemType = BreadcrumbItem;
