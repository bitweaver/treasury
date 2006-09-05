{strip}
<ul>
	{if $gBitUser->hasPermission( 'p_treasury_edit_gallery' )}
		<li><a class="item" href="{$smarty.const.TREASURY_PKG_URL}edit_gallery.php">{biticon ipackage="icons" iname="document-new" iexplain="Create Gallery" iforce="icon"} {tr}Create Gallery{/tr}</a></li>
		{if $gContent->mStructureId}
			<li><a class="item" href="{$smarty.const.TREASURY_PKG_URL}edit_gallery_structure.php?structure_id={$gContent->mStructureId}">{biticon ipackage="icons" iname="document-new" iexplain="Organise Gallery Hierarchy" iforce="icon"} {tr}Change Structure{/tr}</a></li>
		{/if}
	{/if}

	{if $gBitUser->hasPermission( 'p_treasury_view_gallery' )}
		<li><a class="item" href="{$smarty.const.TREASURY_PKG_URL}index.php">{biticon ipackage="icons" iname="format-justify-fill" iexplain="List Galleries" iforce="icon"} {tr}List Galleries{/tr}</a></li>
	{/if}

	{if $gBitUser->hasPermission( 'p_treasury_upload_item' )}
		<li><a class="item" href="{$smarty.const.TREASURY_PKG_URL}upload.php">{biticon ipackage="icons" iname="applications-internet" iexplain="Upload Files" iforce="icon"} {tr}Upload Files{/tr}</a></li>
	{/if}
</ul>
{/strip}
