<?php

declare(strict_types=1);

namespace InSquare\OpendxpFaviconBundle;

use OpenDxp\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use OpenDxp\Model\User\Permission\Definition;

final class Installer extends SettingsStoreAwareInstaller
{
    private const USER_PERMISSIONS_CATEGORY = 'InSquare Favicon Bundle';
    private const USER_PERMISSIONS = [
        'favicon_settings',
    ];

    public function install(): void
    {
        $this->addUserPermission();
        parent::install();
    }

    public function uninstall(): void
    {
        $this->removeUserPermission();
        parent::uninstall();
    }

    private function addUserPermission(): void
    {
        foreach (self::USER_PERMISSIONS as $permission) {
            $definition = Definition::create($permission);
            $definition->setCategory(self::USER_PERMISSIONS_CATEGORY);
            $definition->save();
        }
    }

    private function removeUserPermission(): void
    {
        $db = \OpenDxp\Db::get();

        foreach (self::USER_PERMISSIONS as $permission) {
            $db->delete('users_permission_definitions', [
                'key' => $permission,
            ]);
        }
    }
}
