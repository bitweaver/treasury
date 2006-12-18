{strip}
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}

<div class="display treasury">
	{include file="bitpackage:treasury/gallery_nav_inc.tpl"}

	<div class="header">
		<div class="floaticon">
			{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
			{if $gContent->isOwner() || $gBitUser->isAdmin()}
				{smartlink ifile="edit_item.php" ibiticon="icons/accessories-text-editor" ititle="Edit File" content_id=$gContent->mContentId action=edit}
				{smartlink ifile="edit_item.php" ibiticon="icons/edit-delete" ititle="Remove File" content_id=$gContent->mContentId action=remove}
			{/if}
		</div>

		<h1>{tr}Download{/tr}: {$gContent->getTitle()|escape}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}
		{assign var=guid value=$gContent->mInfo.plugin_guid}
		{include file=$gTreasurySystem->mPlugins.$guid.view_tpl}
	</div><!-- end .body -->
</div><!-- end .treasury -->

{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
{/strip}
