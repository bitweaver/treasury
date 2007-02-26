{if $gContent->mInfo.flv_url}
	<div class="row" style="text-align:center;">
		{include file="bitpackage:treasury/flv_player_inc.tpl" flv=$gContent->mInfo flvPrefs=$gContent->mPrefs}
	</div>

	<div class="pagination">
		{tr}View other sizes{/tr}<br />
		&nbsp;&bull;&nbsp;
		<a href="{$gContent->mInfo.display_url}&size=small">{tr}Small{/tr}</a>&nbsp;&bull;&nbsp;
		<a href="{$gContent->mInfo.display_url}&size=medium">{tr}Medium{/tr}</a>&nbsp;&bull;&nbsp;
		<a href="{$gContent->mInfo.display_url}&size=large">{tr}Large{/tr}</a>&nbsp;&bull;&nbsp;
		<a href="{$gContent->mInfo.display_url}&size=huge">{tr}Huge{/tr}</a>&nbsp;&bull;&nbsp;
		<a href="{$gContent->mInfo.display_url}&size=original">{tr}Original{/tr}</a>&nbsp;&bull;&nbsp;
	</div>
{elseif $gContent->mInfo.status.processing}
	{if $gBitSystem->isFeatureActive( 'treasury_item_view_thumb' )}
		<div class="row" style="text-align:center;">
			<a href="{$gContent->mInfo.download_url}">
				{assign var=size value=$gBitSystem->getConfig('treasury_item_view_thumb')}
				<img src="{$gContent->mInfo.thumbnail_url.$size}{$refresh}" alt="{$gContent->getTitle()}" title="{$gContent->getTitle()}" />
				<br />{$gContent->getTitle()|escape}
			</a>
		</div>
	{/if}
	{formfeedback warning="{tr}The video is being processed. please try to reload in a couple of minutes.{/tr}"}
{elseif $gContent->mInfo.status.error}
	{if $gBitSystem->isFeatureActive( 'treasury_item_view_thumb' )}
		<div class="row" style="text-align:center;">
			<a href="{$gContent->mInfo.download_url}">
				{assign var=size value=$gBitSystem->getConfig('treasury_item_view_thumb')}
				<img src="{$gContent->mInfo.thumbnail_url.$size}{$refresh}" alt="{$gContent->getTitle()}" title="{$gContent->getTitle()}" />
				<br />{$gContent->getTitle()|escape}
			</a>
		</div>
	{/if}
	{if $gContent->isOwner() || $gBitUser->isAdmin()}
		{formfeedback error="{tr}The Video could not be processed. You can upload a different version of the film or simply leave as is.{/tr}"}
	{/if}
{/if}

{strip}
<div class="row">
	{formlabel label="Duration" for=""}
	{forminput}
		{$gContent->mPrefs.duration|display_duration}
	{/forminput}
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
		{formlabel label="Filename" for=""}
		{forminput}
			<a href="{$gContent->mInfo.download_url}">{$gContent->mInfo.filename|escape}</a>
			&nbsp; <small>({$gContent->mInfo.mime_type})</small>
		{/forminput}
	</div>
{/if}

{if $gBitSystem->isFeatureActive( 'treasury_item_view_size' )}
	<div class="row">
		{formlabel label="Filesize" for=""}
		{forminput}
			{$gContent->mInfo.file_size|display_bytes}
		{/forminput}
	</div>
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
