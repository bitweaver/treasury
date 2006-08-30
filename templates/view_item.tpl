{strip}
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}

<div class="display treasury">
	{include file="bitpackage:treasury/gallery_nav_inc.tpl"}

	<div class="header">
		<h1>{tr}Download File{/tr}</h1>
	</div>

	<div class="body">
		{if $gBitUser->hasPermission( 'p_treasury_edit_item' )}
			<div class="floaticon">
				{smartlink ifile="edit_item.php" ibiticon="liberty/edit" ititle="Edit File" content_id=$gContent->mContentId action=edit}
				{smartlink ifile="view_item.php" ibiticon="liberty/delete" ititle="Remove File" content_id=$gContent->mContentId action=remove}
			</div>
		{/if}

		<h2>{$gContent->getTitle()|escape}</h2>

		{assign var=guid value=$gContent->mInfo.plugin_guid}
		{include file=$gTreasurySystem->mPlugins.$guid.view_tpl}
	</div><!-- end .body -->
</div><!-- end .treasury -->

{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
{/strip}
