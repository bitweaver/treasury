{strip}
<p id="flv_player_{$flv.content_id}"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this video.</p>
<script type="text/javascript">/* <![CDATA[ */
	var FO = {ldelim} movie:"{$smarty.const.TREASURY_PKG_URL}libs/flv_player/flvplayer.swf",width:"{$flvPrefs.flv_width}",height:"{$flvPrefs.flv_height+20}",majorversion:"7",build:"0",bgcolor:"#FFFFFF",
	flashvars:"file={$flv.flv_url}&showdigits={$flvPrefs.digits|default:'true'}&autostart=false&image={$flv.thumbnail_url.medium}&showfsbutton=false&repeat=false" {rdelim};
	UFO.create( FO, "flv_player_{$flv.content_id}");
/* ]]> */</script>
{/strip}
