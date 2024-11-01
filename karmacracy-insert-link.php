<?php


/**
 * Class for managing the kcy links inserts in post & pages
 */
if (!class_exists('wp_karmacracy')) {

    class wp_karmacracy {

        const LINK_API_BASE_URL = 'http://kcy.me/api/';
        const API_BASE_URL = 'http://karmacracy.com/api/v1/';
        const DEVELOPER_KEY = 'artb3rri4rt'; // Don't change this Key.
                                             // You will be able to add your karmacracy third party key
                                             // in the settings panel
        const WIDGET_VERSION='1.2';

        //private
        // TODO
        private $admin_options = array();
        private $plugin_url = '';
        private $plugin_dir = '';
        private $plugin_info = array();

        private $widget_options = array();

        /**
         * __construct()
         *
         * Class constructor
         *
         */
        function __construct() {

            //Initialize plugin information
            $this->plugin_info = array(
                'slug' => 'wp_karmacracy',
                'version' => '1.1',
                'name' => 'WP Karmacracy',
                'url' => 'http://www.berriart.com/en/wp-karmacracy/',
                'locale' => 'wp_karmacracy',
                'path' => plugin_basename(__FILE__)
            );

            $this->admin_options = $this->get_admin_options();
            $this->plugin_url = rtrim(plugin_dir_url(__FILE__), '/');
            $this->plugin_dir = rtrim(plugin_dir_path(__FILE__), '/');

            add_action('init', array(&$this, 'init'));
            add_action('wp_ajax_wp_karmacracy_add', array(&$this, 'ajax_karmacracy'));
            add_action('wp_ajax_wp_karmacracy_get_link', array(&$this, 'ajax_get_link'));

            //Check if widget is active, and if it is, set the filter. Widget options are in "wp_karmacracy-widget" group.
            $options=get_option($this->get_plugin_info('slug')."-widget");
            if ($options["widget_active"]) {
            	switch ($options["widget_location"]) {
            		case "manual": 	break;
            		case "title":	$filter="the_title"; break;
            		case "body":
            		case "beforebody":
            		default: 		$filter="the_content"; break;
            	}
            	if ($filter!="") {
            		add_filter($filter,array(&$this,'widget_filter'));
            	}
            }
            $this->widget_options=$options;
        }

        //end constructor

        public function ajax_get_link() {
            // Prevent direct form
            check_ajax_referer('wp_karmacracy_get_link');
            //Sanitize form data
            parse_str($_POST['form_data'], $form_data);
            $url = trim(sanitize_text_field($form_data['url']));
            $text = trim(sanitize_text_field($form_data['text']));

            if (empty($url))
                die(json_encode(array('error' => __('URL cannot be blank', $this->get_plugin_info('locale')))));
            if (empty($text))
                die(json_encode(array('error' => __('Text cannot be blank', $this->get_plugin_info('locale')))));

            //Prepare the remote request
            $params = array(
                'format' => 'json',
                'u' => $this->get_admin_option('username'),
                'key' => $this->get_admin_option('password'),
                'url' => $url
            );

            $request = wp_remote_get($this->build_get_url_with_params(self::LINK_API_BASE_URL, $params));
            if (is_wp_error($request)) {
                die(json_encode(array('error' => $request->get_error_message())));
            }
            $body = json_decode(wp_remote_retrieve_body($request));
            if (!is_object($body)) {
                die(json_encode(array('error' => __('Unable to retrieve short URL', $this->get_plugin_info('locale')))));
            }
            if ( !isset($body->status_code) || $body->status_code != '200') {
                die(json_encode(array('error' => __('Karmacracy API error. Check your API key or try again later.', $this->get_plugin_info('locale')))));
            }

            if (isset($body->data->url)) {
                $return = sprintf("<a href='%s'>%s</a>", $body->data->url, $text);
                die(json_encode(array('link' => $return)));
            } else {
                die(json_encode(array('error' => __('Unable to retrieve short URL, try again later.', $this->get_plugin_info('locale')))));
            }
            exit;
        }
        //end ajax_get_link

        protected function build_get_url_with_params($url, $params) {

            return wp_karmacracy_build_get_url_with_params($url, $params);
        }

        public function ajax_karmacracy() {
            ?>
            <html>
                <head>
                    <title><?php _e('Insert Karmacracy Link', $this->get_plugin_info('locale')); ?></title>
                    <?php
                    wp_enqueue_script('jquery');
                    wp_admin_css('global');
                    wp_admin_css();
                    wp_admin_css('colors');
                    do_action('admin_print_styles');
                    do_action('admin_print_scripts');
                    do_action('admin_head');
                    ?>
                    <script type="text/javascript">
                        var ajaxurl = "<?php echo esc_js(admin_url('admin-ajax.php')); ?>";
                        jQuery( document ).ready(
                        function( $ ) {
                            //Get into Ajax format
                            $( "#wp_karmacracy_form" ).bind( "submit", function() {
                                var form_data = $( this ).serializeArray();
                                var action = $( "#action" ).val();
                                var nonce = $( "#_wpnonce" ).val();
                                form_data = $.param(form_data);
                                $.post( ajaxurl, { action: action, form_data: form_data, _ajax_nonce: nonce },
                                function( response ){
                                    if ( typeof response.error != 'undefined' )  {
                                        $( "#form_error p" ).html( response.error );
                                        $( "#form_error" ).removeClass( "hidden" );
                                        return;
                                    } //end if error
                                    var win = parent.window.dialogArguments || parent.opener || parent.parent || parent.top;
                                    win.send_to_editor( response.link );
                                } //end ajax response
                                , 'json' ); //end ajax
                                return false;
                            } );
                        }
                    );
                    </script>
                </head>
                <body>
            <?php
            if ($this->get_admin_option('verified') != true) {
                ?>
                        <div class='error'><p><strong><?php _e('Your Karmacracy API credentials have not been verified', $this->get_plugin_info('locale')); ?></strong></p></div>
                <?php
            } else {
                ?>
                        <div class='wrap'>
                            <form id="wp_karmacracy_form" method='post' action='<?php esc_url(admin_url('admin-ajax.php')); ?>'>
                                <?php wp_nonce_field('wp_karmacracy_get_link') ?>
                                <h2><?php _e('New Kcy Link', $this->get_plugin_info('locale')); ?></h2>
                                <table class="form-table">
                                    <tbody>
                                        <tr valign="top">
                                            <th scope="row"><label for='url'><?php _e('Long URL (Required)', $this->get_plugin_info('locale')); ?></label></th>
                                            <td>
                                                <input type='text' size='30' name='url' id='url' value='' />   <br />
                                            <?php _e('The URL that you want to share in a short way.', $this->get_plugin_info('locale')); ?>
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row"><label for='text'><?php _e('Text (Required)', $this->get_plugin_info('locale')); ?></label></th>
                                            <td>
                                                <input type='text' size='30' name='text' id='text' value='' />  <br />
                                            <?php _e('The anchor text of the link.', $this->get_plugin_info('locale')); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="submit">
                                    <input class='button-primary' type="submit" name="update" value="<?php esc_attr_e('Insert Link', $this->get_plugin_info('locale')) ?>" />
                                </div><!--/.submit-->
                                <input type='hidden' name='action' id='action' value='wp_karmacracy_get_link' />
                                <div class='error hidden' id='form_error'><p></p></div>
                            </form>

                        </div><!--/wrap-->
                <?php
            } //end if verified
            ?>
                </body>
            </html>
            <?php
            exit;
        }
        //end ajax_yourls

        /**
         * add_media_button()
         *
         * Displays a thumbsup icon in the media bar when editing a post or page
         *
         * @param		string    $context	Media bar string
         * @return		string    Updated context variable with shortcode button added
         */
        public function add_media_button($context) {
            if ( $this->get_admin_option( 'verified' ) != true )
                    return $context;

            $image_btn = $this->get_plugin_url('karmacracy.png');

            $out = '<a href="' . esc_url(admin_url('admin-ajax.php')) . '?action=wp_karmacracy_add&width=450&height=300&TB_iframe=true" class="thickbox" title="' . __("Add Karmacracy Link", $this->get_plugin_info('locale')) . '"><img src="' . $image_btn . '" alt="' . __("Add Karmacracy Link", $this->get_plugin_info('locale')) . '" /></a>';
            return $context . $out;
        }
        //end add_media_button

        /**
         * add_settings_page()
         *
         * Adds an options page to the admin panel area
         *
         * */
        public function add_settings_page() {
            add_options_page('WP Karmacracy Settings', 'WP Karmacracy', 'manage_options', 'wp_karmacracy', array(&$this, 'output_settings'));
        }
        //end add_settings_page

        /**
         * get_admin_option()
         *
         * Returns a localized admin option
         *
         * @param   string    $key    Admin Option Key to Retrieve
         * @return   mixed                The results of the admin key.  False if not present.
         */
        public function get_admin_option($key = '') {
            $admin_options = $this->get_admin_options();
            if (array_key_exists($key, $admin_options)) {
                return $admin_options[$key];
            }
            return false;
        }
        //end get_admin_option

        /**
         * get_admin_options()
         *
         * Initialize and return an array of all admin options
         *
         * @return   array All Admin Options
         */
        public function get_admin_options() {

            if (empty($this->admin_options)) {
                $admin_options = $this->get_plugin_defaults();

                $options = get_option($this->get_plugin_info('slug'));
                if (!empty($options)) {
                    foreach ($options as $key => $option) {
                        if (array_key_exists($key, $admin_options)) {
                            $admin_options[$key] = $option;
                        }
                    }
                }

                //Save the options
                $this->admin_options = $admin_options;
                $this->save_admin_options();
            }
            return $this->admin_options;
        }
        //end get_admin_options

        /**
         * get_all_admin_options()
         *
         * Returns an array of all admin options
         *
         * @return   array					All Admin Options
         */
        public function get_all_admin_options() {
            return $this->admin_options;
        }
        // end get_all_admin_options

        /**
         * get_plugin_defaults()
         *
         * Returns an array of default plugin options (to be stored in the options table)
         *
         * @return		array               Default plugin keys
         */
        public function get_plugin_defaults() {
            if (isset($this->defaults)) {
                return $this->defaults;
            } else {
                $this->defaults = array(
                    'username' => false,
                    'password' => false,
                    'verified' => false,
                    'share' => 1
                );

                return $this->defaults;
            }
        }
        //end get_plugin_defaults

        /**
         * get_plugin_dir()
         *
         * Returns an absolute path to a plugin item
         *
         * @param		string    $path	Relative path to make absolute (e.g., /css/image.png)
         * @return		string               An absolute path (e.g., /htdocs/ithemes/wp-content/.../css/image.png)
         */
        public function get_plugin_dir($path = '') {
            $dir = $this->plugin_dir;
            if (!empty($path) && is_string($path))
                $dir .= '/' . ltrim($path, '/');
            return $dir;
        }
        //end get_plugin_dir

        /**
         * get_plugin_info()
         *
         * Returns a localized plugin key
         *
         * @param   string    $key    Plugin Key to Retrieve
         * @return   mixed                The results of the plugin key.  False if not present.
         */
        public function get_plugin_info($key = '') {
            if (array_key_exists($key, $this->plugin_info)) {
                return $this->plugin_info[$key];
            }
            return false;
        }
        //end get_plugin_info

        /**
         * get_plugin_url()
         *
         * Returns an absolute url to a plugin item
         *
         * @param		string    $path	Relative path to plugin (e.g., /css/image.png)
         * @return		string    An absolute url (e.g., http://www.domain.com/plugin_url/.../css/image.png)
         */
        public function get_plugin_url($path = '') {
            $dir = $this->plugin_url;
            if (!empty($path) && is_string($path))
                $dir .= '/' . ltrim($path, '/');
            return $dir;
        }
        //get_plugin_url

        /**
         * init()
         *
         * Initializes plugin localization, post types, updaters, plugin info, and adds actions/filters
         *
         */
        function init() {

            //Add plugin info
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            $this->plugin_info = wp_parse_args(array_change_key_case(get_plugin_data(__FILE__, false, false), CASE_LOWER), $this->plugin_info);

            //Media bar
            add_action('media_buttons_context', array(&$this, 'add_media_button'));

            //Admin menu
            add_action('admin_menu', array(&$this, 'add_settings_page'));
        }
        //end function init

        public function output_settings() {
            if (isset($_POST['update'])) {
                check_admin_referer('wp_karmacracy_save_settings');
                $options = array();
                $options['username'] = sanitize_text_field($_POST['username']);
                $options['password'] = sanitize_text_field($_POST['password']);
                $options['share'] = sanitize_text_field($_POST['share']);
                $this->save_admin_options($options);

                //Check Karmacracy Key
                $params = array(
                    'u' => $options['username'],
                    'key' => $options['password'],
                    'appkey' => self::DEVELOPER_KEY
                );
                $check_url = self::API_BASE_URL . 'key/check?';
                $first = true;
                foreach($params as $key => $param)
                {
                    if($first)
                    {
                        $first = false;
                    }
                    else
                    {
                        $check_url .= '&';
                    }
                    $check_url .= $key . '=' . urlencode($param);
                }

                $request = wp_remote_get($check_url);

                $error = false;
                if (is_wp_error($request)) {
                    $error = true;
                } else {
                    $body = wp_remote_retrieve_body($request);
                    $body = json_decode($body);
                    if (is_object($body)) {
                        if (!isset($body->ok) || $body->ok != 1)
                            $error = true;
                    } else {
                        $error = true;
                    }
                }
                if ($error) {
                    $this->save_admin_option('verified', false);
                    ?>
                    <div class='error'><p><strong><?php _e('Unable to connect to your Karmacracy API.  Please check your settings or try again later.', $this->get_plugin_info('locale')); ?></strong></p></div>
                    <?php
                } else {
                    $this->save_admin_option('verified', true);
                    ?>
                    <div class='updated'><p><strong><?php _e('Settings Saved', $this->get_plugin_info('locale')); ?></strong></p></div>
                    <?php
                }

            } //end if $_POST['update']
            ?>
            <div class="wrap">
                <h2><?php _e("WP Karmacracy Settings", $this->get_plugin_info('locale')); ?></h2>
                <form method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>">
                    <?php wp_nonce_field('wp_karmacracy_save_settings') ?>
                    <table class="form-table">
                        <tbody>
                            <tr valign='top'>
                                <th scope="row"><label for='username'><?php _e('Karmacracy Login (Username)', $this->get_plugin_info('locale')); ?></label></th>
                                <td  valign='middle'>
                                    <input type='text' size='30' id='username' name='username' value='<?php echo esc_attr($this->get_admin_option('username')); ?>' />
                                </td>
                            </tr>
                            <tr valign='top'>
                                <th scope="row"><label for='password'><?php _e('Karmacracy Third Party Key (appkey)', $this->get_plugin_info('locale')); ?></label></th>
                                <td  valign='middle'>
                                    <input type='password' size='30' id='password' name='password' value='<?php echo esc_attr($this->get_admin_option('password')); ?>' /><br />
                                    <?php _e('You can check your key in the following link:', $this->get_plugin_info('locale')); ?> <a target="_blank" href="http://www.karmacracy.com/settings?t=connections">http://www.karmacracy.com/settings</a>
                                </td>
                            </tr>
                            <tr valign='top'>
                                <th scope="row" colspan="2"><input type='checkbox' id='share' name='share' value='1' <?php if($this->get_admin_option('share')) echo 'checked="checked"'; ?> /> <label for='share'><?php _e('Post updates to your twitter or FB account when you create a fresh post.', $this->get_plugin_info('locale')); ?></label></th>
                            </tr>
                            <tr valign='top'>
                                <td  colspan="2" valign='middle'>

                                    <?php _e('Configure your share options in Karmacracy first if you haven\'t done it yet', $this->get_plugin_info('locale')); ?> <a target="_blank" href="http://karmacracy.com/settings?t=networks">http://www.karmacracy.com/settings</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="submit">
                        <input class='button-primary' type="submit" name="update" value="<?php esc_attr_e('Check and Save Settings', $this->get_plugin_info('locale')) ?>" />
                    </div><!--/.submit-->
                </form>


                                    <?php
                                    //HOOK WITH KARMACRACY-WIDGET; by (karmacracy - Sept 2011)
									include dirname(__FILE__)."/karmacracy-widget.php";
                                    ?>

                <div class="donatediv" style="max-width:740px;">
                    <h3><?php _e('You can invite me for a coffee', $this->get_plugin_info('locale')); ?></h3>
                    <form style="float:left;margin: 10px;" class="donateform" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_s-xclick">

                    <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
                    ">
                    <input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/es_ES/i/scr/pixel.gif" width="1" height="1">
                    </form>

                    <p><?php _e('Do you like this plugin? Have you used it? Have you enjoy it? Althought the plugin is free, if you answer yes to one of this questions, I\'d appreciate you invite me for a coffee.', $this->get_plugin_info('locale')); ?></p>
                    <p><?php _e('I also develop other plugins & themes:', $this->get_plugin_info('locale')); ?> <a href="http://www.berriart.com/recursos/">http://www.berriart.com/recursos/</a></p>
                </div>


            </div><!--/.wrap-->
            <?php
        }
        //end output_settings

        /**
         * save_admin_option()
         *
         * Saves an individual option to the options array
         * @param		string    	$key		Option key to save
         * @param		mixed		$value	Value to save in the option
         */
        public function save_admin_option($key = '', $value = '') {
            $this->admin_options[$key] = $value;
            if($this->admin_options['share'] != 1)
                    $this->admin_options['share'] = 0;
            $this->save_admin_options();
            return $value;
        }
        //end save_admin_option

        /**
         * save_admin_options()
         *
         * Saves a group of admin options to the options table
         * @param		array    	$admin_options		Optional array of options to save (are merged with existing options)
         */
        public function save_admin_options($admin_options = false) {
            if (!empty($this->admin_options)) {
                if (is_array($admin_options)) {
                    $this->admin_options = wp_parse_args($admin_options, $this->admin_options);
                }
                update_option($this->get_plugin_info('slug'), $this->admin_options);
            }
        }
        //end save_admin_options




        /**
         * widget_filter($content), puts the widget html in the content.
         *
         */
        public function widget_filter($content) {
        	if (is_single()) {
        		$widget=$this->get_widget_html();
        		        		if ($this->widget_options["widget_location"]=="beforebody") {
        			return $widget.$content;
        		} else {
        			return $content.$widget;
        		}
        	}
        	return $content;

        }
        //end widget_filter

        public function get_widget_html() {
        	$id=get_the_ID();
        	$kcyJsUrl="http://rodney.karmacracy.com/widget-".self::WIDGET_VERSION."/?id=".$id;
        	$kcyJsUrl.="&type=h";
        	$kcyJsUrl.="&width=".(($this->widget_options["widget_width"])?($this->widget_options["widget_width"]):"700");
        	$kcyJsUrl.="&sc=".(($this->widget_options["widget_sc"])?"1":"0");
        	$kcyJsUrl.="&rb=".(($this->widget_options["widget_rb"])?"1":"0");
        	$kcyJsUrl.="&c1=".(($this->widget_options["widget_color1"])?($this->widget_options["widget_color1"]):"74a3be");
        	$kcyJsUrl.="&c2=".(($this->widget_options["widget_color2"])?($this->widget_options["widget_color2"]):"6694ae");
        	$kcyJsUrl.="&c3=".(($this->widget_options["widget_color3"])?($this->widget_options["widget_color3"]):"f2f2f2");
        	$kcyJsUrl.="&c4=".(($this->widget_options["widget_color4"])?($this->widget_options["widget_color4"]):"ffffff");
        	$kcyJsUrl.="&c5=".(($this->widget_options["widget_color5"])?($this->widget_options["widget_color5"]):"040404");
        	$kcyJsUrl.="&c6=".(($this->widget_options["widget_color6"])?($this->widget_options["widget_color6"]):"ffffff");
        	$kcyJsUrl.="&c7=".(($this->widget_options["widget_color7"])?($this->widget_options["widget_color7"]):"6694ae");
        	$kcyJsUrl.="&c8=".(($this->widget_options["widget_color8"])?($this->widget_options["widget_color8"]):"040404");
        	$kcyJsUrl.="&url=".urlencode(get_permalink());
        	$widget="<div class=\"kcy_karmacracy_widget_h_$id\"></div><script defer=\"defer\" src=\"$kcyJsUrl\"></script>";
        	return $widget;
        }
    }
    //end class
}
// end if

if (!function_exists('wp_karmacracy_instantiate') ) {
	function wp_karmacracy_instantiate() {
		global $wp_karmacracy;
		$wp_karmacracy = new wp_karmacracy();
	}
}

if (!function_exists('wp_karmacracy_widget_html') ) {
	function wp_karmacracy_widget_html() {
		global $wp_karmacracy;
		echo $wp_karmacracy->get_widget_html();
	}
}