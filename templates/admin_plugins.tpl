{strip}
<div class="admin treasury">
	<div class="header">
		<h1>{tr}Admin Tresaury Plugins{/tr}</h1>
	</div>

	<div class="body">
		{form}
			{formfeedback hash=$feedback}

			<table class="panel">
				<caption>{tr}Treasury Plugins{/tr}</caption>
				<tr>
					<th style="width:70%;">{tr}Plugin{/tr}</th>
					<th style="width:20%;">{tr}GUID{/tr}</th>
					<th style="width:10%;">{tr}Active{/tr}</th>
				</tr>

				{foreach from=$gTreasurySystem->mPlugins item=plugin key=guid}
					<tr class="{cycle values="odd,even"}">
						<td>
							<h3>{$plugin.title|escape}</h3>
							<label for="{$guid}">
								{$plugin.description|escape}
							</label>
						</td>
						<td>{$guid}</td>
						<td align="center">
							{if $plugin.is_active == 'x'}
								{tr}Missing{/tr}
							{elseif $guid == $smarty.const.TREASURY_DEFAULT_MIME_HANDLER}
								{tr}Default{/tr}
								<input type="hidden" name="plugins[{$guid}]" value="y" />
							{else}
								{html_checkboxes name="plugins[`$guid`]" values="y" checked=`$plugin.is_active` labels=false id=$guid}
							{/if}
						</td>
					</tr>
				{/foreach}
			</table>

			<div class="row submit">
				<input type="submit" name="pluginsave" value="{tr}Save Plugin Settings{/tr}" />
			</div>

			<div class="row">
				{formlabel label="Reset all plugin settings" for=""}
				{forminput}
					<input type="submit" name="reset_all_plugins" value="{tr}Reset Plugins{/tr}" />
					{formhelp note="This will remove all plugin settings from the database and reset them to the default values. This can be useful if some plugins don't seem to work or you simply want to reset all values on this page."}
				{/forminput}
			</div>
		{/form}

		{if $gTreasurySystem->isPluginActive( 'mime_flv' )}
			{form legend="Flashvideo specific settings"}
				<div class="row">
					{formlabel label="Path to ffmpeg" for="treasury_flv_ffmpeg_path"}
					{forminput}
						<input type='text' name="treasury_flv_ffmpeg_path" id="treasury_flv_ffmpeg_path" size="40" value="{$gBitSystem->getConfig('treasury_flv_ffmpeg_path')|escape|default:$ffmpeg_path}" />
						{formhelp note="If this path is not correct, please set the correct path to ffmepg."}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Video bitrate" for="treasury_flv_video_rate"}
					{forminput}
						<select name="treasury_flv_video_rate" id="treasury_flv_video_rate">
							<option value="11025" {if $gBitSystem->getConfig('treasury_flv_video_rate') == 11025}selected="selected"{/if}>11025</option>
							<option value="22050" {if $gBitSystem->getConfig('treasury_flv_video_rate') == 22050 || !$gBitSystem->isFeatureActive('treasury_flv_video_rate')}selected="selected"{/if}>22050</option>
							<option value="44100" {if $gBitSystem->getConfig('treasury_flv_video_rate') == 44100}selected="selected"{/if}>44100</option>
						</select>
						{formhelp note="Set the video bitrate. The higher the bitrate the higher the quality but also the larger the file."}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Audio bitrate" for="treasury_flv_audio_rate"}
					{forminput}
						<select name="treasury_flv_audio_rate" id="treasury_flv_audio_rate">
							<option value="16" {if $gBitSystem->getConfig('treasury_flv_audio_rate') == 16}selected="selected"{/if}>16</option>
							<option value="32" {if $gBitSystem->getConfig('treasury_flv_audio_rate') == 32 || !$gBitSystem->isFeatureActive('treasury_flv_audio_rate')}selected="selected"{/if}>32</option>
							<option value="64" {if $gBitSystem->getConfig('treasury_flv_audio_rate') == 64}selected="selected"{/if}>64</option>
						</select>
						{formhelp note="Set the video bitrate. The higher the bitrate the higher the quality but also the larger the file."}
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Video width" for="treasury_flv_width"}
					{forminput}
						<select name="treasury_flv_width" id="treasury_flv_width">
							<option value="240" {if $gBitSystem->getConfig('treasury_flv_width') == 240}selected="selected"{/if}>240</option>
							<option value="320" {if $gBitSystem->getConfig('treasury_flv_width') == 320 || !$gBitSystem->isFeatureActive('treasury_flv_width')}selected="selected"{/if}>320</option>
							<option value="480" {if $gBitSystem->getConfig('treasury_flv_width') == 480}selected="selected"{/if}>480</option>
							<option value="640" {if $gBitSystem->getConfig('treasury_flv_width') == 640}selected="selected"{/if}>640</option>
						</select> pixel
						{formhelp note="Set the video width. We recommend 320 pixels. Height of the video will be adjusted automagically."}
					{/forminput}
				</div>

				<div class="row submit">
					<input type="submit" name="plugin_settings" value="{tr}Save Plugin Settings{/tr}" />
				</div>
			{/form}
		{/if}
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
