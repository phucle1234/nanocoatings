<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserFactoryRoleEnumTest extends TestCase
{
    use DatabaseTransactions;

    public function test_factory_role_values_are_valid_for_the_real_users_role_enum(): void
    {
        $column = DB::select("SHOW COLUMNS FROM users LIKE 'role'")[0];
        preg_match("/^enum\((.*)\)$/", $column->Type, $matches);
        $allowedRoles = array_map(fn ($v) => trim($v, "'"), explode(',', $matches[1]));

        // Create several users so both of the factory's randomElement()
        // choices get exercised at least once across the run.
        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            $this->assertContains($user->role, $allowedRoles);
        }
    }
}
