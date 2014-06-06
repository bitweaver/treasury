{strip}
{if $gBitUser->hasPermission( 'p_liberty_attach_attachments' ) && $gBitSystem->isFeatureActive( 'mime_import_file_import_path' )}
	<div class="form-group">
		{formlabel label="Import file" for="ajax_input"}
		{forminput}
			<input type="input" id="ajax_input" name="mimeplugin[{$smarty.const.PLUGIN_MIME_GUID_IMPORT}][import_path]" size="40" />
			{formhelp note="You can click on the load files link below to display available files and it will also insert the correct path to the desired file you wish to import. e.g.: public/video.mpg"}
		{/forminput}
	</div>

	{include file="bitpackage:kernel/ajax_file_browser_inc.tpl" ajax_path_conf=mime_import_file_import_path}
{/if}
{/strip}
