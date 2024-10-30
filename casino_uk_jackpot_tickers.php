<?php
class CasinoUKJackpotTickers_Widget extends WP_Widget {
	function CasinoUKJackpotTickers_Widget() {
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Display jackpot tickers in widget'));
		$control_ops = array('width' => 500);
		parent::__construct('CasinoUKJackpotTickers', 'Casino UK Jackpot Tickers', $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		if ( $title )
		    echo $before_title . $title . $after_title;

		global $CasinoUKJackpotTickers;
		echo $CasinoUKJackpotTickers->get_jackpot_tickers($instance['jackpots']);
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		$instance['title'] = wp_strip_all_tags($new_instance['title']);
		$instance['jackpots'] = $new_instance['jackpots'];

		return $instance;
    }

	function form($instance) {

		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'jackpots' => array() ) );
		$title = wp_strip_all_tags($instance['title']);
		$jackpots = $instance['jackpots'];


	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
	<?php

		global $CasinoUKJackpotTickers;
		$CasinoUKJackpotTickers->print_jackpots($jackpots,4,$this->get_field_name('jackpots').'[]');
	}

}
add_action('widgets_init', create_function('', 'return register_widget("CasinoUKJackpotTickers_Widget");'));
if (!class_exists("CasinoUKJackpotTickers")) {
	class CasinoUKJackpotTickers{
		private $db =  array();
		private $version = '0.1';
		private $setting_page = '';
		private $options = '';
		private $option_name = 'CasinoUKJackpotTickers';
		private $error ='';

		function __construct(){
			global $wpdb;
			$this->db = array();
			$this->plugin_url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));
			add_action('admin_menu',array($this,'add_menu_pages'));
			add_action( 'admin_init', array( $this, 'install' ) );
			add_action('wp_enqueue_scripts',array($this,'load_assets'),99);
			add_action('admin_head', array($this,'add_tinymce'));
			add_shortcode( 'jackpotticker', array($this,'get_jackpot_tickers'));
		}

		function get_jackpot_tickers($widget_jackpots=array()){
			$o = $this->get_option();

			extract($o);
			if($widget_jackpots)
				$jackpots = $widget_jackpots;
			if(!$jackpots)
				return 'Please select one jackpot ticker at least.';


			$html = '<div class="jackpots">';
			$showlogo = $showlogo?'true':'false';
			$aff_link = '';
			if($affiliate_tag){
				if($affiliate_link == 'sub')
					$affiliate_link = 'http://www.casinouk.com/progressive-jackpots.aspx';
				else
					$affiliate_link = 'http://www.casinouk.com/';
				$aff_link = add_query_arg(array('btag'=>$affiliate_tag),$affiliate_link);
			}

			foreach($jackpots as $jackpot){
				if($jackpot == 'total')
					$jackpot = '';
				$html .= '<div class="jackpot">';
				if($aff_link)
					$html .='<a target="_blank" href="'.$aff_link.'">';
				$html .= "<script type='text/javascript' src='http://www.tickerassist.co.uk/ProgressiveTickers/include/js/ProgressiveTickersControl.js?progid={$jackpot}&font-color={$font_color}&font-family={$font}&currency={$currency}&showlogo={$showlogo}&font-size={$font_size}'></script>";
				if($aff_link)
					$html .='</a>';
				$html .= '</div>';
			}

			$html .= '</div>';
			return $html;
		}


		function add_tinymce(){
			global $typenow;
			if(!in_array($typenow,array('post','page')))
				return ;
			add_filter('mce_external_plugins', array($this,'add_tinymce_plugin'));
			add_filter('mce_buttons', array($this,'add_tinymce_button'));
		}

		function add_tinymce_plugin($plugin_array) {
			$plugin_array['jackpotticker'] = $this->asset_url('js/tinymce.js');

			return $plugin_array;
		}

		function add_tinymce_button($buttons) {
			array_push($buttons, 'jackpotticker');
			return $buttons;
		}


		function load_assets(){
			if(!$this->get_option('disable_custom_css')){
				wp_register_style( 'ProgressiveTickers-css', $this->asset_url('css/ProgressiveTickers.css')  );
				wp_enqueue_style( 'ProgressiveTickers-css' );
			}


  			wp_register_script( 'ProgressiveTickers-js', 'http://www.tickerassist.co.uk/ProgressiveTickers/include/js/ProgressiveTickersMandatory.js' );
  			wp_enqueue_script('ProgressiveTickers-js');
		}

		function plugin_url($path=''){
			$plugin_url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));
			return $plugin_url.'/'.trim($path,'/');
		}

		function lib_path($path=''){
			return $this->plugin_path('lib/'.$path);
		}

		function plugin_path($path=''){
			$plugin_path = dirname(__FILE__);
			return $plugin_path.'/'.trim($path,'/');
		}

		function asset_url($path=''){
			return $this->plugin_url('assets/'.$path);
		}

		function asset_path($path=''){
			return $this->plugin_path('assets/'.$path);
		}


		function add_menu_pages(){

			add_menu_page('Casino UK Jackpot Tickers', 'Casino UK Jackpot Tickers', 'manage_options','casino_uk_jackpot_tickers_settings_page',  array($this,'settings_page'));

			$menu=add_submenu_page('casino_uk_jackpot_tickers_settings_page', 'Settings Page', 'Settings Page', 'manage_options','casino_uk_jackpot_tickers_settings_page',  array($this,'settings_page'));

			add_action( "admin_action_casino_uk_jackpot_tickers_settings_page", array($this, 'settings_page') );

			add_action( "admin_print_scripts-$menu", array($this, 'js_scripts') );

			add_action( "admin_print_styles-$menu", array($this, 'css_styles') );

		}


		function js_scripts(){

			wp_enqueue_script('casino_uk_jackpot_tickers_js', $this->asset_url('js/casino_uk_jackpot_tickers.js'),array('jquery','jquery-ui-dialog','jquery-ui-tabs'),false,true);
		}

		function css_styles(){
			wp_enqueue_style('casino_uk_jackpot_tickers_style', $this->asset_url('css/casino_uk_jackpot_tickers.css'));
		}

		function get_css_file($name){
			$filename = $this->asset_path('css/'.$name.'.css');
			if(file_exists($filename))
				return file_get_contents($filename);
			return false;
		}

		function get_default_custom_css(){
			$contents = $this->get_css_file('ProgressiveTickers.bak');
			if(!$contents)
				wp_die("We can't find default css file: ProgressiveTickers.bak.css. Did you delete it?");
			return $contents;
		}

		function get_custom_css(){
			return $this->get_css_file('ProgressiveTickers');
		}

		function write_css_file($contents=''){
			if(!$contents)
				return false;
			$filename = $this->asset_path('css/ProgressiveTickers.css');
			return file_put_contents($filename,$contents);

		}

		function install(){

			$o = $this->get_option();

			if(!$o['version'] || $o['version'] != $this->version){
				$o = array(
							'version'=>$this->version,
							'currency'=>$o['currency']?$o['currency']:'EUR',
							'showlogo'=>isset($o['showlogo'])?$o['showlogo']:true,
							'font'=>$o['font']?$o['font']:'arial',
							'font_color'=>$o['font_color']?$o['font_color']:'black',
							'font_size'=>$o['font_size']?$o['font_size']:'13',
							'affiliate_tag'=>$o['affiliate_tag']?$o['affiliate_tag']:'',
							'jackpots'=>$o['jackpots']?$o['jackpots']:array(),
							'disable_custom_css'=>isset($o['disable_custom_css'])?$o['disable_custom_css']:false,
							'affiliate_link'=>$o['affiliate_link']?$o['affiliate_link']:'home'
						);
				$this->update_option($o);

			}


		}

		function uninstall(){
			global $wpdb;
			foreach($this->db as $table)
				$wpdb->query(  "DROP TABLE {$table}" );
			delete_option($this->option_name);
		}


		function get_option( $name='',$default='' ) {

			if ( empty( $this->options ) ) {

				$options = get_option( $this->option_name );

			}else {

				$options = $this->options;
			}
			if ( !$options ) return false;
			if ( $name ){
				if($value = $options[$name])
					return $value;
				return $default;
			}

			return $options;
		}

		function update_option($ops){

			if(is_array($ops)){

				$options = $this->get_option();

				foreach($ops as $key => $value){

					$options[$key] = $value;

				}
				update_option($this->option_name,$options);
				$this->options = $options;
			}

		}

		function print_font($selected=''){
			$fonts = array("arial","georgia","tahoma","times New Roman","trebuchet MS","verdana");
			echo '<select name="font">';
			echo '<option value="0">Please Select One</option>';
			foreach($fonts as $c){
				echo '<option value="'.$c.'"';
				if($c == $selected)
					echo ' selected=selected';
				echo '>'.ucfirst($c).'</option>';
			}
			echo '</select>';
		}

		function print_font_size($selected=''){
			$sizes = range(8,16);
			echo '<select name="font_size">';
			echo '<option value="0">Please Select One</option>';
			foreach($sizes as $c){
				echo '<option value="'.$c.'"';
				if($c == $selected)
					echo ' selected=selected';
				echo '>'.$c.'</option>';
			}
			echo '</select>';
		}
		function print_font_color($selected=''){
			$colors = array("black","blue","red","silver","white","yellow");
			echo '<select name="font_color">';
			echo '<option value="0">Please Select One</option>';
			foreach($colors as $c){
				echo '<option value="'.$c.'"';
				if($c == $selected)
					echo ' selected=selected';
				echo '>'.ucfirst($c).'</option>';
			}
			echo '</select>';
		}

		function print_currency($selected=''){
			$curs = array(	"ALL",	"USD",	"AFN",	"ARS",	"AWG",	"AUD",	"AZN",	"BSD",	"BBD",	"BYR",	"EUR",	"BZD",	"BMD",	"BOB",	"BAM",	"BWP",	"BGN",	"BRL",	"GBP",	"BND",	"KHR",	"CAD",	"KYD",	"CLP",	"CNY",	"COP",	"CRC",	"HRK",	"CUP",	"CZK",	"DKK",	"DOP",	"XCD",	"EGP",	"SVC",	"EEK",	"FKP",	"FJD",	"GHC",	"GIP",	"GTQ",	"GGP",	"GYD",	"HNL",	"HKD",	"HUF",	"ISK",	"INR",	"IDR",	"IRR",	"IMP",	"ILS",	"JMD",	"JPY",	"JEP",	"KZT",	"KPW",	"KRW",	"KGS",	"LAK",	"LVL",	"LBP",	"LRD",	"LTL",	"MKD",	"MYR",	"MUR",	"MXN",	"MNT",	"MZN",	"NAD",	"NPR",	"ANG",	"NZD",	"NIO",	"NGN",	"NOK",	"OMR",	"PKR",	"PAB",	"PYG",	"PEN",	"PHP",	"PLN",	"QAR",	"RON",	"RUB",	"SHP",	"SAR",	"RSD",	"SCR",	"SGD",	"SBD",	"SOS",	"ZAR",	"LKR",	"SEK",	"CHF",	"SRD",	"SYP",	"TWD",	"THB",	"TTD",	"TRY",	"TRL",	"TVD",	"UAH",	"UYU",	"UZS",	"VEF",	"VND",	"YER",	"ZWD");
			echo '<select name="currency">';
			echo '<option value="0">Please Select One</option>';
			foreach($curs as $cur){
				echo '<option value="'.$cur.'"';
				if($cur == $selected)
					echo ' selected=selected';
				echo '>'.$cur.'</option>';
			}
			echo '</select>';
		}

		function print_jackpots($selected=array(),$split=9,$attr_name='jackpots[]'){



			$jackpots = array(
				'total' => 'Jackpots Total',
				'1'=>'Cash Splash',
				'1-5reel'=>'Cash Splash 5 Reel',
				'2'=>'Lots a Loot',
				'2-5reel'=>'Lots a Loot 5 Reel',
				'3'=>'Wow Pot',
				'3-5reel'=>'Wow pot 5 Reel',
				'4'=>'Super jax',
				'5'=>'Fruit Fiesta',
				'5-5reel'=>'Fruit Fiesta 5 Reel',
				'6'=>'Treasure Nile',
				'7'=>'Cyberstud',
				'8'=>'Jackpot Deuces',
				'9'=>'Triple Sevens',
				'10'=>'Major Millions',
				'10-5reel'=>'Major Millions 5 Reel',
				'10-mspin'=>'Major Millions Mega Spin',
				'11'=>'Roulette Royale',
				'12'=>'King Cashalot',
				'13'=>'Tunzamunni',
				'14'=>'Poker Ride lack',
				'15'=>'Mega Moolah Mega',
				'15-5reel'=>'MM 5 Reel Mega',
				'15-summer'=>'MM Summertime Mega',
				'15-isis'=>'MM Isis Mega lack',
				'16'=>'Mega Moolah Major',
				'16-5reel'=>'MM 5 Reel Major',
				'16-summer'=>'MM Summertime Major',
				'16-isis'=>'MM Isis Major',
				'17'=>'Mega Moolah Minor',
				'17-5reel'=>'MM 5 Reel Minor',
				'17-summer'=>'MM Summertime Minor',
				'17-isis'=>'MM Isis Minor',
				'18'=>'Mega Moolah Mini',
				'18-5reel'=>'MM 5 Reel Mini',
				'18-summer'=>'MM Summertime Mini',
				'18-isis'=>'MM Isis Mini',
				'19'=>'Caribbean Draw',
				'18-tdk'=>'The Dark Knight (Mini)',
				'17-tdk'=>'The Dark Knight (Minor)',
				'16-tdk'=>'The Dark Knight (Major)',
				'15-tdk'=>'The Dark Knight (Mega)',
			);
			$i = 1;


			foreach($jackpots as $id => $name){
				echo  '<div style="text-align:center;float:left;width:100px;padding:5px"><img width="80" height="64" src="' .$this->asset_url('jackpot/'.$id.'.gif') . '" /><br />';
				echo '<strong>'.$name.'</strong><br/> <input type="checkbox" name="'.$attr_name.'" value="' . $id . '"';
				if($selected && in_array((string)$id,$selected)){

					echo ' checked="checked"';
				}

				echo '/></div>';
				if($i % $split == 0){
					echo '<div style="clear:both"></div>';
				}

				$i++;
			}
			echo '<div style="clear:both"></div>';
		}

		function error_log($msg){
			$this->error .= "<p style='color:red;'>{$msg}</p>";
		}

		function print_error(){
			echo $this->error;
		}

		function clean_post(){
			unset($_POST[$_POST['page']]);
			unset($_POST['page']);
			unset($_POST['action']);
			unset($_POST['_wp_http_referer']);


			foreach($_POST as &$v){
				$v = str_replace('\\', '', $v);
				if(ctype_alnum($v))
					$v = wp_strip_all_tags($v);
			}
			return $_POST;

		}

		function settings_page(){
			$o = $this->get_option();
			if(wp_verify_nonce( $_POST['casino_uk_jackpot_tickers_settings_page'], 'casino_uk_jackpot_tickers_settings_page' )){
				if($_POST['reset_css']){
					$css = $this->get_default_custom_css();

					$this->write_css_file($css);
				}else{
					$_POST = $this->clean_post();
					foreach($_POST as $k => $v)
						$o[$k] = $v;
					
					     
					$o['jackpots'] = $_POST['jackpots']?$o['jackpots']:array();
					$o['showlogo'] = $_POST['showlogo']?true:false;
					$o['disable_custom_css'] = $_POST['disable_custom_css']?true:false;
					$this->write_css_file($_POST['custom_css']);
				}

				$this->update_option($o);
				$this->redirect_to_current_page();
			}
			extract($o);
			@include($this->plugin_path('casino_uk_jackpot_tickers_settings_page.php'));
		}



		function redirect_to_current_page(){

			$this->redirect_to_page(self_admin_url('admin.php?page='.$_REQUEST['page'].'&success'));
		}

		function redirect_to_page($redir){
			wp_redirect($redir);
			exit;
		}

	}


}

if(!isset($CasinoUKJackpotTickers)){
		$CasinoUKJackpotTickers = new CasinoUKJackpotTickers();
}
?>
