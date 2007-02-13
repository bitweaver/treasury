{strip}
{if $centerItemList}
	<div class="listing treasury">
		<div class="header">
			<h1>{$treasury_center_params.title|default:"{tr}Random Uploads{/tr}"}</h1>
		</div>

		<div class="body">
			{foreach from=$centerItemList item=item}
				<a href="{$item.display_url}">
					<img class="thumb" src="{$item.thumbnail_url.avatar}" alt="{$item.title|escape}" title="{$item.title|escape}" />
				</a>
			{foreachelse}
				{tr}No records found{/tr}
			{/foreach}
		</div><!-- end .body -->
	</div><!-- end .treasury -->
{/if}
{/strip}
