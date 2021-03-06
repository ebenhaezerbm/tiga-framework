<?php
namespace Tiga\Framework;

/**
 * Helper class to easily handle Ajax request.
 */
class Ajax
{
    /**
     * Hook required script to WordPress.
     */
    public function hook()
    {
        wp_enqueue_script('jquery');

        add_action('wp_head', array($this, 'printToken'));
        add_action('admin_head', array($this, 'printToken'));

        add_action('wp_footer', array($this, 'printAjaxHeader'));
        add_action('admin_footer', array($this, 'printAjaxHeader'));
    }

    /**
     * Print CSRF Token in wp_head.
     */
    public function printToken()
    {
        echo "<meta name='_tiga_token' content='".csrf_token()."'/>";
    }

    /**
     * Setup ajax request on Jquery with CSRF Token.
     */
    public function printAjaxHeader()
    {
        ?>
		<script type="text/javascript">
		jQuery(function() {
		    jQuery.ajaxSetup({
		        headers: {
		            'X-CSRF-Tiga-Token': jQuery('meta[name="_tiga_token"]').attr('content')
		        }
		    });
		});

		var tiga_ajax_url = '<?php echo tiga_url('') ?>';

		</script>
		<?php

    }
}
