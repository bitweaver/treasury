{strip}
<p id="flv_player_{$flv.content_id}"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this video.</p>
<script type="text/javascript">/* <![CDATA[ */
	var so = new SWFObject('{$smarty.const.TREASURY_PKG_URL}libs/flv_player/flvplayer.swf','player','{$flvPrefs.flv_width}','{$flvPrefs.flv_height+20}','7');
	so.addVariable("file","{$flv.flv_url}");
	so.addVariable("image","{$flv.thumbnail_url.medium}");
	so.addVariable("overstretch","fit");
	so.addVariable("frontcolor","0x{$gBitSystem->getConfig('treasury_flv_frontcolor','FFFFFF')}");
	so.addVariable("backcolor","0x{$gBitSystem->getConfig('treasury_flv_backcolor','000000')}");
	so.write('flv_player_{$flv.content_id}');
/* ]]> */</script>
{/strip}
