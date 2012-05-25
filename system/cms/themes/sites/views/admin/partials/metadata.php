
<?php echo css('admin/style.css'); ?>
<?php echo css('jquery/jquery-ui.css'); ?>
<?php echo css('jquery/colorbox.css'); ?>
<?php echo js('jquery/jquery-ui.min.js'); ?>
<?php echo js('jquery/jquery.colorbox.min.js'); ?>
<?php echo js('jquery/jquery.livequery.min.js'); ?>
<?php echo js('jquery/jquery.uniform.min.js'); ?>
<?php echo js('admin/functions.js'); ?>

<script type="text/javascript">
	var APPPATH_URI			= "<?php echo APPPATH_URI;?>";
	var SITE_URL			= "<?php echo rtrim(site_url(), '/').'/';?>";
	var BASE_URL			= "<?php echo BASE_URL;?>";
	var BASE_URI			= "<?php echo BASE_URI;?>";
	var DEFAULT_TITLE		= "<?php echo lang('site.sites'); ?>";
	var DIALOG_MESSAGE		= "<?php echo lang('global:dialog:delete_message'); ?>";
	pyro.apppath_uri		= "<?php echo APPPATH_URI; ?>";
	pyro.base_uri			= "<?php echo BASE_URI; ?>";
	jQuery.noConflict();
</script>

<?php echo $template['metadata']; ?>
