{strip}
<ul>
	{if $gBitUser->hasPermission( 'p_treasury_create_gallery' )}
		<li><a class="item" href="{$smarty.const.TREASURY_PKG_URL}edit_gallery.php">{booticon iname="icon-file" iexplain="Create Gallery" ilocation=menu}</a></li>
		{if $gContent->mStructureId}
			<li><a class="item" href="{$smarty.const.TREASURY_PKG_URL}edit_gallery_structure.php?structure_id={$gContent->mStructureId}">{biticon iname="view-refresh" iexplain="Gallery Hierarchy" ilocation=menu}</a></li>
		{/if}
	{/if}

	{if $gBitUser->hasPermission( 'p_treasury_view_gallery' )}
		<li><a class="item" href="{$smarty.const.TREASURY_PKG_URL}index.php">{booticon iname="icon-list" iexplain="List Galleries" ilocation=menu}</a></li>
	{/if}

	{if $gBitUser->hasPermission( 'p_treasury_upload_item' )}
		<li><a class="item" href="{$smarty.const.TREASURY_PKG_URL}upload.php">{booticon iname="icon-cloud-upload" iexplain="Upload Files" ilocation=menu}</a></li>
	{/if}
</ul>
{/strip}
