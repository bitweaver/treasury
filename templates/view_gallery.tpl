{strip}
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}

<div class="display treasury">
	{include file="bitpackage:treasury/gallery_nav_inc.tpl" galleryDisplayPath=$gContent->mInfo.gallery_display_path}

	<div class="header">
		<div class="floaticon">
			{if $gBitUser->hasPermission('p_treasury_upload_item')}
				{smartlink ititle="Upload Files" ibiticon="icons/applications-internet" ifile="upload.php" content_id=$gContent->mContentId}
			{/if}
			{if $gContent->isOwner() || $gBitUser->hasPermission('p_treasury_edit_gallery')}
				{smartlink ititle="Edit Gallery" ibiticon="icons/accessories-text-editor" ifile="edit_gallery.php" structure_id=$gContent->mStructureId action=edit}
			{/if}
			{if $gBitUser->hasPermission('p_treasury_create_gallery')}
				{smartlink ititle="Insert Gallery" ibiticon="icons/insert-object" ifile="edit_gallery.php" structure_id=$gContent->mStructureId action=insert}
			{/if}
			{if $gBitUser->isAdmin()}
				{if $gContent->mPerms}
					{smartlink ititle="Assign Permissions" ibiticon="icons/emblem-readonly" ipackage=liberty ifile="content_permissions.php" content_id=$gContent->mContentId}
				{else}
					{smartlink ititle="Assign Permissions" ibiticon="icons/emblem-shared" ipackage=liberty ifile="content_permissions.php" content_id=$gContent->mContentId}
				{/if}
			{/if}
			{if $gContent->isOwner() || $gBitUser->hasPermission('p_treasury_create_gallery')}
				{smartlink ititle="Remove Gallery" ibiticon="icons/edit-delete" ifile="edit_gallery.php" content_id=$gContent->mContentId action=remove_gallery}
			{/if}
		</div>

		<h1>{$gContent->getTitle()}</h1>
	</div>

	<div class="body">
		{if count($subtree) gt 1}
			{include file="bitpackage:treasury/structure_inc.tpl" ifile="view.php"}

			<hr />
		{/if}

		<p class="description">
			{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_thumb' ) and $gContent->mInfo.thumbnail_url}
				<a href="{$gContent->mInfo.display_url}">
					<img class="thumb" src="{$gContent->mInfo.thumbnail_url}{$refresh}" alt="{$gContent->mInfo.title|escape}" title="{$gContent->mInfo.title|escape}" />
				</a>
			{/if}
			<br />
			{$gContent->mInfo.data|escape|nl2br}
		</p>

		{if $gContent->mItems}
			<table class="data">
				<caption>{tr}List of files{/tr} <span class="total">[ {$listInfo.total_records|default:0} ]</span></caption>
				<tr>
					{if $gContent->getPreference('item_list_thumb_size')}
						<th style="width:10%"></th>
					{/if}
					<th style="width:50%">
						{smartlink ititle=Name isort=title list_page=$listInfo.current_page structure_id=$gContent->mStructureId}
					</th>
					{if $gBitSystem->isFeatureActive( 'treasury_item_list_date' ) || $gBitSystem->isFeatureActive( 'treasury_item_list_creator' )}
						<th style="width:10%">
							{smartlink ititle=Uploaded isort=created list_page=$listInfo.current_page structure_id=$gContent->mStructureId}
						</th>
					{/if}
					{if $gBitSystem->isFeatureActive( 'treasury_item_list_size' )}
						<th style="width:10%">{tr}Size{/tr}</th>
					{/if}
					{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' )}
						<th style="width:10%">
							{smartlink ititle=Downloads isort="lch.hits" list_page=$listInfo.current_page structure_id=$gContent->mStructureId}
						</th>
					{/if}
					<th style="width:20%">{tr}Actions{/tr}</th>
				</tr>

				{foreach from=$gContent->mItems item=item}
					<tr>
						{if $gContent->getPreference('item_list_thumb_size')}
							{assign var=thumbsize value=$gContent->getPreference('item_list_thumb_size')}
							<td style="text-align:center;">
								{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
									<a href="{$item.display_url}&amp;structure_id={$gContent->mStructureId}">
								{/if}
								<img src="{$item.thumbnail_url.$thumbsize}" alt="{$item.title}" title="{$item.title}" />
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_name' )}
									<br />{$item.filename}
								{/if}
								{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
									</a>
								{/if}
							</td>
						{/if}
						<td>
							<h3><a href="{$item.display_url}&amp;structure_id={$gContent->mStructureId}">{$item.title}</a></h3>
							{if $gBitSystem->isFeatureActive( 'treasury_item_list_desc' )}
								<p>{$item.data}</p>
							{/if}
						</td>
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_date' ) || $gBitSystem->isFeatureActive( 'treasury_item_list_creator' )}
							<td>
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_date' )}
									{$item.created|bit_short_date}<br />
								{/if}
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_creator' )}
									{tr}by{/tr}: {displayname hash=$item}
								{/if}
							</td>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_size' )}
							<td style="text-align:right;">
								{$item.file_size|kbsize}
							</td>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' )}
							<td style="text-align:right;">
								{$item.hits|default:"{tr}none{/tr}"}
							</td>
						{/if}
						<td class="actionicon">
							{if $gBitUser->hasPermission( 'p_treasury_download_item' )}
								<a href="{$smarty.const.TREASURY_PKG_URL}download.php?content_id={$item.content_id}">{biticon ipackage="icons" iname="emblem-downloads" iexplain="Download File"}</a>
							{/if}
							{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
								<a href="{$item.display_url}&amp;structure_id={$gContent->mStructureId}">{biticon ipackage="icons" iname="document-open" iexplain="View File"}</a>
							{/if}
							{if $gContent->isOwner( $item ) || $gBitUser->isAdmin()}
								<a href="{$smarty.const.TREASURY_PKG_URL}edit_item.php?content_id={$item.content_id}&amp;action=edit">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit File"}</a>
								<a href="{$smarty.const.TREASURY_PKG_URL}edit_item.php?content_id={$item.content_id}&amp;action=remove">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove File"}</a>
							{/if}
							{*if $gBitUser->isAdmin()}
								{smartlink ititle="Assign Permissions" ibiticon="icons/emblem-shared" ipackage=liberty ifile="content_permissions.php" content_id=$item.content_id}
							{/if*}
						</td>
					</tr>
				{/foreach}
			</table>
		{else}
			<p class="norecords">
				{tr}No Files Found{/tr}
				<br />
				<a href="{$smarty.const.TREASURY_PKG_URL}upload.php?content_id={$gContent->mContentId}">{tr}Upload Files{/tr}</a>
			</p>
		{/if}
		{pagination}
	</div><!-- end .body -->
</div><!-- end .treasury -->

{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
{/strip}
