{strip}
{if $gBitUser->hasPermission( 'p_treasury_edit_gallery' ) && $editicons}
	<div class="floaticon">
		{smartlink ititle="Upload Files" ibiticon="icons/go-up" ifile="upload.php" content_id=$subtree[ix].content_id}
		{smartlink ititle="Edit Gallery" ibiticon="icons/accessories-text-editor" ifile="edit_gallery.php" structure_id=$subtree[ix].structure_id action=edit}
		{smartlink ititle="Insert Gallery" ibiticon="icons/insert-object" ifile="edit_gallery.php" structure_id=$subtree[ix].structure_id action=insert}
		{smartlink ititle="Remove Gallery" ibiticon="icons/edit-delete" ifile="view.php" content_id=$subtree[ix].content_id action=remove}
	</div>
{/if}

{if $subtree[ix].content_id == $smarty.request.content_id || $subtree[ix].structure_id == $smarty.request.structure_id}
	{assign var=current value=1}
{else}
	{assign var=current value=0}
{/if}

{if $checkbox}
	<label><input type="checkbox" value="{$subtree[ix].content_id}" name="galleryContentIds[]"
		{foreach from=$galleryContentIds item=galid}
			{if $galid == $subtree[ix].content_id} checked="checked" {/if}
		{/foreach}
	/>&nbsp;&nbsp;
{/if}

{if $current}<strong>{/if}
	{if $ifile}
		<a href="{$smarty.const.TREASURY_PKG_URL}{$ifile}?structure_id={$subtree[ix].structure_id}">{$subtree[ix].title|escape}</a>
	{else}
		<a href="{$smarty.const.TREASURY_PKG_URL}edit_gallery.php?structure_id={$subtree[ix].structure_id}">{$subtree[ix].title|escape}</a>
	{/if}
{if $current}</strong>{/if}

{if $checkbox}
	</label>
{/if}

{biticon ipackage=liberty iname=spacer iforce=icon}
{/strip}
