<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can switch between tenants via API', function () {
    $tenant1 = Tenant::factory()->create(['name' => 'Tenant One']);
    $tenant2 = Tenant::factory()->create(['name' => 'Tenant Two']);
    $user = User::factory()->create();

    $user->tenants()->attach([$tenant1->id, $tenant2->id]);

    $this->actingAs($user);

    // Switch to tenant2
    $response = $this->post('/tenant/switch', [
        'tenant_id' => $tenant2->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Switched to Tenant Two');
    expect(session('current_tenant_id'))->toBe($tenant2->id);
});

test('user cannot switch to tenant they do not have access to', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    $user = User::factory()->create();

    $user->tenants()->attach($tenant1->id);

    $this->actingAs($user);

    $response = $this->post('/tenant/switch', [
        'tenant_id' => $tenant2->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['tenant' => 'You do not have access to this tenant.']);
});

test('tenant switch requires valid tenant ID', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();
    $user->tenants()->attach($tenant->id);
    
    $this->actingAs($user);

    $response = $this->post('/tenant/switch', [
        'tenant_id' => 'invalid-id',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors();
});