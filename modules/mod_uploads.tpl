{* $Header$ *}
{strip}
{if $gBitSystem->isPackageActive( 'treasury' ) && $modItems}
	{bitmodule title="$moduleTitle" name="treasury_items"}
		<ul>
			{foreach from=$modItems item=modItem}
				<li class="{cycle values='odd,even'} item">
					<a href="{$modItem->mInfo.display_url}" title="{$modItem->mInfo.title|escape} - {$modItem->mInfo.last_modified|bit_short_datetime}, by {displayname user=$modItem->mInfo.modifier_user real_name=$modItem->mInfo.modifier_real_name nolink=1}{if (strlen($modItem->mInfo.title) > $maxlen) AND ($maxlen > 0)}, {$modItem->mInfo.title|escape}{/if}">
						{if $module_params.image}
							<img src="{$modItem->mInfo.thumbnail_url.icon}" title="{$modItem->mInfo.title|escape}" alt="{$modItem->mInfo.title|escape}" />
							<br />
						{/if}

						{if $maxlen gt 0}
							{$modItem->mInfo.title|escape|truncate:$maxlen:"...":true}
						{else}
							{$modItem->mInfo.title|escape}
						{/if}
					</a>

					{if $module_params.description}
						<br />
						{if $maxlendesc gt 0}
							{$modItem->mInfo.data|escape|truncate:$maxlendesc:"...":true}
						{else}
							{$modItem->mInfo.data|escape}
						{/if}
					{/if}
				</li>
			{/foreach}
			<li class="more"><a href="{$smarty.const.TREASURY_PKG_URL}index.php">{tr}Show more{/tr} &hellip;</a></li>
		</ul>
	{/bitmodule}
{/if}
{/strip}
