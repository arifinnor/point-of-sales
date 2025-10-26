<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change tenant_id to uuid in roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex('roles_team_foreign_key_index');
            $table->dropUnique(['tenant_id', 'name', 'guard_name']);
            $table->uuid('tenant_id')->nullable()->change();
            $table->index('tenant_id', 'roles_team_foreign_key_index');
            $table->unique(['tenant_id', 'name', 'guard_name']);
        });

        // Change tenant_id to uuid in model_has_permissions table
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->dropIndex('model_has_permissions_team_foreign_key_index');
            $table->uuid('tenant_id')->change();
            $table->index('tenant_id', 'model_has_permissions_team_foreign_key_index');
            $table->primary(['tenant_id', 'permission_id', 'model_uuid', 'model_type'],
                'model_has_permissions_permission_model_type_primary');
        });

        // Change tenant_id to uuid in model_has_roles table
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->dropIndex('model_has_roles_team_foreign_key_index');
            $table->uuid('tenant_id')->change();
            $table->index('tenant_id', 'model_has_roles_team_foreign_key_index');
            $table->primary(['tenant_id', 'role_id', 'model_uuid', 'model_type'],
                'model_has_roles_role_model_type_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse changes - change back to unsignedBigInteger
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex('roles_team_foreign_key_index');
            $table->dropUnique(['tenant_id', 'name', 'guard_name']);
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
            $table->index('tenant_id', 'roles_team_foreign_key_index');
            $table->unique(['tenant_id', 'name', 'guard_name']);
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->dropIndex('model_has_permissions_team_foreign_key_index');
            $table->unsignedBigInteger('tenant_id')->change();
            $table->index('tenant_id', 'model_has_permissions_team_foreign_key_index');
            $table->primary(['tenant_id', 'permission_id', 'model_uuid', 'model_type'],
                'model_has_permissions_permission_model_type_primary');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->dropIndex('model_has_roles_team_foreign_key_index');
            $table->unsignedBigInteger('tenant_id')->change();
            $table->index('tenant_id', 'model_has_roles_team_foreign_key_index');
            $table->primary(['tenant_id', 'role_id', 'model_uuid', 'model_type'],
                'model_has_roles_role_model_type_primary');
        });
    }
};
