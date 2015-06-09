<?php
namespace Tiga\Framework;

class Ajax{

	function hook()
	{
		add_action('wp_head',array($this,'printToken'));
		add_action('admin_head',array($this,'printToken'));

		add_action('wp_footer',array($this,'printAjaxHeader'));
		add_action('admin_footer',array($this,'printAjaxHeader'));
	}

	function printToken()
	{
		echo "<meta name='_tiga_token' content='".csrf_token()."'/>";

	}

	function printAjaxHeader()
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
		</script>
		<?php
	}

}