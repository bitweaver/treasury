{* $Header: /cvsroot/bitweaver/_bit_treasury/modules/mod_uploads.tpl,v 1.3 2008/05/28 19:05:47 wjames5 Exp $ *}
{strip}
{if $gBitSystem->isPackageActive( 'fisheye' ) && $modItems}
	{bitmodule title="$moduleTitle" name="treasury_items"}
		<ul class="data">
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
			{foreachelse}
				<li></li>
			{/foreach}
		</ul>
	{/bitmodule}
{/if}
{/strip}
