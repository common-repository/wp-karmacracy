<?php


/**
 * wp_karmacracy_warnings()
 * 
 * Show warnings if the appkey is not inserted
 */
if (!function_exists('wp_karmacracy_warnings') ) {
    function wp_karmacracy_warnings() {
        if ( !wp_karmacracy_is_verified() ) {
            if(!isset($_GET['page']) || $_GET['page'] != 'wp_karmacracy') {
                if (!function_exists('wp_karmacracy_admin_warning') ) {
                    function wp_karmacracy_admin_warning() {
                        echo "
                        <div id='wp-karmacracy-warning' class='updated fade'><p><strong>".__('WP Karmacracy is almost ready.', 'wp_karmacracy')."</strong> ".sprintf(__('You must <a href="%1$s">enter your Karmacracy credentials</a> for it to work.', 'wp_karmacracy'), 'options-general.php?page=wp_karmacracy')."</p></div>
                        ";
                    }
                }
                add_action('admin_notices', 'wp_karmacracy_admin_warning');
            }
        } 
    }
}

/**
 * The WP Karmacracy dashboard widget
 */
if (!function_exists('wp_karmacracy_add_dashboard_widget') ) {
    function wp_karmacracy_dashboard_widget() {
        
        $options = get_option('wp_karmacracy');
        
        $url = 'http://karmacracy.com/api/v1/user/' . $options['username'];
        $params = array(
            'kcy' => '1',
            'num' => '5',
            'appkey' => wp_karmacracy::DEVELOPER_KEY
        );
        
        $request = wp_remote_get(wp_karmacracy_build_get_url_with_params($url, $params));
        
        if(isset($request['response']['code']) && 200 == $request['response']['code'] && isset($request['body']) )
        {
            $body = json_decode($request['body'], true);
            if(isset($body['data']['user'][0]) && !isset($body['error']))
            {
                $user = $body['data']['user'][0];
                wp_karmacracy_dashboard_widget_html($user);
                
                return;
            }
        }

        echo '<p>' . __('Karmacracy API error. Check your API key or try again later.', 'wp_karmacracy') . '</p>';       
    }
}

/**
 * Shows the dashboard widget CSS
 */
if (!function_exists('wp_karmacracy_admin_css') ) {
    function wp_karmacracy_admin_css() {
?>
<style type="text/css">
/*Perfil: img, usuario, palabras, kcyrank*/
#dashboard-widgets #wp-karmacracy-dashboard-widget {font-family: Helvetica,Arial,'Lucida Sans Unicode','Lucida Grande',LucidaGrande,'Lucida Sans',sans-serif;}
#dashboard-widgets #wp-karmacracy-dashboard-widget .human {float:left;width:100%; padding:15px 0 8px 0; color:#fff; text-shadow: 1px 1px #28658b; border-bottom:1px solid #28658b}
#dashboard-widgets #wp-karmacracy-dashboard-widget .humanphoto {float:left; width:14%;margin-right: 10px;}
#dashboard-widgets #wp-karmacracy-dashboard-widget .humanphoto img {vertical-align:middle; padding:3px; background:#fff;}

#dashboard-widgets #wp-karmacracy-dashboard-widget .humanwho {float:left; width:62%; text-align:left;}
#dashboard-widgets #wp-karmacracy-dashboard-widget .humanwho h4 {font-family: Helvetica,Arial,'Lucida Sans Unicode','Lucida Grande',LucidaGrande,'Lucida Sans',sans-serif;font-size:24px; padding-left:4px;}
#dashboard-widgets #wp-karmacracy-dashboard-widget .humanwho li {font-size:14px; font-style:italic;padding-left:4px; color:#fff}
#dashboard-widgets #wp-karmacracy-dashboard-widget .humanwho li a {color:#ddd; text-decoration:none} .humanwho li a:hover {text-decoration:underline}

#wp-karmacracy-dashboard-widget .humankcyrank {float:left; width:20%; text-align:right;}
#wp-karmacracy-dashboard-widget .humankcyrank li {line-height: 26px;}

/*Perfil: Stats*/
#wp-karmacracy-dashboard-widget .stats { margin:0 auto; width:100%;}
#wp-karmacracy-dashboard-widget .humanstats {text-align: center;float:left; width:100%; border-top:1px solid #59a1ce; text-shadow: 1px 1px #28658b}
#wp-karmacracy-dashboard-widget .humanstats li {line-height: 26px;list-style: none;float:left; width:16.65%; padding:15px 0 0 0; color:#fff; font-size:12px}
#wp-karmacracy-dashboard-widget .humanstats_big {font-size:22px;}

#wp-karmacracy-dashboard-widget .tab {background: #3A85B4;padding: 10px;}
#wp-karmacracy-dashboard-widget .clearboth {clear:both;}
#wp-karmacracy-dashboard-widget div.inside {margin:0;}
#wp-karmacracy-dashboard-widget .big {font-size:2.5em;}
#wp-karmacracy-dashboard-widget .stats_big {font-size:22px;}

/*KCYS*/
/*Clase que engloba toda una kcy*/
.mainkcy {float:left; border-bottom:1px dotted #e0e0e0; padding:0 0 14px 0; margin: 8px 0;}

/*Arrow que seÃ±ala la kcy*/
.kcynutarrow {float:left; width:8px;text-align:right; margin-top:26px;}

/*Nuts de una kcy*/
.kcynuts {float:left; width:4em; margin-top: 14px; padding:4px 2px 0 2px; background:#f6f6f6; -moz-border-radius:5px;-webkit-border-radius:5px; -ms-border-radius:5px; text-shadow:#fff 1px 1px; }
.kcynutaward {float:left;  width:4em; text-align:center; color:#999;}
.kcynutaward li.kcynutawardnumber {float:left; font-size:3em; padding:3px 4px 0 4px;}
.kcynutaward li.kcynutawardtext {float:left; text-align:left; font-size:1em; padding:6px 0 0 0;}
.kcynutaward li.kcyawardname { width:100%; padding:4px 0; font-size:.8em; }


/*Share de una kcy mas los kclicks que tiene*/
.kcyshare {float:left; width:6em; margin:0 6px;}
.kcysharebotton {padding-top:10px;}
/*kclicks numero + texto*/
.kcysharenumber {padding:1em 0 0 0; font-size:1.6em; color:#3a85b4; text-align:center;  outline: none; }
.kcysharetext {padding:4px 0; color:#ddd; font-size:20px; font-style:italic; }

/*Kcy content type 1: cuando NO tiene nuts la kcys*/
.postbox .kcycontenttype1 {float:left; width:80%; margin:0 6px; text-align:left;}
.postbox .kcycontenttype1 ul {font-size:1.2em;}
.postbox .kcycontenttype1 li.kcycontentdate {float:left; width:100%; padding-top:8px; font-size:.8em; color:#C9C9C9; font-style:italic}
.postbox .kcycontenttype1 li.kcycontentdate a {color:#999; text-decoration:underline;} .kcycontenttype1 li.kcycontentdate a:hover {text-decoration:none}
.postbox .kcycontenttype1 li.kcycontententtitle {float:left; width:100%; padding-top:5px;}
.postbox .kcycontenttype1 li.kcycontententtitle a {color:#000;font-size:100%; text-decoration:none;}
.postbox .kcycontenttype1 li.kcycontententtitle a:hover {color:#2c739f; text-decoration:underline;}

.postbox .kcycontenttype1 li.kcycontentdomaindetails {float:left; width:100%; padding-top:5px; font-size:.85em; color:#CCC;}
.postbox .kcycontenttype1 li.kcycontentdomaindetails a.details, .kcycontenttype1 li.kcycontentdomaindetails a.share, .kcycontenttype1 li.kcycontentdomaindetails a.delete, .kcycontenttype1 li.kcycontentdomaindetails a.stats 
{color:#999; text-decoration:none; padding-right:10px } 

.postbox .kcycontenttype1 li.kcycontentdomaindetails a.details:hover {color:#000;}
.postbox .kcycontenttype1 li.kcycontentdomaindetails a.share:hover {color:#225593;}
.postbox .kcycontenttype1 li.kcycontentdomaindetails a.delete:hover {color:#e40000;}
.green{color:#45B72C}
#wp-karmacracy-kcys {margin:10px;}
</style>
<?php
    }
}

/**
 * Shows the dashboard widget HTML
 */
if (!function_exists('wp_karmacracy_dashboard_widget_html') ) {
    function wp_karmacracy_dashboard_widget_html($user) {

?>
<div id="wp_karmacracy_dashboard_widget">
  <div class="tab">
      <div class="human">
          <div class="humanphoto">
              <img src="<?php echo $user['img']; ?>" alt="<?php echo $user['username']; ?>" width="80" height="80" /> 
          </div> 
		      <div class="humanwho">
              <h4><?php echo $user['username']; ?></h4>	
              <ul>
                  <li><?php _e('Top words', 'wp_karmacracy'); ?> (<?php echo $user['stats']['topwordscount']; ?>) »
                      <?php 
                      $first = true; 
                      foreach ($user['stats']['topwords5'] as $tag) {
                        if($first) {
                          $first = false;
                        }
                        else {
                          echo ', ';
                        }
                        echo "<a href='http://karmacracy.com/{$user['username']}/words/{$tag}'>{$tag}</a>";
                      }
                      ?>		
                  </li>
              </ul>
		      </div>
		      <div class="humankcyrank">
              <ul>
				          <li>
				              <?php _e('My <strong>kcyrank</strong>', 'wp_karmacracy'); ?>
				              <br />
				              <span title="<?php _e('Global karma position:', 'wp_karmacracy'); ?> <?php echo $user['kcyrank']; ?>" class="big"><?php echo $user['kcyrank']; ?></span>
				          </li>
              </ul>
		      </div>                    
      </div>
      <div class="humanstats">  
          <ul>
                      <li><?php _e('Total <strong>Kcys</strong>', 'wp_karmacracy'); ?><br /><span class="stats_big"><?php echo $user['stats']['totalkcys']; ?></span></li>
                      <li><?php _e('Total <strong>Domains</strong>', 'wp_karmacracy'); ?><br /><span class="stats_big"><?php echo $user['stats']['totalclips']; ?></span></li>
                      <li><?php _e('Total <strong>Kclicks</strong>', 'wp_karmacracy'); ?><br /><span class="stats_big"><?php echo number_format($user['stats']['totalkclicks'], 0); ?></span></li>
                      <li><?php _e('Total <strong>Nuts</strong>', 'wp_karmacracy'); ?><br /><span class="stats_big"><?php echo $user['stats']['totalawards']; ?></span></li>
                      <li><?php _e('My <strong>Circle</strong>', 'wp_karmacracy'); ?><br /><span class="stats_big"><?php echo $user['stats']['humanscircle']; ?></span></li>                  
                      <li><?php _e('My <strong>KOI</strong>', 'wp_karmacracy'); ?><br /><span class="stats_big"><?php echo number_format($user['stats']['koi'], 2); ?></span></li>                
              </ul>
          </div>
      <div class="clearboth"></div>
  </div>
</div>
<div id="wp-karmacracy-kcys">
    <?php if(count($user['kcys'])>0): ?>
        <?php foreach ($user['kcys'] as $kcy): ?>
        <div class="mainkcy">

            <div class="kcyshare">
                <ul>
                    <li class="kcysharenumber"><?php echo $kcy['kclicks']; ?> </li>
                    <li class="kcysharetext">kclicks</li>
                </ul>
            </div>

            <div class="kcycontenttype1">
                <ul>
                    <?php $kcy_id = substr($kcy['shorturl'], strrpos($kcy['shorturl'], '/')+1); ?> 
                    <li class="kcycontentdate"><a target="_blank" href="<?php echo $kcy['shorturl']; ?>"><?php echo substr($kcy['shorturl'], 7); ?></a> &raquo; <?php echo sprintf(__('kcy created on %s', 'wp_karmacracy'), date(__('l, jS \of F \of Y \a\t H:i:s', 'wp_karmacracy'), strtotime($kcy['date']) ) ); ?>  </li>
                    <li class="kcycontententtitle"><a title="<?php echo $kcy['title']; ?>" href="<?php echo $kcy['longurl']; ?>"><?php echo $kcy['title']; ?></a></li>
                    <li class="kcycontentdomaindetails"><span class="green"><?php echo parse_url($kcy['longurl'], PHP_URL_HOST); ?></span> &mdash; <a title="<?php _e('kcy details', 'wp_karmacracy'); ?>" class="details" href="http://karmacracy.com/<?php echo $user['username']; ?>/<?php echo $kcy_id; ?>"><?php _e('details', 'wp_karmacracy'); ?></a>
                        <a title="<?php _e('new kcy or share this kcy', 'wp_karmacracy'); ?>" class="share" href="http://karmacracy.com/<?php echo $user['username']; ?>/share-win?k=<?php echo $kcy_id; ?>"><?php _e('share', 'wp_karmacracy'); ?></a>
                        <a title="<?php _e('kcy stats', 'wp_karmacracy'); ?>" class="stats" href="http://karmacracy.com/<?php echo $user['username']; ?>/<?php echo $kcy_id; ?>?tab=traffic"><?php _e('stats', 'wp_karmacracy'); ?></a>
                    </li>
                </ul>
            </div>

        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <div class="clearboth"></div>
    <?php echo '<p class="textright"><a href="http://karmacracy.com/' . $user['username'] . '" class="button">'. __('View all', 'wp_karmacracy') . '</a></p>'; ?>
</div>
<?php

    }
}

/**
 * Adds the WP Karmacracy dashboard widget
 */
if (!function_exists('wp_karmacracy_add_dashboard_widget') ) {
    function wp_karmacracy_add_dashboard_widget() {
        if ( wp_karmacracy_is_verified() ) {
            add_action('admin_print_styles','wp_karmacracy_admin_css');
            wp_add_dashboard_widget( 'wp-karmacracy-dashboard-widget', 'WP Karmacracy', 'wp_karmacracy_dashboard_widget' );
        }
    }
}