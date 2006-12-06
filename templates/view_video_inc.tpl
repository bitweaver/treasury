{strip}
{if $gBitSystem->isFeatureActive( 'treasury_item_view_thumb' )}
	<div class="row" style="text-align:center;">
		<a href="{$gContent->mInfo.download_url}">
			<img src="{$gContent->mInfo.thumbnail_url.small}{$refresh}" alt="{$gContent->getTitle()}" title="{$gContent->getTitle()}" />
			<br />{$gContent->getTitle()|escape}
		</a>
	</div>
{/if}

{if $gBitSystem->isFeatureActive( 'treasury_item_view_desc' )}
	{if $gContent->mInfo.data}
		<div class="row">
			{formlabel label="Description" for=""}
			{forminput}
				{$gContent->mInfo.data|escape|nl2br}
			{/forminput}
		</div>
	{/if}
{/if}

{if $gBitSystem->isFeatureActive( 'treasury_item_view_name' )}
	<div class="row">
		{formlabel label="File name" for=""}
		{forminput}
			<a href="{$gContent->mInfo.download_url}">{$gContent->mInfo.filename|escape}</a>
			&nbsp; <small>({$gContent->mInfo.mime_type})</small>
		{/forminput}
	</div>
{/if}

{if $gBitSystem->isFeatureActive( 'treasury_item_view_size' )}
	<div class="row">
		{formlabel label="File size" for=""}
		{forminput}
			{$gContent->mInfo.file_size|kbsize}
		{/forminput}
	</div>

	{*
	<div class="row">
		{formlabel label="Download Calculator" for=""}
		{forminput}
			{$gContent->mInfo.file_size|kbsize}
		{/forminput}
	</div>
	*}
{/if}

{if $gBitSystem->isFeatureActive( 'treasury_item_view_date' ) || $gBitSystem->isFeatureActive( 'treasury_item_view_creator' )}
	<div class="row">
		{formlabel label="First Uploaded" for=""}
		{forminput}
			{if $gBitSystem->isFeatureActive( 'treasury_item_view_date' )}
				{$gContent->mInfo.created|bit_long_datetime}<br />
			{/if}
			{if $gBitSystem->isFeatureActive( 'treasury_item_view_creator' )}
				{tr}By{/tr}: {displayname hash=$gContent->mInfo}
			{/if}
		{/forminput}
	</div>

	{if $gBitSystem->isFeatureActive( 'treasury_item_view_date' ) and $gContent->mInfo.created != $gContent->mInfo.last_modified}
		<div class="row">
			{formlabel label="Last Modified" for=""}
			{forminput}
				{$gContent->mInfo.last_modified|bit_long_datetime}<br />
			{/forminput}
		</div>
	{/if}
{/if}

{if $gBitSystem->isFeatureActive( 'treasury_item_view_hits' )}
	<div class="row">
		{formlabel label="Downloads" for=""}
		{forminput}
			{$gContent->mInfo.hits|default:"{tr}none{/tr}"}
		{/forminput}
	</div>
{/if}

<div class="row">
	{formlabel label="Wiki attachment" for=""}
	{forminput}
		{$gContent->mInfo.wiki_plugin_link}
		{formhelp note="To include this file in a wiki page, blog post, article &hellip;, use the above text."}
	{/forminput}
</div>

{if $gContent->mInfo.icons}
	<h2>{tr}Selected Icons{/tr}</h2>
	<ul style="list-style:none; margin:0; padding:0;">
		{foreach from=$gContent->mInfo.icons key=name item=icon}
			<li style="list-style:none; float:left; display:inline; margin:0 0 10px 10px; text-align:center">
				<img src="{$icon}" title="{$name}" alt="{$name}" />
			</li>
		{/foreach}
	</ul>
	<div class="clear"></div>
{/if}

{if $gContent->mInfo.frameshots}
	<h2>{tr}Frameshots{/tr}</h2>
	<div style="text-align:center;">
		{foreach from=$gContent->mInfo.frameshots item=fshot}
			<img src="{$fshot}" title="{tr}Frameshot{/tr}" alt="{tr}Frameshot{/tr}" /> &nbsp;
		{/foreach}
	</div>
{/if}

{/strip}
