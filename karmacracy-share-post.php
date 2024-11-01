<?php

/**
 * wp_karmacracy_share_post()
 * 
 * Share published posts to your networks
 */
if (!function_exists('wp_karmacracy_share_post') ) {
    function wp_karmacracy_share_post ($postID) {
        
        $options = get_option('wp_karmacracy');

        // Check share option
        if ( isset($options['share']) && 1 == $options['share'] && wp_karmacracy_is_verified() ) {
            
            if (!wp_is_post_revision($postID)) {

                $post = get_post ($postID);
                
                $networks = wp_karmacracy_get_networks($options);
                if(count($networks) > 0) {
                    
                    // Kcy the post URL
                    $params = array(
                        'format' => 'json',
                        'u' => $options['username'],
                        'key' => $options['password'],
                        'url' => get_permalink ($postID)
                    );
                    $kcy = false;
                    $request = wp_remote_get(wp_karmacracy_build_get_url_with_params(wp_karmacracy::LINK_API_BASE_URL, $params));
                    if (!is_wp_error($request)) {
                        $body = json_decode(wp_remote_retrieve_body($request));
                        if (is_object($body) && isset($body->status_code) && $body->status_code == '200') {
                            $kcy = substr($body->data->url, strrpos($body->data->url, '/')+1);
                        }
                    }

                    // If we kcy the post correctly
                    if ($kcy) {
                        $sharetext = sprintf (__('I just published %s', 'wp_karmacracy') . ' ' . $body->data->url, $post->post_title);
                        $parameters = array(
                            'u' => $options['username'],
                            'k' => $options['password'],
                            'appkey' => wp_karmacracy::DEVELOPER_KEY,
                            'txt' => $sharetext,
                            'kcy' => $kcy  
                        );

                        foreach($networks as $network) {
                            $parameters['where'] = $network['type'] . '_' . $network['connectid'];
                            wp_remote_get(wp_karmacracy_build_get_url_with_params('http://karmacracy.com/api/v1/share/',$parameters));
                        }    
                    }
                }
            }
        }
    }
}

/**
 * wp_karmacracy_get_networks()
 * 
 * Get networks' conections from Karmacracy
 */
if (!function_exists('wp_karmacracy_get_networks') ) {
    function wp_karmacracy_get_networks ($options) {
        
        $networks = array();

        $url = 'http://karmacracy.com/api/v1/networks/';
        $params = array(
            'u' => $options['username'],
            'k' => $options['password'],
            'appkey' => wp_karmacracy::DEVELOPER_KEY
        );

        $request = wp_remote_get(wp_karmacracy_build_get_url_with_params($url, $params));

        if(isset($request['response']['code']) && 200 == $request['response']['code'] && isset($request['body']) )
        {
            $body = json_decode($request['body'], true);
            if(isset($body['data']['network']) && !isset($body['error']))
            {
                $networks = $body['data']['network'];
            }
        }
        
        return $networks;
    }
}

