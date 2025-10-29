<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Register;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OutletControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'view_outlet']);
        Permission::create(['name' => 'manage_outlet']);

        // Create role
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['view_outlet', 'manage_outlet']);

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create user
        $this->user = User::factory()->create();
        $this->user->tenants()->attach($this->tenant->id, ['is_default' => true]);

        // Set tenant context before assigning role
        app()->instance('current_tenant', $this->tenant);
        setPermissionsTeamId($this->tenant->id);

        $this->user->assignRole($role);

        // Set tenant context
        app()->instance('current_tenant', $this->tenant);
        setPermissionsTeamId($this->tenant->id);
    }

    public function test_can_view_outlets_index(): void
    {
        $outlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)
            ->get(route('outlets.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('outlets/Index')
            ->has('outlets', 1)
            ->where('outlets.0.id', $outlet->id)
        );
    }

    public function test_can_create_outlet(): void
    {
        $outletData = [
            'code' => 'OUT001',
            'name' => 'Test Outlet',
            'address' => '123 Test Street',
            'mode' => 'pos',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('outlets.store'), $outletData);

        $response->assertRedirect(route('outlets.index'));
        $response->assertSessionHas('success', 'Outlet created successfully.');

        $this->assertDatabaseHas('outlets', [
            'code' => 'OUT001',
            'name' => 'Test Outlet',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_outlet_code_must_be_unique_per_tenant(): void
    {
        // Create existing outlet
        Outlet::factory()->create([
            'code' => 'OUT001',
            'tenant_id' => $this->tenant->id,
        ]);

        $outletData = [
            'code' => 'OUT001', // Same code
            'name' => 'Test Outlet',
            'mode' => 'pos',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('outlets.store'), $outletData);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_can_update_outlet(): void
    {
        $outlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

        $updateData = [
            'code' => 'OUT002',
            'name' => 'Updated Outlet',
            'address' => '456 Updated Street',
            'mode' => 'restaurant',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('outlets.update', $outlet), $updateData);

        $response->assertRedirect(route('outlets.index'));
        $response->assertSessionHas('success', 'Outlet updated successfully.');

        $this->assertDatabaseHas('outlets', [
            'id' => $outlet->id,
            'code' => 'OUT002',
            'name' => 'Updated Outlet',
        ]);
    }

    public function test_can_delete_outlet_without_registers(): void
    {
        $outlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('outlets.destroy', $outlet));

        $response->assertRedirect(route('outlets.index'));
        $response->assertSessionHas('success', 'Outlet deleted successfully.');

        $this->assertDatabaseMissing('outlets', ['id' => $outlet->id]);
    }

    public function test_cannot_delete_outlet_with_registers(): void
    {
        $outlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);
        Register::factory()->create(['outlet_id' => $outlet->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('outlets.destroy', $outlet));

        $response->assertRedirect(route('outlets.index'));
        $response->assertSessionHas('error', 'Cannot delete outlet with existing registers. Please delete all registers first.');

        $this->assertDatabaseHas('outlets', ['id' => $outlet->id]);
    }

    public function test_tenant_isolation(): void
    {
        // Create another tenant and outlet
        $otherTenant = Tenant::factory()->create();
        $otherOutlet = Outlet::factory()->create(['tenant_id' => $otherTenant->id]);

        // Create outlet for current tenant
        $currentOutlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)
            ->get(route('outlets.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('outlets/Index')
            ->has('outlets', 1)
            ->where('outlets.0.id', $currentOutlet->id)
        );

        // Should not be able to access other tenant's outlet
        $response = $this->actingAs($this->user)
            ->get(route('outlets.edit', $otherOutlet));

        $response->assertNotFound();
    }

    public function test_requires_permission_to_manage_outlets(): void
    {
        // Create user without manage_outlet permission
        $userWithoutPermission = User::factory()->create();
        $userWithoutPermission->tenants()->attach($this->tenant->id, ['is_default' => true]);

        $outletData = [
            'code' => 'OUT001',
            'name' => 'Test Outlet',
            'mode' => 'pos',
        ];

        $response = $this->actingAs($userWithoutPermission)
            ->post(route('outlets.store'), $outletData);

        $response->assertForbidden();
    }

    public function test_superadmin_can_view_all_tenants_outlets(): void
    {
        // Create superadmin role
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(['view_outlet', 'manage_outlet']);

        // Create superadmin user
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        // Create another tenant with outlets
        $otherTenant = Tenant::factory()->create();
        $otherTenantOutlet = Outlet::factory()->create([
            'tenant_id' => $otherTenant->id,
            'code' => 'OTHER001',
            'name' => 'Other Tenant Outlet',
        ]);

        // Create outlet in current tenant
        $currentTenantOutlet = Outlet::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'CURRENT001',
            'name' => 'Current Tenant Outlet',
        ]);

        // Act as superadmin
        $response = $this->actingAs($superAdmin)->get('/outlets');

        // Assert superadmin can see outlets from all tenants
        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('outlets/Index')
            ->has('outlets', 2)
            ->where('isSuperAdmin', true)
        );
    }

    public function test_superadmin_can_create_outlet_in_any_tenant(): void
    {
        // Create superadmin role
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(['view_outlet', 'manage_outlet']);

        // Create superadmin user
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        // Create another tenant
        $otherTenant = Tenant::factory()->create();

        // Act as superadmin and create outlet in other tenant
        $response = $this->actingAs($superAdmin)->post('/outlets', [
            'tenant_id' => $otherTenant->id,
            'code' => 'SUPER001',
            'name' => 'Superadmin Created Outlet',
            'mode' => 'pos',
        ]);

        // Assert outlet was created in the correct tenant
        $response->assertRedirect('/outlets');
        $this->assertDatabaseHas('outlets', [
            'tenant_id' => $otherTenant->id,
            'code' => 'SUPER001',
            'name' => 'Superadmin Created Outlet',
        ]);
    }

    public function test_superadmin_can_edit_outlet_in_any_tenant(): void
    {
        // Create superadmin role
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(['view_outlet', 'manage_outlet']);

        // Create superadmin user
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        // Ensure superadmin can access all tenants
        $this->assertTrue($superAdmin->canAccessAllTenants());

        // Create another tenant with outlet
        $otherTenant = Tenant::factory()->create();
        $outlet = Outlet::factory()->create([
            'tenant_id' => $otherTenant->id,
            'code' => 'EDIT001',
            'name' => 'Original Name',
        ]);

        // Act as superadmin and edit outlet
        $response = $this->actingAs($superAdmin)->put("/outlets/{$outlet->id}", [
            'code' => 'EDIT001',
            'name' => 'Updated Name',
            'mode' => 'restaurant',
        ]);

        // Assert outlet was updated
        $response->assertRedirect('/outlets');
        $this->assertDatabaseHas('outlets', [
            'id' => $outlet->id,
            'name' => 'Updated Name',
            'mode' => 'restaurant',
        ]);
    }

    public function test_regular_user_cannot_see_other_tenants_outlets(): void
    {
        // Create outlet in another tenant
        $otherTenant = Tenant::factory()->create();
        Outlet::factory()->create([
            'tenant_id' => $otherTenant->id,
            'code' => 'OTHER001',
            'name' => 'Other Tenant Outlet',
        ]);

        // Create outlet in current tenant
        $currentTenantOutlet = Outlet::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'CURRENT001',
            'name' => 'Current Tenant Outlet',
        ]);

        // Act as regular user
        $response = $this->actingAs($this->user)->get('/outlets');

        // Assert regular user only sees current tenant's outlets
        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('outlets/Index')
            ->has('outlets', 1)
            ->where('isSuperAdmin', false)
        );
    }
}
