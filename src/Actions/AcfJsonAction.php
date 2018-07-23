<?php

namespace Prophets\WPACF\Actions;

use Prophets\WPBase\Actions\ActionAbstract;
use Prophets\WPBase\HookManager;
use Prophets\WPACF\AcfManager;
use Prophets\WPBase\PluginRepository;

/**
 * Class AcfJsonAction
 * @package Prophets\WPACF\Actions
 */
class AcfJsonAction extends ActionAbstract
{
    /**
     * @var array
     */
    protected $fieldGroupContext;

    /**
     * Only allow editing of ACF fields when in development mode and authenticatedGkk as a super admin.
     *
     * @return bool
     */
    public static function isEditMode()
    {
        return WP_ENV === 'development' && is_super_admin();
    }

    /**
     * Initialize the filters when in "edit mode" or else just load the fields.
     *
     * Also add a filter to hide ACF admin navigation when not a super admin.
     *
     * @return void
     */
    public function init()
    {
        $showAdmin = false;
        $hookManager = new HookManager();

        if (self::isEditMode()) {
            $hookManager->addHook('action', [
                'name'     => 'acf/update_field_group',
                'use'      => [$this, 'setFieldGroupContext'],
                'priority' => 1,
            ]);
            $hookManager->addHook('filter', [
                'name'     => 'acf/settings/save_json',
                'use'      => [$this, 'saveJsonFilter'],
                'priority' => 99,
            ]);
            $hookManager->addHook('filter', [
                'name'     => 'acf/settings/load_json',
                'use'      => [$this, 'loadJsonFilter'],
                'priority' => 99,
            ]);
            if (is_super_admin()) {
                $showAdmin = true;
            }
        } else {
            $this->loadFields();
        }
        $hookManager->addHook('filter', [
            'name' => 'acf/settings/show_admin',
            'use'  => function () use ($showAdmin) {
                return $showAdmin;
            }
        ]);
    }

    /**
     * @return AcfManager
     */
    public function getAcfManager()
    {
        return PluginRepository::getInstance()->getPlugin('acf');
    }

    /**
     * Get the paths where ACF group json files are located.
     *
     * @return array
     */
    protected function getStoragePaths()
    {
        return $this->getAcfManager()->getStoragePaths();
    }

    /**
     * Return json storage path.
     *
     * @param string $path
     *
     * @return null|string
     */
    public function saveJsonFilter($path)
    {
        if (($key = $this->getValueFromContext('key')) !== null) {
            return $this->getAcfManager()->getStoragePathForGroupKey($key) ?: $path;
        }

        return $path;
    }

    /**
     * Set the ACF field group context.
     *
     * @param array $fieldGroup
     */
    public function setFieldGroupContext(array $fieldGroup)
    {
        $this->fieldGroupContext = $fieldGroup;
    }

    /**
     * Get a value from ACF field group context.
     *
     * @param string $name
     * @param null $default
     *
     * @return mixed|null
     */
    public function getValueFromContext($name, $default = null)
    {
        return $this->fieldGroupContext[$name] ?? $default;
    }

    /**
     * Prepend AcfManager storage paths to the stack, making the manager's storage paths
     * have priority over regular defined paths.
     *
     * @param array $paths
     *
     * @return array
     */
    public function loadJsonFilter(array $paths)
    {
        $paths = array_merge($this->getStoragePaths(), $paths);

        return $paths;
    }

    /**
     * Load the ACF fields from all the json files located in the storage paths.
     *
     * @return void
     */
    public function loadFields()
    {
        foreach ($this->getStoragePaths() as $path) {
            $this->loadFieldsFromDir($path);
        }
    }

    /**
     * Load the ACF fields from all the json files located in a directory.
     *
     * @param string $path
     */
    protected function loadFieldsFromDir($path)
    {
        foreach (AcfManager::getJsonFilesFromDir($path) as $file) {
            acf_add_local_field_group(json_decode(file_get_contents($file->getPathname()), true));
        }
    }
}
