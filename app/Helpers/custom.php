<?php

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        $arrKey = explode(".", $key);
        if($arrKey[0] == "config-variables"){
            $newkey = env("BRAND_NAME").".".$key;
            $varValue = app('config')->get($newkey, $default);
            if(!empty($varValue)){
                return $varValue;
            }
        }

        
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }

    function selectedSettingTab($selectedTab, $tabClass) {
        return $selectedTab == $tabClass ? ' active' : '';
    }

    function showVehicleSelectedTab($selectedTab, $tabClass) {
        return $selectedTab == $tabClass ? ' active' : '';
    }

    function showTelematicsSelectedTab($selectedTab, $tabClass) {
        return $selectedTab == $tabClass ? ' active' : '';
    }

}


?>