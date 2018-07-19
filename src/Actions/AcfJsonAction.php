<?php

namespace Prophets\WPACF\Actions;

use Prophets\WPBase\Actions\ActionAbstract;
use Prophets\WPBase\HookManager;
use Prophets\WPACF\AcfManager;

class AcfJsonAction extends ActionAbstract
{
	protected $currentFieldGroup;

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
		        'name' => 'acf/update_field_group',
		        'use'  => [$this, 'setCurrentFieldGroup']
	        ]);
            $hookManager->addHook('filter', [
                'name' => 'acf/settings/save_json',
                'use'  => [$this, 'saveJsonFilter']
            ]);
            $hookManager->addHook('filter', [
                'name' => 'acf/settings/load_json',
                'use'  => [$this, 'loadJsonFilter']
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
	    return $this->pluginRepository->getPlugin('acf');
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
     * @return string|null
     */
    public function saveJsonFilter()
    {
        return $this->getAcfManager()->getStoragePathForGroupKey($this->currentFieldGroup);
    }

	/**
	 * Set the current ACF field group key.
	 *
	 * @param string $groupKey
	 */
	public function setCurrentFieldGroup($groupKey)
    {
    	$this->currentFieldGroup = $groupKey;
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
		foreach (AcfManager::getJsonFilesFromDir($path) as $file) {
			acf_add_local_field_group(json_decode(file_get_contents($file->getPathname()), true));
		}
    }
}
