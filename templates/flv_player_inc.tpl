{strip}
<p id="flv_player_{$flv.content_id}"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this video.</p>
<script type="text/javascript">/* <![CDATA[ */
	var FO = {ldelim} movie:"{$smarty.const.TREASURY_PKG_URL}libs/flv_player/flvplayer.swf",width:"{$flv_prefs.flv_width|default:320}",height:"{if $flv_prefs.flv_height}{$flv_prefs.flv_height+20}{else}260{/if}",majorversion:"7",build:"0",bgcolor:"#FFFFFF",
	flashvars:"file={$flv.flv_url}&showdigits=true&autostart=false&image={$flv.thumbnail_url.medium}&showfsbutton=true&repeat=false" {rdelim};
	UFO.create( FO, "flv_player_{$flv.content_id}");
/* ]]> */</script>
{/strip}
