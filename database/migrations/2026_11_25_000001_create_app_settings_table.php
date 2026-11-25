<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Services\AppSettingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general')->index();
            $table->string('type', 30)->default('text');
            $table->timestamps();
        });

        $now = now();
        $rows = [];
        foreach (AppSettingService::DEFAULTS as $key => $meta) {
            $rows[] = [
                'key' => $key,
                'value' => $meta['value'],
                'group' => $meta['group'],
                'type' => $meta['type'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('app_settings')->insertOrIgnore($rows);

        if (Schema::hasTable('units')) {
            DB::table('units')
                ->where('address', 'like', '%Pangeran Ayin%')
                ->orWhereNull('address')
                ->update([
                    'address' => 'Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir',
                    'updated_at' => $now,
                ]);
        }

        if (Schema::hasTable('permissions') && Schema::hasTable('roles')) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $perm = Permission::firstOrCreate(['name' => 'manage-settings']);

            foreach ([UserRole::SUPERADMIN->value, UserRole::TREASURER->value] as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $role->givePermissionTo($perm);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
