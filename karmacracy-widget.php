<?php
//KARMACRACY WIDGET EXTENSION (called from karmacracy-insert-link inside wp_karmacracy::output_settings()

//UPDATE
if (isset($_POST["widgetupdate"])) {
	check_admin_referer('wp_karmacracy_save_widget_settings');
	$options = array();
	$options['widget_width'] = preg_replace("/[^0-9]/","",sanitize_text_field($_REQUEST['size']));
	$cpars=array("color1","color2","color3","color4","color5","color6","color7","color8","color9");
	foreach ($cpars as $cp) {
		$options["widget_".$cp] = preg_replace("/[^0-9a-f]/","",sanitize_text_field(strtolower($_REQUEST[$cp])));
	}
	$options['widget_sc'] = isset($_REQUEST["sc"])?true:false;
	$options['widget_rb'] = isset($_REQUEST["rb"])?true:false;
	$options['widget_location'] = sanitize_text_field($_REQUEST['location']);
	if (@$_REQUEST["widget_active"]=="1") {
		$options['widget_active'] = true;

	} else {
		$options['widget_active'] = false;
	}
	update_option($this->get_plugin_info('slug')."-widget", $options);
	?>
<div class='updated'>
	<p>
		<strong><?php _e('Updated widget settings', $this->get_plugin_info('locale')); ?> </strong>
	</p>
</div>
	<?php
}

// GET OPTIONS
$options=get_option($this->get_plugin_info('slug')."-widget");

?>
<h2><?php _e("WP Karmacracy Widget Settings", $this->get_plugin_info('locale')); ?></h2>
                <form method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>">
                    <?php wp_nonce_field('wp_karmacracy_save_widget_settings') ?>
                    <table class="form-table">
                        <tbody>
                            <tr valign='top'>
                                <th scope="row" colspan="2">
                                	<input type="checkbox" id="widget_active" name="widget_active" onclick="checkWidget(jQuery(this));" value="1" <?=(($options["widget_active"])?("checked='checked'"):"")?>>
                                	<label for='widget_active'> <?php _e('Show karmacracy widget in the blog', $this->get_plugin_info('locale')); ?></label>
                                </th>
                            </tr>
                        </tbody>
                    </table>

<?php
$WIDGET_CODE_TEMPLATE="<div class=\"kcy_karmacracy_widget_h_ID\"></div><script defer=\"defer\" src=\"#KCYJSURL#\"></script>";
$WVERSION=self::WIDGET_VERSION;
?>
<script type="text/javascript" src="<?=plugins_url('js/jquery.colorpicker.js', __FILE__)?>"></script>
<link href="<?=plugins_url('css/colorpicker.css', __FILE__)?>" media="screen, projection" rel="stylesheet" type="text/css" />
<link href="<?=plugins_url('css/karmacracy-widget.css', __FILE__)?>" media="screen, projection" rel="stylesheet" type="text/css" />
<script type="text/javascript">
//<![CDATA[
function checkWidget(t) {
	if (t.is(":checked")) {
		jQuery('#wchooser').slideDown();
	} else {
		jQuery('#wchooser').slideUp();
	}
}
function recalc() {
jQuery('#atw').html('<div class="kcy_karmacracy_widget_h_ID"></div>');
var kcyJsUrl="http://rodney.karmacracy.com/widget-<?=$WVERSION?>/?id=ID";
kcyJsUrl+="&type="+(jQuery('typeV').attr('checked')?"v":"h");
kcyJsUrl+="&width="+jQuery('#size').val();
kcyJsUrl+="&sc="+(jQuery('#sc').attr('checked')?"1":"0");
kcyJsUrl+="&rb="+(jQuery('#rb').attr('checked')?"1":"0");
kcyJsUrl+="&c1="+jQuery('#color1').val();
kcyJsUrl+="&c2="+jQuery('#color2').val();
kcyJsUrl+="&c3="+jQuery('#color3').val();
kcyJsUrl+="&c4="+jQuery('#color4').val();
kcyJsUrl+="&c5="+jQuery('#color5').val();
kcyJsUrl+="&c6="+jQuery('#color6').val();
kcyJsUrl+="&c7="+jQuery('#color7').val();
kcyJsUrl+="&c8="+jQuery('#color8').val();
jQuery('#codehtml').val(new String('<?=str_replace("<","'+'<'+'",$WIDGET_CODE_TEMPLATE)?>').replace(/\#KCYJSURL\#/g,kcyJsUrl));

kcyJsUrl+="&url=http://karmacracy.com";

var s=document.createElement('script');
s.src=kcyJsUrl;
document.getElementsByTagName('head')[0].appendChild(s);


}

jQuery(document).ready(function() {
	 jQuery('#color1,#color2,#color3,#color4,#color5,#color6,#color7,#color8').ColorPicker( {
		 onSubmit: function(cp,hex,rgb,el) { jQuery(el).val(hex); jQuery('#show-'+(jQuery(el).attr('id').replace('color',''))).css('backgroundColor','#'+hex); jQuery(el).ColorPickerHide(); recalc(); },
		 onChange: function(cp,hex,rgb) { jQuery(jQuery(this).data('colorpicker').el).val(hex);  jQuery('#show-'+jQuery(this).data('colorpicker').el.id.replace('color','')).css('backgroundColor','#'+hex); }
	 });
	 jQuery('#colorselectors b').click(function () {
		 	var md=jQuery(this).attr('id').replace('show-','');

		 	jQuery('#color'+md).click();
	 });
	 recalc();
});

//]]>
</script>
<style type="text/css">.kcy-container li { margin-bottom:0px !important; }</style>

                   	<div id="wchooser" style="display:<?=(($options["widget_active"])?"block":"none")?>">
                   	<div class="explain" >
                   		<div class="cw wrapper" style="display:none">
							<h4><b>2. </b><?=_e("Type of widget")?></h4>
							<p><?=_e("Horizontal")?></p>
							<div class="cw-type">
							<ul>
							<li><input onclick="recalc()" type="radio" id="typeH" value="h" name="type" checked="checked"> <label for="typeH"><?=_e("resources.wid.conf.type.h")?></label></li>
							<li><input onclick="recalc()" type="radio" id="typeV" value="v" name="type"> <label for="typeV"><?=_e("resources.wid.conf.type.v")?></label></li>
							</ul>
						</div>
						</div>
						<div class="cw wrapper">
						<h4><b>1. </b><?=_e("Size")?></h4>
						<p><?=_e("Set a size for your widget, so it perfectly fits in your webpages")?></p>
						<div class="cw-size wrapper">
                            <ul>
                                <li><input class="rounded5" onblur="recalc()" type="text" id="size" value="<?=(isset($options["widget_width"])?$options["widget_width"]:"700")?>" name="size"> <label for="size"><?=_e("pixels")?></label></li>
                            </ul>
                        </div>
						</div>
						<div class="cw wrapper">
						<h4><b>2. </b><?=_e("Location")?></h4>
						<p><?=_e("Choose where you want to put the widget")?>:
						<select class="rounded5" name="location">
						<option <?=($options["widget_location"]=="body")?"selected='selected'":""?> value="body">After the body</option>
						<option <?=($options["widget_location"]=="beforebody")?"selected='selected'":""?> value="beforebody">Before the body</option>
						<!-- <option <?=($options["widget_location"]=="title")?"selected='selected'":""?> value="title">After the title</option> -->
						<option <?=($options["widget_location"]=="manual")?"selected='selected'":""?> value="manual">Manual - use wp_karmacracy_widget_html() function</option>
						</select>
						</p>
						</div>
						<div class="cw wrapper">
						<h4><b>3. </b><?=_e("Colour")?></h4>
						<p><?=_e("Make your widget colourful")?></p>
						<div id="colorselectors">
							<div class="tab-item active" id="cw-appearance">
	                            <div class="t-section t-tpl-50-50">
	                                <div class="t-first t-unit">
	                                    <input onchange="recalc()" type="text" id="color1" value="<?=(isset($options["widget_color1"])?$options["widget_color1"]:"74a3be")?>" name="color1" class="widget-colors text rounded5">
	                                    <b id="show-1" style="background-color: #<?=(isset($options["widget_color1"])?$options["widget_color1"]:"74a3be")?>"></b>
	                                    <span><?=_e("Upper tab")?></span>
	                                </div>
	                                <div class="t-first t-unit">
	                                    <input onchange="recalc()" type="text" id="color2" value="<?=(isset($options["widget_color2"])?$options["widget_color2"]:"6694ae")?>" name="color2" class="widget-colors text rounded5">
	                                    <b id="show-2" style="background-color: #<?=(isset($options["widget_color2"])?$options["widget_color2"]:"6694ae")?>"></b>
	                                    <span><?=_e("Sections tab")?></span>
	                                </div>
	                                <div class="t-first t-unit">
	                                    <input  onchange="recalc()" type="text" id="color3" value="<?=(isset($options["widget_color3"])?$options["widget_color3"]:"f2f2f2")?>" name="color3" class="widget-colors text rounded5">
	                                    <b id="show-3" style="background-color: #<?=(isset($options["widget_color3"])?$options["widget_color3"]:"f2f2f2")?>"></b>
	                                    <span><?=_e("Bottom tab")?></span>
	                                </div>
	                                <div class="t-first t-unit">
	                                    <input  onchange="recalc()" type="text" id="color4" value="<?=(isset($options["widget_color4"])?$options["widget_color4"]:"ffffff")?>" name="color4" class="widget-colors text rounded5">
	                                    <b id="show-4" style="background-color: #<?=(isset($options["widget_color4"])?$options["widget_color4"]:"ffffff")?>"></b>
	                                    <span><?=_e("Sections")?></span>
	                                </div>
	                         	 </div>
	                         	 <div class="t-section t-tpl-50-50">
	                                <div class="t-first t-unit">
	                                    <input onchange="recalc()" type="text" id="color5" value="<?=(isset($options["widget_color5"])?$options["widget_color5"]:"040404")?>" name="color5" class="widget-colors text rounded5">
	                                    <b id="show-5" style="background-color: <?=(isset($options["widget_color5"])?$options["widget_color5"]:"040404")?>"></b>
	                                    <span><?=_e("Button background")?></span>
	                                </div>
	                                <div class="t-first t-unit">
	                                    <input onchange="recalc()" type="text" id="color6" value="<?=(isset($options["widget_color6"])?$options["widget_color6"]:"ffffff")?>" name="color6"  class="widget-colors text rounded5">
	                                    <b id="show-6" style="background-color: #<?=(isset($options["widget_color6"])?$options["widget_color6"]:"ffffff")?>"></b>
	                                    <span><?=_e("Button text")?></span>
	                                </div>
	                                <div class="t-first t-unit">
	                                    <input vonchange="recalc()" type="text" id="color7" value="<?=(isset($options["widget_color7"])?$options["widget_color7"]:"6694ae")?>" name="color7"  class="widget-colors text rounded5">
	                                    <b id="show-7" style="background-color: #<?=(isset($options["widget_color7"])?$options["widget_color7"]:"6694ae")?> "></b>
	                                    <span><?=_e("Links")?></span>
	                                </div>
	                                <div class="t-first t-unit">
	                                    <input onchange="recalc()" type="text" id="color8" value="<?=(isset($options["widget_color8"])?$options["widget_color8"]:"040404")?>" name="color8"  class="widget-colors text rounded5">
	                                    <b id="show-8" style="background-color: #<?=(isset($options["widget_color8"])?$options["widget_color8"]:"040404")?>"></b>
	                                    <span><?=_e("Text")?></span>
	                                </div>
	                         	 </div>
                        	</div>
						</div>
						</div><div class="cw wrapper">
						<h4><b>4. </b><?=_e("Other options")?></h4>
                        <div class="cw-type wrapper">
                            <ul>
                                <li><input onclick="recalc()" type="checkbox" id="sc" value="1" name="sc" <?=(($options["widget_sc"])?("checked='checked'"):"")?>> <label for="sc"><?=_e("Show clicks")?></label></li>
                                <li><input onclick="recalc()" type="checkbox" id="rb" value="1" name="rb" <?=(($options["widget_rb"])?("checked='checked'"):"")?>> <label for="rb"><?=_e("Rounded borders")?></label></li>
                            </ul>
                        </div>



                    </div>
                    <div class="cw wrapper"><p>
&nbsp;
						</p><p>
&nbsp;
						</p></div>
					<div class="cw wrapper">
						<h4><?=_e("Preview")?></h4>
						<p><?=_e("Here you can see how your widget is going to be.")?></p>
						<div id="atw" style="height:160px">

						</div>
					</div>
</div></div>


  <div class="submit">
                        <input class='button-primary' type="submit" name="widgetupdate" value="<?php esc_attr_e('Update Widget Settings', $this->get_plugin_info('locale')) ?>" />
</div>
</form>



