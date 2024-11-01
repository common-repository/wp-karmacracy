<?php

/**
 * wp_karmacracy_is_verified()
 *
 * Verifies if the user has inserted de appkey
 */
if (!function_exists('wp_karmacracy_is_verified') ) {
    function wp_karmacracy_is_verified() {

        $options = get_option('wp_karmacracy');

        if ( isset($options['password']) && '' != $options['password'] && isset($options['verified']) && true == $options['verified'] ) {

            return true;
        }

        return false;
    }
}

/**
 * Creates a GET URL with the base URL and the parameters
 */
if (!function_exists('wp_karmacracy_build_get_url_with_params') ) {
    function wp_karmacracy_build_get_url_with_params($url, $params) {
        $finalUrl = $url;
        if(is_array($params))
        {
            $finalUrl .= '?';
            $first = true;
            foreach($params as $key=>$param)
            {
                if($first)
                    $first = false;
                else
                    $finalUrl .= '&';

                $finalUrl .= $key . '=' . urlencode($param);
            }
        }

        return $finalUrl;
    }
}

/**
 * Plugin localization
 */
if (!function_exists('wp_karmacracy_localization') ) {
    function wp_karmacracy_localization() {
        // Internationalizing the plugin
        $currentLocale = get_locale();
        if(!empty($currentLocale))
        {
          $moFile = dirname(__FILE__) . '/lang/' . $currentLocale . '.mo';
          //echo $moFile;
          if(@file_exists($moFile) && is_readable($moFile))
          {
              load_textdomain('wp_karmacracy', $moFile);
          }
        }
    }
}

