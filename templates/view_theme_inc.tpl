{strip}
{if $gBitSystem->isFeatureActive( 'treasury_item_view_thumb' )}
	<div class="row" style="text-align:center;">
		<a href="{$smarty.const.TREASURY_PKG_URL}download.php?content_id={$gContent->mContentId}">
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
			<a href="{$smarty.const.TREASURY_PKG_URL}download.php?content_id={$gContent->mContentId}">{$gContent->mInfo.filename|escape}</a>
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
		{formlabel label="Uploaded" for=""}
		{forminput}
			{if $gBitSystem->isFeatureActive( 'treasury_item_view_size' )}
				{$gContent->mInfo.created|bit_long_datetime}<br />
			{/if}
			{if $gBitSystem->isFeatureActive( 'treasury_item_view_date' )}
				{tr}By{/tr}: {displayname hash=$gContent->mInfo}
			{/if}
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
{/if}

{if $gContent->mInfo.screenshots}
	<h2>{tr}Screenshots{/tr}</h2>
	<div style="text-align:center;">
		{foreach from=$gContent->mInfo.screenshots item=sshot}
			<img src="{$sshot}" title="{tr}Screenshot{/tr}" alt="{tr}Screenshot{/tr}" /> &nbsp;
		{/foreach}
	</div>
{/if}
{/strip}
