{* $Header: /cvsroot/bitweaver/_bit_treasury/modules/Attic/mod_uplods.tpl,v 1.1 2006/12/04 21:05:25 squareing Exp $ *}
{strip}
{if $gBitSystem->isPackageActive( 'fisheye' ) && $modItems}
	{bitmodule title="$moduleTitle" name="fisheye_images"}
		<ul class="data">
			{foreach from=$modItems item=modItem}
				<li class="{cycle values='odd,even'} item">
					<a href="{$modItem.display_url}" title="{$modItem.title|escape} - {$modItem.last_modified|bit_short_datetime}, by {displayname user=$modItem.modifier_user real_name=$modItem.modifier_real_name nolink=1}{if (strlen($modItem.title) > $maxlen) AND ($maxlen > 0)}, {$modItem.title|escape}{/if}">
						{if $module_params.image}
							<img src="{$modItem.thumbnail_url.icon}" title="{$modItem.title|escape}" alt="{$modItem.title|escape}" />
							<br />
						{/if}

						{if $maxlen gt 0}
							{$modItem.title|escape|truncate:$maxlen:"...":true}
						{else}
							{$modItem.title|escape}
						{/if}
					</a>

					{if $module_params.description}
						<br />
						{if $maxlendesc gt 0}
							{$modItem.data|truncate:$maxlendesc:"...":true}
						{else}
							{$modItem.data}
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
