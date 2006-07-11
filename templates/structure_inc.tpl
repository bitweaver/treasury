{strip}
<ul>
	<li>
		{section name=ix loop=$subtree}
			{if $subtree[ix].pos eq ''}
				{include file="bitpackage:treasury/structure_section_inc.tpl"}
			{else}
				{if $subtree[ix].first}<ul>{else}</li>{/if}
				{if $subtree[ix].last}</ul>{else}
					<li>
						{include file="bitpackage:treasury/structure_section_inc.tpl"}
				{/if}
			{/if}
		{/section}
	</li>
</ul><!-- end outermost .toc -->
{/strip}
