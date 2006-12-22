{strip}
{if $gBitSystem->isFeatureActive( 'treasury_item_view_thumb' )}
	<div class="row" style="text-align:center;">
		<a href="{$gContent->mInfo.download_url}">
			{assign var=size value=$gBitSystem->getConfig('treasury_item_view_thumb')}
			<img src="{$gContent->mInfo.thumbnail_url.$size}{$refresh}" alt="{$gContent->getTitle()}" title="{$gContent->getTitle()}" />
			<br />{$gContent->getTitle()|escape}
		</a>
	</div>
{/if}

<div class="row">
	<object type="application/x-shockwave-flash" data="{$smarty.const.TREASURY_PKG_URL}templates/flash.container.swf?path={$gContent->mInfo.source_url}" width="100%" height="600">
		<param name="movie" value="{$smarty.const.TREASURY_PKG_URL}templates/flash.container.swf?path={$gContent->mInfo.source_url}" />
		<img src="{$smarty.const.TREASURY_PKG_URL}templates/noflash.container.gif" width="200" height="100" alt="" />
	</object>
</div>

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

{/strip}
