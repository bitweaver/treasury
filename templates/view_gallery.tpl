{strip}
<div class="display treasury">
	{include file="bitpackage:treasury/gallery_nav_inc.tpl" galleryDisplayPath=$gContent->mInfo.gallery_display_path}

	<div class="header">
		<div class="floaticon">
			{smartlink ititle="Upload Files" ibiticon="liberty/upload" ifile="upload.php" content_id=$gContent->mContentId}
			{smartlink ititle="Edit Gallery" ibiticon="liberty/edit" ifile="edit_gallery.php" structure_id=$gContent->mStructureId action=edit}
			{if $gBitUser->isAdmin()}
				{if $gContent->mPerms}
					{smartlink ititle="Assign Permissions" ibiticon="liberty/permissions_set" ipackage=liberty ifile="content_permissions.php" content_id=$subtree[ix].content_id}
				{else}
					{smartlink ititle="Assign Permissions" ibiticon="liberty/permissions" ipackage=liberty ifile="content_permissions.php" content_id=$subtree[ix].content_id}
				{/if}
			{/if}
			{smartlink ititle="Insert Gallery" ibiticon="liberty/insert" ifile="edit_gallery.php" structure_id=$gContent->mStructureId action=insert}
			{smartlink ititle="Remove Gallery" ibiticon="liberty/delete" ifile="view.php" content_id=$subtree[ix].content_id action=remove}
		</div>

		<h1>{$gContent->getTitle()}</h1>
		<h2>{$gContent->mInfo.data|escape}</h2>
	</div>

	<div class="body">
		{include file="bitpackage:treasury/structure_inc.tpl" ifile="view.php" noicons=true}

		<hr />

		{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_thumb' ) and $gContent->mInfo.thumbnail_url}
			<a href="{$gContent->mInfo.display_url}">
				<img class="thumb" src="{$gContent->mInfo.thumbnail_url}" alt="{$gContent->mInfo.title|escape}" title="{$gContent->mInfo.title|escape}" />
			</a>
		{/if}

		{if $gContent->mItems}
			<table class="data">
				<caption>{tr}List of files{/tr} <span class="total">[ {$listInfo.total_records|default:0} ]</span></caption>
				<tr>
					{if $gContent->getPreference('gallery_thumb_size')}
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
						<th style="width:20%">{tr}Size{/tr}</th>
					{/if}
					<th style="width:20%">{tr}Actions{/tr}</th>
				</tr>

				{foreach from=$gContent->mItems item=item}
					<tr>
						{if $gContent->getPreference('gallery_thumb_size')}
							{assign var=thumbsize value=$gContent->getPreference('gallery_thumb_size')}
							<td style="text-align:center;">
								<a href="{$item.display_url}&amp;structure_id={$gContent->mStructureId}">
									<img src="{$item.thumbnail_url.$thumbsize}" alt="{$item.title}" title="{$item.title}" />
									{if $gBitSystem->isFeatureActive( 'treasury_item_list_name' )}
										<br />{$item.filename}
									{/if}
								</a>
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
						<td class="actionicon">
							{if $gBitUser->hasPermission( 'p_treasury_download_item' )}
								<a href="{$smarty.const.TREASURY_PKG_URL}download.php?content_id={$item.content_id}">{biticon ipackage=liberty iname=download iexplain="Download File"}</a>
							{/if}
							{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
								<a href="{$item.display_url}&amp;structure_id={$gContent->mStructureId}">{biticon ipackage=liberty iname=view iexplain="View File"}</a>
							{/if}
							{*if $gBitUser->isAdmin()}
								{smartlink ititle="Assign Permissions" ibiticon="liberty/permissions" ipackage=liberty ifile="content_permissions.php" content_id=$item.content_id}
							{/if*}
							{if $gBitUser->hasPermission( 'p_treasury_edit_item' )}
								<a href="{$smarty.const.TREASURY_PKG_URL}edit_item.php?content_id={$item.content_id}&amp;action=edit">{biticon ipackage=liberty iname=edit iexplain="Edit File"}</a>
								<a href="{$smarty.const.TREASURY_PKG_URL}view_item.php?content_id={$item.content_id}&amp;action=remove">{biticon ipackage=liberty iname=delete iexplain="Remove File"}</a>
							{/if}
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
{/strip}
