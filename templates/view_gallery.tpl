{strip}
<div class="edit treasury">
	<div class="header">
		<h1>{tr}File Gallery{/tr}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:treasury/structure_inc.tpl" ifile="view.php"}

		<hr />

		<h2>{$gContent->getTitle()}</h2>

		{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_thumb' ) and $gContent->mInfo.thumbnail_url}
			<a href="{$gContent->mInfo.display_url}">
				<img class="thumb" src="{$gContent->mInfo.thumbnail_url}" alt="{$gContent->mInfo.title|escape}" title="{$gContent->mInfo.title|escape}" />
			</a>
		{/if}

		<p>{$gContent->mInfo.data|escape}</p>

		{if $gContent->mItems}
			<table class="data">
				<caption>{tr}List of files{/tr} <span class="total">[ {$listInfo.total_records|default:0} ]</span></caption>
				<tr>
					{if $gBitSystem->isFeatureActive( 'treasury_item_list_thumb' )}
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
						{if $gBitSystem->isFeatureActive( 'treasury_item_list_thumb' )}
							{assign var=thumbsize value=$gBitSystem->getConfig('treasury_item_list_thumb')}
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
							<a href="{$item.display_url}&amp;structure_id={$gContent->mStructureId}">{biticon ipackage=liberty iname=view iexplain="View File"}</a>
							{if $gBitUser->hasPermission( 'p_treasury_upload_item' )}
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
