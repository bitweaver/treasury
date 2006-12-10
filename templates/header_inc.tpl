{strip}
{if $gBitSystem->isPackageActive( 'rss' ) and $gBitSystem->isFeatureActive( 'treasury_rss' ) and $smarty.const.ACTIVE_PACKAGE eq 'treasury' and $gBitUser->hasPermission( 'p_treasury_view_item' )}
	{if $gContent->mContentTypeGuid == $smarty.const.TREASURYGALLERY_CONTENT_TYPE_GUID}
		<link rel="alternate" type="application/rss+xml" title="{$gBitSystem->getConfig('treasury_rss_title',"{tr}File Galleries{/tr} RSS")}" href="{$smarty.const.TREASURY_PKG_URL}treasury_rss.php?version={$gBitSystem->getConfig('rssfeed_default_version',0)}&amp;gallery_content_id={$gContent->mContentId}" />
	{elseif $gContent->mContentTypeGuid == $smarty.const.TREASURYITEM_CONTENT_TYPE_GUID}
		{foreach from=$gContent->mInfo.galleries item=treasury_rss_gal}
			<link rel="alternate" type="application/rss+xml" title="{$gBitSystem->getConfig('treasury_rss_title',"{tr}File Galleries{/tr} RSS")} - {$treasury_rss_gal.title}" href="{$smarty.const.TREASURY_PKG_URL}treasury_rss.php?version={$gBitSystem->getConfig('rssfeed_default_version',0)}&amp;gallery_content_id={$treasury_rss_gal.content_id}" />
		{/foreach}
	{/if}
{/if}
{/strip}
