<div class="wrap">
<?php screen_icon('options-general');?>

<h2>Casino UK Jackpot Tickers</h2>
<?php
if(isset($_GET['success'])){?>
    <div id="message" class="updated">
        <p><strong>Done!</strong></p>
    </div>
<?php  }?>
<form method="post" action="<?php echo self_admin_url( 'admin.php' ); ?>" enctype="multipart/form-data">
<input type="hidden" name="page" value="<?php echo $_REQUEST['page'];?>" />
<input type="hidden" name="action" value="casino_uk_jackpot_tickers_settings_page" />
<?php wp_nonce_field( 'casino_uk_jackpot_tickers_settings_page', 'casino_uk_jackpot_tickers_settings_page' ); ?>
		<div id="casino_uk_jackpot_tickers-tabs">
		<ul class="hide-if-no-js">
			<li><a href="#settings">Settings</a></li>
			<li><a href="#jackpots">Jackpots</a></li>
			<li><a href="#custom-css">Custom CSS</a></li>
		</ul>

		<div class="casino_uk_jackpot_tickers-section" id="settings">
			<h3 class="hide-if-js">Settings</h3>
			<p><strong>Currency</strong> <?php $this->print_currency($currency);?></p>
			<p><strong>Display Jackpot Logo</strong> <input type="checkbox" name="showlogo" <?php checked($showlogo);?>/> </p>
			<p><strong>Font</strong> <?php $this->print_font($font);?></p>
			<p><strong>Font Color</strong> <?php $this->print_font_color($font_color);?></p>
			<p><strong>Font Size</strong> <?php $this->print_font_size($font_size);?></p>

			<p><strong>Affiliate Tag:</strong> <input type="text" name="affiliate_tag" value="<?php echo $affiliate_tag;?>" class="medium-text"/></p>

			<p><strong>Affiliate Link:</strong>  <input type="radio" name="affiliate_link" value="home" <?php checked($affiliate_link,'home');?>/> <a target="_blank" href="http://www.casinouk.com">www.casinouk.com</a> <input type="radio" name="affiliate_link" value="sub" <?php checked($affiliate_link,'sub');?>/> <a target="_blank" href="http://www.casinouk.com/progressive-jackpots.aspx">www.casinouk.com/progressive-jackpots.aspx</a></p>
			<p class="howto">When affiliate tag is not empty, what destination do you want to link to?</p>




		</div> <!-- section -->

		<div class="casino_uk_jackpot_tickers-section" id="jackpots">
			<h3 class="hide-if-js">Jackpots</h3>
			<h3>We provide a shortcode and a widget to display selected jackpots.</h3>
			<?php $this->print_jackpots($jackpots);?>


		</div> <!-- section -->

		<div class="casino_uk_jackpot_tickers-section" id="custom-css">
			<h3 class="hide-if-js">Custom CSS</h3>
			<p class="howto">This feature is designed for users who are familiar with CSS.</p>
			<textarea name="custom_css" cols="60" rows="20"><?php echo $this->get_custom_css();?></textarea>
			<p><strong>Disable Custom CSS</strong> <input type="checkbox" name="disable_custom_css" <?php checked($disable_custom_css);?>/></p>

			<p><input type="submit" name="reset_css" class="button-secondary" value="Reset to Default CSS" /></p>
		</div> <!-- section -->


		</div>  <!-- tabs -->

<p><input type="submit" class="button-primary" value="Save Changes" /></p>
<p></p>
<p><strong></strong></p>
<p class="howto"></p>
</form>

</div>
