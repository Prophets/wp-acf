<?php
/**
 * Plugin Name: Prophets ACF extension
 * Author: Stijn Huyberechts
 * Text Domain: prophets_acf
 * Code standard: PSR2
 */

use Prophets\WPBase\PluginRepository;

PluginRepository::getInstance()
	->registerPlugin(AcfManager::class, 'acf', __DIR__ . '/config/base.php');