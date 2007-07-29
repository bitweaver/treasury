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
			{formlabel label="Video bitrate" for="treasury_flv_video_bitrate"}
			{forminput}
				{html_options
					options=$rates.video_bitrate
					values=$rates.video_bitrate
					name=treasury_flv_video_bitrate
					id=treasury_flv_video_bitrate
					selected=$gBitSystem->getConfig('treasury_flv_video_bitrate')|default:200000} kbits/s
				{formhelp note="Set the video bitrate. The higher the bitrate the higher the quality but also the larger the file."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Video width" for="treasury_flv_width"}
			{forminput}
				{html_options
					options=$rates.video_width
					values=$rates.video_width
					name=treasury_flv_width
					id=treasury_flv_width
					selected=$gBitSystem->getConfig('treasury_flv_width')|default:320} pixel
				{formhelp note="Set the video width. We recommend 320 pixels. Height of the video will be adjusted automagically."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Audio sample rate" for="treasury_flv_audio_samplerate"}
			{forminput}
				{html_options
					options=$rates.audio_samplerate
					values=$rates.audio_samplerate
					name=treasury_flv_audio_samplerate
					id=treasury_flv_audio_samplerate
					selected=$gBitSystem->getConfig('treasury_flv_audio_samplerate')|default:22050} Hz
				{formhelp note="Set the audio sample rate. The higher the bitrate the higher the quality but also the larger the file."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Audio bitrate" for="treasury_flv_audio_bitrate"}
			{forminput}
				{html_options
					options=$rates.audio_bitrate
					values=$rates.audio_bitrate
					name=treasury_flv_audio_bitrate
					id=treasury_flv_audio_bitrate
					selected=$gBitSystem->getConfig('treasury_flv_audio_bitrate')|default:32} bits/s
				{formhelp note="Set the audio bitrate. The higher the bitrate the higher the quality but also the larger the file."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Default displayed size" for="treasury_flv_default_size"}
			{forminput}
				{html_options
					options=$rates.display_size
					values=$rates.display_size
					name=treasury_flv_default_size
					id=treasury_flv_default_size
					selected=$gBitSystem->getConfig('treasury_flv_default_size')}
				{formhelp note="If you are encoding small versions of the videos you can display larger versions. This will reduce video quality but make the encoded video smaller."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Foreground Colour" for="treasury_flv_frontcolor"}
			{forminput}
				<input type='text' name="treasury_flv_frontcolor" id="treasury_flv_frontcolor" size="10" value="{$gBitSystem->getConfig('treasury_flv_frontcolor')|default:"FFFFFF"}" />
				{formhelp note="Foreground colour of the progress bar."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Background Colour" for="treasury_flv_backcolor"}
			{forminput}
				<input type='text' name="treasury_flv_backcolor" id="treasury_flv_backcolor" size="10" value="{$gBitSystem->getConfig('treasury_flv_backcolor')|default:"000000"}" />
				{formhelp note="Background colour of the progress bar."}
			{/forminput}
		</div>

		<div class="row submit">
			<input type="submit" name="plugin_settings" value="{tr}Save Plugin Settings{/tr}" />
		</div>
	{/form}
{/if}
