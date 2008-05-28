{strip}
<div class="listing treasury">
	<div class="header">
		<h1>{tr}File Galleries{/tr}</h1>
	</div>

	<div class="body">
		{if $galleryList}
			<ul class="data">
				{foreach from=$galleryList item=gallery}
					<li class="item {cycle values='odd,even'}">
						<div class="floaticon">
							{if $gBitUser->hasPermission( 'p_treasury_edit_gallery' )}
								{smartlink ititle="Edit" ibiticon="icons/accessories-text-editor" ifile="edit_gallery.php" content_id=$gallery.content_id action=edit}
								{smartlink ititle="Insert Sub-gallery" ibiticon="icons/insert-object" ifile="edit_gallery.php" content_id=$gallery.content_id action=insert}
							{/if}
							{if $gBitUser->hasPermission( 'p_treasury_upload_item' )}
								{smartlink ititle="Upload Files" ibiticon="icons/go-up" ifile="upload.php" content_id=$gallery.content_id}
							{/if}
						</div>
						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_title' )}
							<h2><a href="{$gallery.display_url}">{$gallery.title|escape}</a></h2>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_thumb' ) and $gallery.thumbnail_url}
							<a href="{$gallery.display_url}">
								<img class="thumb" src="{$gallery.thumbnail_url}" alt="{$gallery.title|escape}" title="{$gallery.title|escape}" />
							</a>
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_desc' )}
							{$gallery.parsed_data}
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_structure' ) and count($gallery.subtree) gt 1}
							{include file="bitpackage:treasury/structure_inc.tpl" subtree=$gallery.subtree ifile="view.php"}
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_created' )}
							<br />{tr}Created{/tr}: {$gallery.created|bit_long_datetime}
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_creator' )}
							<br />{tr}by{/tr}: {displayname hash=$gallery}
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_item_count' )}
							<br />{tr}Number of files{/tr}: {$gallery.item_count}
						{/if}
						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_hits' ) and $gallery.hits}
							<br />{tr}Number of Times accessed{/tr}: {$gallery.hits}
						{/if}
						<div class="clear"></div>
					</li>
				{/foreach}
			</ul>
		{else}
			<p class="norecords">
				{tr}No galleries found{/tr}
				{if $gBitUser->hasPermission( 'p_treasury_create_gallery' )}
					<br />
					<a href="{$smarty.const.TREASURY_PKG_URL}edit_gallery.php">{tr}Create Gallery{/tr}</a>
				{/if}
			</p>
		{/if}
		{pagination}
	</div><!-- end .body -->
</div><!-- end .treasury -->
{/strip}
