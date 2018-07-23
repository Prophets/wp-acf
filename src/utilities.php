<?php

if ( ! function_exists('prophets_acf_get_local_field')) {
    /**
     * Extend local ACF functionality with a field search by name
     *
     * @param $name
     *
     * @return mixed|null
     */
    function prophets_acf_get_local_field($name)
    {
        $localFields = acf()->local;

        foreach ($localFields->fields as $field) {
            if ($field['name'] === $name) {
                return $field;
            }
        }

        return null;
    }
}
