<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * RBAC: the original users.role column was ENUM('employee','admin').
 * The application now uses exactly two roles: 'admin' and 'customer'.
 *
 *  1. Existing databases (MySQL/MariaDB/PostgreSQL): widen the column so it can
 *     hold 'customer' and make 'customer' the default.
 *     Fresh installs already get a VARCHAR(20) default 'customer' column from the
 *     (updated) create_users_table migration, so the MODIFY below is a harmless no-op.
 *  2. Convert every existing 'employee' row to 'customer'.
 *     Existing 'admin' rows are untouched. No users are deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'customer'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'customer'");
        }

        DB::table('users')->where('role', 'employee')->update(['role' => 'customer']);

        // Least privilege: anything that is neither admin nor customer becomes customer.
        DB::table('users')->whereNotIn('role', ['admin', 'customer'])->update(['role' => 'customer']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'customer')->update(['role' => 'employee']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('employee','admin') NOT NULL DEFAULT 'employee'");
        }
    }
};
