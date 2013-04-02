{strip}
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}

<div class="display treasury">
	{include file="bitpackage:treasury/gallery_nav_inc.tpl" galleryDisplayPath=$gContent->mInfo.gallery_display_path}

	<div class="floaticon">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
		{if $gBitSystem->isPackageActive( 'rss' ) and $gBitSystem->isFeatureActive( 'treasury_rss' )}
			<a href="{$smarty.const.TREASURY_PKG_URL}treasury_rss.php?version={$gBitSystem->getConfig('rssfeed_default_version',0)}&amp;gallery_content_id={$gContent->mContentId}">{biticon ipackage="rss" iname="rss-16x16" iexplain="RSS feed"}</a>
		{/if}
		{if $gContent->hasUserPermission( 'p_treasury_upload_item' )}
			{smartlink ititle="Upload Files" booticon="icon-cloud-upload" ifile="upload.php" content_id=$gContent->mContentId}
		{/if}
		{if $gContent->hasUpdatePermission()}
			{smartlink ititle="Edit Gallery" booticon="icon-edit" ifile="edit_gallery.php" structure_id=$gContent->mStructureId action=edit}
		{/if}
		{if $gContent->hasUserPermission( 'p_treasury_create_gallery' )}
			{smartlink ititle="Insert Gallery" ibiticon="icons/insert-object" ifile="edit_gallery.php" structure_id=$gContent->mStructureId action=insert}
		{/if}
		{if $gContent->hasAdminPermission()}
			{smartlink ititle="Remove Gallery" booticon="icon-trash" ifile="edit_gallery.php" content_id=$gContent->mContentId action=remove_gallery}
		{/if}
	</div>

	<div class="header">
		<h1>{$gContent->getTitle()}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}
		<p class="description">
			{if $gBitSystem->isFeatureActive( 'treasury_gallery_view_thumb' ) and $gContent->mInfo.thumbnail_url}
				{assign var=galThumb value=$gBitSystem->getConfig('treasury_gallery_view_thumb')}
				<a href="{$gContent->mInfo.display_url}">
					<img class="thumb" src="{$gContent->mInfo.thumbnail_url.$galThumb}{$refresh}" alt="{$gContent->mInfo.title|escape}" title="{$gContent->mInfo.title|escape}" />
				</a>
			{/if}
			<br />
			{$gContent->mInfo.parsed_data}
		</p>

		{if count($subtree) gt 1}
			{include file="bitpackage:treasury/structure_inc.tpl"}

			<hr />
		{/if}

		{formfeedback hash=$feedback}

		{if $gContent->mItems}
			{form id=formid}
				<input type="hidden" name="structure_id" value="{$gContent->mStructureId}" />
				{assign var=thumbsize value=$gContent->getPreference('item_list_thumb_size',$gBitSystem->getConfig('treasury_item_list_thumb'))}
				<table class="data">
					<caption>{tr}List of files{/tr} <span class="total">[ {$listInfo.total_records|default:0} ]</span></caption>
					<tr>
						{if $thumbsize}
							<th style="width:1%"></th>
						{/if}
						<th style="width:60%">
							{smartlink ititle=Name isort=title icontrol=$listInfo structure_id=$gContent->mStructureId}
						</th>
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_date' ) || $gBitSystem->isFeatureActive( 'treasury_item_list_creator' )}
							<th style="width:10%">
								{smartlink ititle=Uploaded isort=created iorder=desc idefault=1 icontrol=$listInfo structure_id=$gContent->mStructureId}
							</th>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_size' )}
							<th style="width:10%">{tr}Size{/tr}</th>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' ) || $gBitSystem->isFeatureActive( 'treasury_item_list_downloads' )}
							<th style="width:10%">
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' )}
									{smartlink ititle=Hits isort="lch.hits" iorder=desc icontrol=$listInfo structure_id=$gContent->mStructureId}
								{/if}
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' ) && $gBitSystem->isFeatureActive( 'treasury_item_list_downloads' )}
									<br />
								{/if}
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_downloads' )}
									{smartlink ititle=Downloads isort="la.hits" iorder=desc icontrol=$listInfo structure_id=$gContent->mStructureId}
								{/if}
							</th>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' )}
						{/if}
						<th style="width:20%">{tr}Actions{/tr}</th>
					</tr>

					{foreach from=$gContent->mItems item=item}
						<tr class="{cycle values="odd,even"}">
							{if $thumbsize}
								<td style="text-align:center;">
									{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
										<a href="{$item->mInfo.display_url}">
									{/if}
									<img src="{$item->mInfo.thumbnail_url.$thumbsize}" alt="{$item->mInfo.title|escape}" title="{$item->mInfo.title|escape}" />
									{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
										</a>
									{/if}
								</td>
							{/if}
							<td>
								<h3><a href="{$item->mInfo.display_url}">{$item->mInfo.title|escape}</a></h3>
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_desc' ) && $item->mInfo.data}
									{$item->mInfo.parsed_data}
								{/if}
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_attid' )}
									<small>{$item->mInfo.wiki_plugin_link}</small>
									{assign var=br value=1}
								{/if}
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_name' )}
									{if $br}<br />{/if}
									{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
										<a href="{$item->mInfo.display_url}">
									{/if}
									{$item->mInfo.filename} <small>({$item->mInfo.mime_type})</small>
									{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
										</a>
									{/if}
								{/if}
							</td>
							{if $gBitSystem->isFeatureActive( 'treasury_item_list_date' ) || $gBitSystem->isFeatureActive( 'treasury_item_list_creator' )}
								<td>
									{if $gBitSystem->isFeatureActive( 'treasury_item_list_date' )}
										{$item->mInfo.created|bit_short_date}<br />
									{/if}
									{if $gBitSystem->isFeatureActive( 'treasury_item_list_creator' )}
										{tr}by{/tr}: {displayname hash=$item->mInfo}
									{/if}
								</td>
							{/if}
							{if $gBitSystem->isFeatureActive( 'treasury_item_list_size' )}
								<td style="text-align:right;">
									{if $item->mInfo.download_url}
										{$item->mInfo.file_size|display_bytes}
									{/if}
								</td>
							{/if}
							{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' ) || $gBitSystem->isFeatureActive( 'treasury_item_list_downloads' )}
								<td style="text-align:right;">
									{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' )}
										{$item->mInfo.hits|default:"{tr}none{/tr}"}
									{/if}
									{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' ) && $gBitSystem->isFeatureActive( 'treasury_item_list_downloads' )}
										<br />
									{/if}
									{if $gBitSystem->isFeatureActive( 'treasury_item_list_downloads' )}
										{$item->mInfo.downloads|default:"{tr}none{/tr}"}
									{/if}
								</td>
							{/if}
							<td class="actionicon">
								{if $gBitUser->hasPermission( 'p_treasury_download_item' ) && $item->mInfo.download_url}
									<a href="{$item->mInfo.download_url}">{biticon ipackage="icons" iname="emblem-downloads" iexplain="Download File"}</a>
								{/if}
								{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
									<a href="{$item->mInfo.display_url}">{booticon iname="icon-folder-open"  ipackage="icons"  iexplain="View File"}</a>
								{/if}
								{if $gContent->isOwner( $item->mInfo ) || $gBitUser->isAdmin()}
									<a href="{$smarty.const.TREASURY_PKG_URL}edit_item.php?content_id={$item->mInfo.content_id}&amp;action=edit">{booticon iname="icon-edit" ipackage="icons" iexplain="Edit File"}</a>
									<a href="{$smarty.const.TREASURY_PKG_URL}edit_item.php?content_id={$item->mInfo.content_id}&amp;action=remove">{booticon iname="icon-trash" ipackage="icons" iexplain="Remove File"}</a>
									<input type="checkbox" name="del_content_ids[]" value="{$item->mInfo.content_id}" />
									{assign var=checks value=true}
								{/if}
							</td>
						</tr>
					{/foreach}
				</table>

				{if $checks}
					<div style="text-align:right">
						<script type="text/javascript">/* <![CDATA[ check / uncheck all */
							document.write("<label for=\"switcher\">{tr}Select All{/tr}</label> ");
							document.write("<input name=\"switcher\" id=\"switcher\" type=\"checkbox\" onclick=\"BitBase.switchCheckboxes(this.form.id,'del_content_ids[]','switcher')\" />");
						/* ]]> */</script>
						<br />

						{tr}With selected items{/tr}:<br />
						<select name="action" onchange="this.form.submit();">
							<option value="dummy">{tr}No Action{/tr}</option>
							<option value="remove">{tr}Delete{/tr}</option>
						</select>

						<noscript>
							<div><input type="submit" name="submit" value="Process Selected Files" /></div>
						</noscript>
					</div>
				{/if}
			{/form}
		{else}
			<p class="norecords">
				{tr}No Items Found{/tr}
				{if $gContent->hasUserPermission( 'p_treasury_upload_item' )}
					<br />
					<a href="{$smarty.const.TREASURY_PKG_URL}upload.php?content_id={$gContent->mContentId}">{tr}Upload Files{/tr}</a>
				{/if}
			</p>
		{/if}
		{pagination}
	</div><!-- end .body -->
</div><!-- end .treasury -->

{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='view' serviceHash=$gContent->mInfo}
{/strip}
