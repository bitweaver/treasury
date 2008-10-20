{strip}
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}

<div class="display treasury">
	{include file="bitpackage:treasury/gallery_nav_inc.tpl"}

	<div class="floaticon">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
		{if $gContent->hasUpdatePermission()}
			{smartlink ifile="edit_item.php" ibiticon="icons/accessories-text-editor" ititle="Edit File" content_id=$gContent->mContentId action=edit}
			{smartlink ifile="edit_item.php" ibiticon="icons/edit-delete" ititle="Remove File" content_id=$gContent->mContentId action=remove}
		{/if}
	</div>

	<div class="header">
		<h1>{$gContent->getTitle()|escape}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}
		<div class="description">
			{$gContent->mInfo.parsed_data}
		</div>
		{include file=$gLibertySystem->getMimeTemplate('view',$gContent->mInfo.attachment_plugin_guid) attachment=$gContent->mInfo}
	</div><!-- end .body -->

	{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}

	{if $item_display_comments}
		{include file="bitpackage:liberty/comments.tpl"}
	{/if}
</div><!-- end .treasury -->
{/strip}
