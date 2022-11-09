<?php
/**
 * @author  omibeaver
 * Booking Hoos list
 */
class omiHooks
{









    public static function closeUpdate(){
        add_filter('pre_site_transient_update_core', function ($a){return null;});
        add_filter('pre_site_transient_update_plugins',  function ($a){return null;});
        add_filter('pre_site_transient_update_themes',  function ($a){return null;});
        remove_action('admin_init', '_maybe_update_core');
        remove_action('admin_init', '_maybe_update_plugins');
        remove_action('admin_init', '_maybe_update_themes');
    }





}