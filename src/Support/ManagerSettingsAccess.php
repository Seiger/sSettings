<?php namespace Seiger\sSettings\Support;

use EvolutionCMS\Models\RolePermissions;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check whether the logged-in manager still holds the settings permission.
 *
 * @since 2.1.0
 */
final class ManagerSettingsAccess
{
    /**
     * Require the session grant and confirm it against the manager's current role in the database.
     *
     * Read from the primary connection so a committed revocation applies on the next request.
     *
     * @since 2.1.0
     */
    public static function allows(): bool
    {
        if (empty($_SESSION['mgrValidated'])) {
            return false;
        }

        $core = evo();
        $userId = (int) $core->getLoginUserID('mgr');
        if ($userId <= 0 || !$core->hasPermission('settings', 'mgr')) {
            return false;
        }

        return RolePermissions::query()
            ->useWritePdo()
            ->join('user_roles', 'user_roles.id', '=', 'role_permissions.role_id')
            ->join('user_attributes', 'user_attributes.role', '=', 'user_roles.id')
            ->join('users', 'users.id', '=', 'user_attributes.internalKey')
            ->where('users.id', $userId)
            ->where('role_permissions.permission', 'settings')
            ->pluck('role_permissions.permission')
            ->containsStrict('settings');
    }

    /**
     * Stop a denied Livewire request before component hydration or writes can continue.
     *
     * The Evolution manager's exception handler can turn a thrown 403 into a 500.
     * Send the denial and use Evolution's request termination when it is available.
     *
     * @since 2.1.0
     */
    public static function authorize(): void
    {
        if (self::allows()) {
            return;
        }

        $message = __('global.access_permission_denied');
        $response = new Response(is_string($message) ? $message : 'Access permission denied.', Response::HTTP_FORBIDDEN, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
        $response->sendHeaders();
        $response->sendContent();

        if (function_exists('evo_exit')) {
            evo_exit();
        }
        exit;
    }
}
