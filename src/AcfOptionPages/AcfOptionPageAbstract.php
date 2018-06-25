<?php

namespace Prophets\WPACF\AcfOptionPages;

use Prophets\WPBase\Base;

abstract class AcfOptionPageAbstract
{
    /**
     * Default option page settings
     * @var array
     */
    static protected $defaultSettings = [
        'capability' => 'manage_options'
    ];

    /**
     * @var array
     */
    protected $settings;
    /**
     * @var Base
     */
    protected $base;

    /**
     * AcfOptionPageAbstract constructor.
     * @param Base $base
     */
    public function __construct(Base $base)
    {
        $this->base = $base;
        add_action('admin_menu', [$this, 'init']);
    }

    /**
     * Initialize the ACF managed option page
     */
    public function init()
    {
        $settings = array_merge(static::$defaultSettings, $this->settings);
        $page = acf_add_options_page($settings);

        if (isset($this->subpages) && is_array($this->subpages)) {
            foreach ($this->subpages as $subpage) {
                $subpage['parent_slug'] = $page['menu_slug'];
                acf_add_options_sub_page($subpage);
            }
        }
    }
}
