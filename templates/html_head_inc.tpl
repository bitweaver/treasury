{strip}
{if $gBitSystem->isPackageActive( 'rss' ) and $gBitSystem->isFeatureActive( 'treasury_rss' ) and $gBitSystem->getActivePackage() eq 'treasury' and $gBitUser->hasPermission( 'p_treasury_view_item' )}
	{if $gGallery}
		<link rel="alternate" type="application/rss+xml" title="{$gBitSystem->getConfig('treasury_rss_title',"{tr}File Galleries{/tr} RSS")} - {$gGallery->getTitle()}" href="{$smarty.const.TREASURY_PKG_URL}treasury_rss.php?version={$gBitSystem->getConfig('rssfeed_default_version',0)}&amp;gallery_content_id={$gGallery->mContentId}" />
	{else}
		<link rel="alternate" type="application/rss+xml" title="{$gBitSystem->getConfig('treasury_rss_title',"{tr}File Galleries{/tr} RSS")}" href="{$smarty.const.TREASURY_PKG_URL}treasury_rss.php?version={$gBitSystem->getConfig('rssfeed_default_version',0)}&amp;gallery_content_id={$gContent->mContentId}" />
	{/if}
{/if}
{/strip}
