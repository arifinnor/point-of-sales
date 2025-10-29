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

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Tenant $tenant;

    protected Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'view_register']);
        Permission::create(['name' => 'manage_register']);

        // Create role
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['view_register', 'manage_register']);

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create outlet
        $this->outlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);

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

    public function test_can_view_registers_index(): void
    {
        $register = Register::factory()->create(['outlet_id' => $this->outlet->id]);

        $response = $this->actingAs($this->user)
            ->get(route('registers.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('registers/Index')
            ->has('registers', 1)
            ->where('registers.0.id', $register->id)
        );
    }

    public function test_can_create_register(): void
    {
        $registerData = [
            'outlet_id' => $this->outlet->id,
            'name' => 'Register 1',
            'printer_profile_id' => null,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('registers.store'), $registerData);

        $response->assertRedirect(route('outlets.index'));
        $response->assertSessionHas('success', 'Register created successfully.');

        $this->assertDatabaseHas('registers', [
            'outlet_id' => $this->outlet->id,
            'name' => 'Register 1',
        ]);
    }

    public function test_can_create_register_with_outlet_id_parameter(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('registers.create', ['outlet_id' => $this->outlet->id]));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('registers/Create')
            ->where('outlet_id', $this->outlet->id)
            ->has('outlets')
        );
    }

    public function test_can_update_register(): void
    {
        $register = Register::factory()->create(['outlet_id' => $this->outlet->id]);

        $updateData = [
            'name' => 'Updated Register',
            'printer_profile_id' => 'printer-123',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('registers.update', $register), $updateData);

        $response->assertRedirect(route('outlets.index'));
        $response->assertSessionHas('success', 'Register updated successfully.');

        $this->assertDatabaseHas('registers', [
            'id' => $register->id,
            'name' => 'Updated Register',
            'printer_profile_id' => 'printer-123',
        ]);
    }

    public function test_can_delete_register(): void
    {
        $register = Register::factory()->create(['outlet_id' => $this->outlet->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('registers.destroy', $register));

        $response->assertRedirect(route('outlets.index'));
        $response->assertSessionHas('success', 'Register deleted successfully.');

        $this->assertDatabaseMissing('registers', ['id' => $register->id]);
    }

    public function test_tenant_isolation_via_outlet(): void
    {
        // Create another tenant and outlet
        $otherTenant = Tenant::factory()->create();
        $otherOutlet = Outlet::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherRegister = Register::factory()->create(['outlet_id' => $otherOutlet->id]);

        // Create register for current tenant
        $currentRegister = Register::factory()->create(['outlet_id' => $this->outlet->id]);

        $response = $this->actingAs($this->user)
            ->get(route('registers.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('registers/Index')
            ->has('registers', 1)
            ->where('registers.0.id', $currentRegister->id)
        );

        // Should not be able to access other tenant's register
        $response = $this->actingAs($this->user)
            ->get(route('registers.show', $otherRegister));

        $response->assertNotFound();
    }

    public function test_requires_permission_to_manage_registers(): void
    {
        // Create user without manage_register permission
        $userWithoutPermission = User::factory()->create();
        $userWithoutPermission->tenants()->attach($this->tenant->id, ['is_default' => true]);

        $registerData = [
            'outlet_id' => $this->outlet->id,
            'name' => 'Register 1',
        ];

        $response = $this->actingAs($userWithoutPermission)
            ->post(route('registers.store'), $registerData);

        $response->assertForbidden();
    }

    public function test_register_name_is_required(): void
    {
        $registerData = [
            'outlet_id' => $this->outlet->id,
            'name' => '', // Empty name
        ];

        $response = $this->actingAs($this->user)
            ->post(route('registers.store'), $registerData);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_outlet_id_must_exist(): void
    {
        $registerData = [
            'outlet_id' => 'non-existent-uuid',
            'name' => 'Register 1',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('registers.store'), $registerData);

        $response->assertSessionHasErrors(['outlet_id']);
    }

    public function test_superadmin_can_view_all_tenants_registers(): void
    {
        // Create superadmin role
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(['view_register', 'manage_register']);

        // Create superadmin user
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        // Create another tenant with outlet and register
        $otherTenant = Tenant::factory()->create();
        $otherOutlet = Outlet::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherRegister = Register::factory()->create(['outlet_id' => $otherOutlet->id]);

        // Create register in current tenant
        $currentOutlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);
        $currentRegister = Register::factory()->create(['outlet_id' => $currentOutlet->id]);

        // Act as superadmin
        $response = $this->actingAs($superAdmin)->get('/registers');

        // Assert superadmin can see registers from all tenants
        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('registers/Index')
            ->has('registers', 2)
            ->has('isSuperAdmin', true)
        );
    }

    public function test_superadmin_can_create_register_in_any_tenant_outlet(): void
    {
        // Create superadmin role
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(['view_register', 'manage_register']);

        // Create superadmin user
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        // Create another tenant with outlet
        $otherTenant = Tenant::factory()->create();
        $otherOutlet = Outlet::factory()->create(['tenant_id' => $otherTenant->id]);

        // Act as superadmin and create register in other tenant's outlet
        $response = $this->actingAs($superAdmin)->post('/registers', [
            'outlet_id' => $otherOutlet->id,
            'name' => 'Superadmin Register',
        ]);

        // Assert register was created
        $response->assertRedirect('/outlets');
        $this->assertDatabaseHas('registers', [
            'outlet_id' => $otherOutlet->id,
            'name' => 'Superadmin Register',
        ]);
    }

    public function test_regular_user_cannot_access_other_tenants_registers(): void
    {
        // Create register in another tenant
        $otherTenant = Tenant::factory()->create();
        $otherOutlet = Outlet::factory()->create(['tenant_id' => $otherTenant->id]);
        Register::factory()->create(['outlet_id' => $otherOutlet->id]);

        // Create register in current tenant
        $currentOutlet = Outlet::factory()->create(['tenant_id' => $this->tenant->id]);
        $currentRegister = Register::factory()->create(['outlet_id' => $currentOutlet->id]);

        // Act as regular user
        $response = $this->actingAs($this->user)->get('/registers');

        // Assert regular user only sees current tenant's registers
        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('registers/Index')
            ->has('registers', 1)
            ->has('isSuperAdmin', false)
        );
    }
}
