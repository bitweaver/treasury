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

		<div class="row">
			{formlabel label="Default displayed size" for="treasury_flv_default_size"}
			{forminput}
				<select name="treasury_flv_default_size" id="treasury_flv_default_size">
					<option value="0" >{tr}Same as encoded video{/tr}</option>
					<option value="240" {if $gBitSystem->getConfig('treasury_flv_default_size') == 240}selected="selected"{/if}>{tr}Small{/tr}</option>
					<option value="320" {if $gBitSystem->getConfig('treasury_flv_default_size') == 320}selected="selected"{/if}>{tr}Medium{/tr}</option>
					<option value="480" {if $gBitSystem->getConfig('treasury_flv_default_size') == 480}selected="selected"{/if}>{tr}Large{/tr}</option>
					<option value="640" {if $gBitSystem->getConfig('treasury_flv_default_size') == 640}selected="selected"{/if}>{tr}Huge{/tr}</option>
				</select>
				{formhelp note="If you are encoding small versions of the videos you can display larger versions. This will reduce video quality but make the encoded video smaller."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Foreground Colour" for="treasury_flv_frontcolor"}
			{forminput}
				<input type='text' name="treasury_flv_frontcolor" id="treasury_flv_frontcolor" size="10" value="{$gBitSystem->getConfig('treasury_flv_frontcolor')|default:"FFFFFF"}" />
				{formhelp note="Foreground colour of the progress bar"}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Background Colour" for="treasury_flv_backcolor"}
			{forminput}
				<input type='text' name="treasury_flv_backcolor" id="treasury_flv_backcolor" size="10" value="{$gBitSystem->getConfig('treasury_flv_backcolor')|default:"000000"}" />
				{formhelp note="Background colour of the progress bar"}
			{/forminput}
		</div>

		<div class="row submit">
			<input type="submit" name="plugin_settings" value="{tr}Save Plugin Settings{/tr}" />
		</div>
	{/form}
{/if}
