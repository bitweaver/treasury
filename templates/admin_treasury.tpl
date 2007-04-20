{* $Header: /cvsroot/bitweaver/_bit_treasury/templates/admin_treasury.tpl,v 1.5 2007/04/20 20:46:25 laetzer Exp $ *}
{strip}
{form}
	<input type="hidden" name="page" value="{$page}" />

	{jstabs}
		{jstab title="Settings"}
			{legend legend="Treasury Settings"}
				{foreach from=$treasurySettings key=feature item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$feature}
						{forminput}
							{if $output.type == 'checkbox'}
								{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{else}
								<input type='text' name="{$feature}" id="{$feature}" size="{if $output.type == 'text'}40{else}5{/if}" value="{$gBitSystem->getConfig($feature)|escape}" /> {$output.unit}
							{/if}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Gallery Listing"}
			{legend legend="Gallery Listing"}
				<div class="row">
					{formlabel label="Gallery Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="treasury_gallery_list_thumb" selected=$gBitSystem->getConfig('treasury_gallery_list_thumb')}
						{formhelp note="This is the size of the gallery image if one is uploaded."}
					{/forminput}
				</div>
		
				{foreach from=$galleryListing key=feature item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$feature}
						{forminput}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Item Listing"}
			{legend legend="Item Listing"}
				<div class="row">
					{formlabel label="Item List Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="treasury_item_list_thumb" selected=$gBitSystem->getConfig('treasury_item_list_thumb')}
						{formhelp note="The size of icons displayed in the item list."}
					{/forminput}
				</div>
    		
				{foreach from=$itemListing key=feature item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$feature}
						{forminput}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Item Viewing"}
			{legend legend="Item Viewing"}
				<div class="row">
					{formlabel label="Item List Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="treasury_item_view_thumb" selected=$gBitSystem->getConfig('treasury_item_view_thumb')}
						{formhelp note="Size of the image displyed when viewing an item."}
					{/forminput}
				</div>
    		
				{foreach from=$itemViewing key=feature item=output}
					<div class="row">
						{formlabel label=`$output.label` for=$feature}
						{forminput}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{formhelp note=`$output.note` page=`$output.page`}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}
	{/jstabs}

	<div class="row submit">
		<input type="submit" name="treasury_settings" value="{tr}Change preferences{/tr}" />
	</div>
{/form}
{/strip}