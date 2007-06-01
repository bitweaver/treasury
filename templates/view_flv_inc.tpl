{strip}
{form ipackage=treasury ifile="plugins/form.flv.php"}
	<input type="hidden" name="content_id" value="{$gContent->mContentId}" />

	{if $gContent->mInfo.flv_url}
		<div class="row" style="text-align:center;">
			{include file="bitpackage:treasury/flv_player_inc.tpl" flv=$gContent->mInfo flvPrefs=$gContent->mPrefs}
		</div>

		{if $gBitSystem->isFeatureActive( 'treasury_item_view_desc' ) && $gContent->mInfo.data}
			<p class="description">
				{$gContent->mInfo.data|escape|nl2br}
			</p>
		{/if}

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

	{if $gContent->mPrefs.duration}
		<div class="row">
			{formlabel label="Duration" for=""}
			{forminput}
				{$gContent->mPrefs.duration|display_duration}
			{/forminput}
		</div>
	{/if}

	{if $gContent->mInfo.download_url}
		{if $gBitSystem->isFeatureActive( 'treasury_item_view_name' )}
			<div class="row">
				{formlabel label="Filename" for=""}
				{forminput}
					<a href="{$gContent->mInfo.download_url}">{$gContent->mInfo.filename|escape}</a>
					&nbsp; <small>({$gContent->mInfo.mime_type})</small>
					{if ($gContent->isOwner() || $gBitUser->isAdmin()) && $gContent->mInfo.flv_url}
						<br /><input type="submit" name="remove_original" value="{tr}Remove Original{/tr}" />
						{formhelp note="This will remove the original file from the server. The falsh video will remain and you can still view the video but you cannot download the original anymore."}
					{/if}
				{/forminput}
			</div>
		{/if}

		{if $gContent->isOwner() || $gBitUser->isAdmin()}
			<div class="row">
				{formlabel label="New Aspect Ratio" for="aspect"}
				{forminput}
					{* there doesn't seem to be a way to select the correct aspect - especially if it's not a common one *}
					<select name="aspect" id="aspect">
						<option value="{math equation="x/y" x=4  y=3 }">4:3 ({tr}TV{/tr})</option>
						<option value="{math equation="x/y" x=14 y=9 }">14:9 ({tr}Anamorphic{/tr})</option>
						<option value="{math equation="x/y" x=16 y=9 }">16:9 ({tr}Widescreen{/tr})</option>
						<option value="{math equation="x/y" x=16 y=10}">16:10 ({tr}Computer Widescreen{/tr})</option>
					</select>
					<input type="submit" name="aspect_ratio" value="{tr}Set Aspect{/tr}" />
					{formhelp note="Here you can override the initially set aspect ratio. Please note that the displayed aspect aspect ratio might not correspond to the set value."}
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
			{formlabel label="Views" for=""}
			{forminput}
				{$gContent->mInfo.hits|default:"{tr}none{/tr}"}
			{/forminput}
		</div>
	{/if}

	{if $gBitSystem->isFeatureActive( 'treasury_item_view_attid' )}
		{attachhelp legend=1 hash=$gContent->mInfo}
	{/if}
{/form}
{/strip}
