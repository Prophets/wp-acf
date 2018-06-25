<?php

namespace Prophets\WPACF\Actions;

use Prophets\WPBase\Actions\ActionAbstract;

class AcfJsonAction extends ActionAbstract
{
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

        if (self::isEditMode()) {
            $this->base->addHook('filter', [
                'name' => 'acf/settings/save_json',
                'use' => [$this, 'saveJsonFilter']
            ]);
            $this->base->addHook('filter', [
                'name' => 'acf/settings/load_json',
                'use' => [$this, 'loadJsonFilter']
            ]);
            if (is_super_admin()) {
                $showAdmin = true;
            }
        } else {
            $this->loadFields();
        }
        $this->base->addHook('filter', [
            'name' => 'acf/settings/show_admin',
            'use' => function () use ($showAdmin) {
                return $showAdmin;
            }
        ]);
    }

    /**
     * Get the path where ACF group json files are located.
     *
     * @return array
     */
    protected function getStoragePaths()
    {
        return $this->base->getConfig()->get('acf')['localJsonPaths'] ?? [];
    }

    /**
     * Return json storage path.
     *
     * @return string|null
     */
    public function saveJsonFilter()
    {
        return $this->getStoragePaths()[0] ?? null;
    }

    /**
     * Remove the default json storage path and our own to the stack.
     *
     * @param array $paths
     *
     * @return array
     */
    public function loadJsonFilter(array $paths)
    {
        $paths = array_merge($paths, $this->getStoragePaths());

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
        if (is_dir($path)) {
            foreach (new \DirectoryIterator($path) as $file) {
                if ($file->getExtension() === 'json') {
                    acf_add_local_field_group(json_decode(file_get_contents($file->getPathname()), true));
                }
            }
        }
    }
}
