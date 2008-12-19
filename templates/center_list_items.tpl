{strip}
{if $centerItemList}
	<div class="listing treasury">
		<div class="header">
			<h1>{$treasury_center_params.title|default:"{tr}Random Uploads{/tr}"}</h1>
		</div>

		<div class="body">
			{foreach from=$centerItemList item=item}
				<a href="{$item->mInfo.display_url}">
					<img class="thumb" src="{$item->mInfo.thumbnail_url.avatar}" alt="{$item->mInfo.title|escape}" title="{$item->mInfo.title|escape}" />
				</a>
			{foreachelse}
				{tr}No records found{/tr}
			{/foreach}
		</div><!-- end .body -->
	</div><!-- end .treasury -->
{/if}
{/strip}
