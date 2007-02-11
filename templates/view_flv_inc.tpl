{if $gContent->mInfo.flv_url}
	<div class="row" style="text-align:center;">
		<p id="treasury_player"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</p>
		<script type="text/javascript">
			var FO = {ldelim}	movie:"{$smarty.const.TREASURY_PKG_URL}libs/flv_player/flvplayer.swf",width:"320",height:"250",majorversion:"7",build:"0",bgcolor:"#FFFFFF",
			flashvars:"file={$gContent->mInfo.flv_url}&showdigits=false&autostart=false&image={$gContent->mInfo.thumbnail_url.medium}&showfsbutton=true&repeat=false" {rdelim};
			UFO.create(	FO, "treasury_player");
		</script>
	</div>
{elseif $gContent->mInfo.status.processing}
	{formfeedback warning="The video is being processed. please try to reload in a couple of minutes."}
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
		{formfeedback error="The Video could not be processed."}
	{/if}
{/if}

{strip}
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

	{*
	<div class="row">
		{formlabel label="Download Calculator" for=""}
		{forminput}
			{$gContent->mInfo.file_size|display_bytes}
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
