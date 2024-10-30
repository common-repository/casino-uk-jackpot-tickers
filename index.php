<?php
/*
 * Plugin Name:   Casino UK Jackpot Tickers
 * Version:       0.1
 * Plugin URI:    http://www.casinouk.com
 * Description:	  The Casino UK Jackpot Tickers plugin makes integrating progressive jackpots tickers into your casino affiliate site easy.
 * Author:        CasinoUK.com
 * Author URI:    http://www.casinouk.com
 */
require_once(dirname(__FILE__).'/casino_uk_jackpot_tickers.php');
register_activation_hook(__FILE__, array(&$CasinoUKJackpotTickers, 'install'));
// register_deactivation_hook(__FILE__, array(&$CasinoUKJackpotTickers, 'uninstall'));
?>
