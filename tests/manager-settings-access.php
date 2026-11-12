<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager;
use Seiger\sSettings\Support\ManagerSettingsAccess;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

function evo()
{
    return new class {
        public function getLoginUserID(string $context): int
        {
            return (int) ($_SESSION[$context . 'InternalKey'] ?? 0);
        }

        public function hasPermission(string $permission, string $context): bool
        {
            return !empty($_SESSION[$context . 'Permissions'][$permission]);
        }
    };
}

$core = getenv('SSETTINGS_DEMO_CORE');
if (!$core || !is_file($core . '/vendor/autoload.php')) {
    throw new RuntimeException('Set SSETTINGS_DEMO_CORE');
}
require $core . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/Support/ManagerSettingsAccess.php';

$capsule = new Manager();
$capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => 'evo_']);
$capsule->bootEloquent();
$db = $capsule->getConnection();
$schema = $db->getSchemaBuilder();
$schema->create('users', static fn ($table) => $table->integer('id')->primary());
$schema->create('user_roles', static fn ($table) => $table->integer('id')->primary());
$schema->create('user_attributes', static function ($table): void {
    $table->integer('internalKey')->primary();
    $table->integer('role');
});
$schema->create('role_permissions', static function ($table): void {
    $table->integer('role_id');
    $table->string('permission');
});

$db->table('users')->insert(['id' => 10]);
$db->table('user_roles')->insert([['id' => 2], ['id' => 3]]);
$db->table('user_attributes')->insert(['internalKey' => 10, 'role' => 2]);
$db->table('role_permissions')->insert(['role_id' => 2, 'permission' => 'settings']);

$count = 0;
$check = static function (bool $expected, string $message) use (&$count): void {
    if (ManagerSettingsAccess::allows() !== $expected) {
        throw new RuntimeException($message);
    }
    $count++;
};

$_SESSION = ['mgrValidated' => true, 'mgrInternalKey' => 10, 'mgrRole' => 2, 'mgrPermissions' => ['settings' => 1]];
$check(true, 'Current role with settings can open the panel');

$db->table('role_permissions')->where('role_id', 2)->delete();
$check(false, 'Revocation denies the next check despite the unchanged session grant');
$db->table('role_permissions')->insert(['role_id' => 2, 'permission' => 'settings']);
$check(true, 'The check reads a new role grant from the database');

$db->table('user_attributes')->where('internalKey', 10)->update(['role' => 3]);
$check(false, 'Role reassignment takes effect despite the unchanged session role');
$db->table('user_attributes')->where('internalKey', 10)->update(['role' => 2]);
unset($_SESSION['mgrPermissions']['settings']);
$check(false, 'A database grant does not replace the session permission check');
$_SESSION['mgrPermissions']['settings'] = 1;
$_SESSION['mgrValidated'] = false;
$check(false, 'An inactive session cannot use a retained permission snapshot');
$_SESSION['mgrValidated'] = true;

$db->table('user_roles')->where('id', 2)->delete();
$check(false, 'A grant for a removed role does not authorize the session');
$db->table('user_roles')->insert(['id' => 2]);
$db->table('users')->where('id', 10)->delete();
$check(false, 'A session for a removed user cannot open the panel');

echo "Manager settings access checks passed: {$count}\n";
