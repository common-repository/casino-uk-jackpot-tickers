jQuery(document).ready(function($) {

    tinymce.create('tinymce.plugins.jackpotticker', {
        init : function(ed, url) {
                // Register command for when button is clicked
                ed.addCommand('cb_insert_shortcode', function() {
                    var content = '[jackpotticker]';

                    tinymce.execCommand('mceInsertContent', false, content);
                });

            // Register buttons - trigger above command when clicked
            ed.addButton('jackpotticker', {title : 'Insert shortcode', cmd : 'cb_insert_shortcode', image: url + '/icon.png' });
        },
    });

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('jackpotticker', tinymce.plugins.jackpotticker);
});