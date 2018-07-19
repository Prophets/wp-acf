<?php

namespace Prophets\WPACF;

class AcfManager
{
    /**
     * @var array
     */
    private $localJsonPaths = [];

	/**
	 * Add a path with ACF json fields.
	 *
	 * @param $path
	 */
	public function addLocalJsonPath($path)
	{
		$this->localJsonPaths[$path] = array_map(function ($value) {
			return basename($value, '.json');
		}, self::getJsonFilesFromDir($path));
	}

	/**
	 * Get the paths where ACF group json files are located.
	 *
	 * @return array
	 */
	public function getStoragePaths()
	{
		return array_keys($this->localJsonPaths);
	}

	/**
	 * @param $groupKey
	 *
	 * @return null|string
	 */
	public function getStoragePathForGroupKey($groupKey)
	{
		if (empty($groupKey)) {
			return null;
		}
		foreach ($this->localJsonPaths as $path => $groupKeys) {
			if (in_array($groupKey, $groupKeys)) {
				return $path;
			}
		}

		return null;
	}

	/**
	 * Get the ACF json files located in a directory.
	 *
	 * @param string $path
	 * @return array
	 */
	public static function getJsonFilesFromDir($path)
	{
		$files = [];

		if (is_dir($path)) {
			foreach (new \DirectoryIterator($path) as $file) {
				if ($file->getExtension() === 'json') {
					$files[] = $file->getPathname();
				}
			}
		}

		return $files;
	}
}
