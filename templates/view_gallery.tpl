{strip}
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='nav' serviceHash=$gContent->mInfo}

<div class="display treasury">
	{include file="bitpackage:treasury/gallery_nav_inc.tpl" galleryDisplayPath=$gContent->mInfo.gallery_display_path}

	<div class="floaticon">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
		{if $gContent->hasUserPermission( 'p_treasury_upload_item' )}
			{smartlink ititle="Upload Files" ibiticon="icons/go-up" ifile="upload.php" content_id=$gContent->mContentId}
		{/if}
		{if $gContent->hasEditPermission() || $gContent->hasUserPermission( 'p_treasury_edit_gallery' )}
			{smartlink ititle="Edit Gallery" ibiticon="icons/accessories-text-editor" ifile="edit_gallery.php" structure_id=$gContent->mStructureId action=edit}
		{/if}
		{if $gContent->hasUserPermission( 'p_treasury_create_gallery' )}
			{smartlink ititle="Insert Gallery" ibiticon="icons/insert-object" ifile="edit_gallery.php" structure_id=$gContent->mStructureId action=insert}
		{/if}
		{if $gContent->hasAdminPermission()}
			{smartlink ititle="Remove Gallery" ibiticon="icons/edit-delete" ifile="edit_gallery.php" content_id=$gContent->mContentId action=remove_gallery}
		{/if}
	</div>

	<div class="header">
		<h1>{$gContent->getTitle()}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='body' serviceHash=$gContent->mInfo}
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

		{formfeedback hash=$feedback}

		{if $gContent->mItems}
			{form id=formid}
				<input type="hidden" name="structure_id" value="{$gContent->mStructureId}" />
				<table class="data">
					<caption>{tr}List of files{/tr} <span class="total">[ {$listInfo.total_records|default:0} ]</span></caption>
					<tr>
						{if $gContent->getPreference('item_list_thumb_size')}
							<th style="width:10%"></th>
						{/if}
						<th style="width:50%">
							{smartlink ititle=Name isort=title icontrol=$listInfo structure_id=$gContent->mStructureId}
						</th>
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_date' ) || $gBitSystem->isFeatureActive( 'treasury_item_list_creator' )}
							<th style="width:10%">
								{smartlink ititle=Uploaded isort=created iorder=desc idefault=1 icontrol=$listInfo structure_id=$gContent->mStructureId}
							</th>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_size' )}
							<th style="width:10%">{tr}Size{/tr} /<br />{tr}Duration{/tr}</th>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' )}
							<th style="width:10%">
								{smartlink ititle=Downloads isort="lch.hits" icontrol=$listInfo structure_id=$gContent->mStructureId}
							</th>
						{/if}
						<th style="width:20%">{tr}Actions{/tr}</th>
					</tr>

					{foreach from=$gContent->mItems item=item}
						<tr class="{cycle values="odd,even"}">
							{if $gContent->getPreference('item_list_thumb_size')}
								{assign var=thumbsize value=$gContent->getPreference('item_list_thumb_size')}
								<td style="text-align:center;">
									{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
										<a href="{$item.display_url}">
									{/if}
									<img src="{$item.thumbnail_url.$thumbsize}" alt="{$item.title|escape}" title="{$item.title|escape}" />
									{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
										</a>
									{/if}
								</td>
							{/if}
							<td>
								<h3><a href="{$item.display_url}">{$item.title|escape}</a></h3>
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_desc' ) && $item.data}
									<p>{$item.data|escape|nl2br}</p>
								{/if}
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_attid' )}
									<small>{$item.wiki_plugin_link}</small>
									{assign var=br value=1}
								{/if}
								{if $gBitSystem->isFeatureActive( 'treasury_item_list_name' )}
									{if $br}<br />{/if}
									{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
										<a href="{$item.display_url}">
									{/if}
									{$item.filename} <small>({$item.mime_type})</small>
									{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
										</a>
									{/if}
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
									{if $item.download_url}
										{$item.file_size|display_bytes}
									{/if}
									{if $item.prefs.duration}
										{if $item.download_url} / {/if}{$item.prefs.duration|display_duration}
									{/if}
								</td>
							{/if}
							{if $gBitSystem->isFeatureActive( 'treasury_item_list_hits' )}
								<td style="text-align:right;">
									{$item.hits|default:"{tr}none{/tr}"}
								</td>
							{/if}
							<td class="actionicon">
								{if $gBitUser->hasPermission( 'p_treasury_download_item' ) && $item.download_url}
									<a href="{$item.download_url}">{biticon ipackage="icons" iname="emblem-downloads" iexplain="Download File"}</a>
								{/if}
								{if $gBitUser->hasPermission( 'p_treasury_view_item' )}
									<a href="{$item.display_url}">{biticon ipackage="icons" iname="document-open" iexplain="View File"}</a>
								{/if}
								{if $gContent->isOwner( $item ) || $gBitUser->isAdmin()}
									<a href="{$smarty.const.TREASURY_PKG_URL}edit_item.php?content_id={$item.content_id}&amp;action=edit">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit File"}</a>
									<a href="{$smarty.const.TREASURY_PKG_URL}edit_item.php?content_id={$item.content_id}&amp;action=remove">{biticon ipackage="icons" iname="edit-delete" iexplain="Remove File"}</a>
									<input type="checkbox" name="del_content_ids[]" value="{$item.content_id}" />
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
							document.write("<input name=\"switcher\" id=\"switcher\" type=\"checkbox\" onclick=\"switchCheckboxes(this.form.id,'del_content_ids[]','switcher')\" />");
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
				{tr}No Files Found{/tr}
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
