{* $Header$ *}
{strip}
{form}
	<input type="hidden" name="page" value="{$page}" />

	{jstabs}
		{jstab title="Settings"}
			{legend legend="Treasury Settings"}
				{foreach from=$treasurySettings key=feature item=output}
					<div class="form-group">
						{formlabel label=$output.label for=$feature}
						{forminput}
							{if $output.type == 'checkbox'}
								{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{else}
								<input type='text' name="{$feature}" id="{$feature}" size="{if $output.type == 'text'}40{else}5{/if}" value="{$gBitSystem->getConfig($feature)|escape}" /> {$output.unit}
							{/if}
							{formhelp note=$output.note page=$output.page}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Gallery Listing"}
			{legend legend="Gallery Listing"}
				<div class="form-group">
					{formlabel label="Gallery Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="treasury_gallery_view_thumb" selected=$gBitSystem->getConfig('treasury_gallery_view_thumb')}
						{formhelp note="This is the size of the gallery image when viewing the gallery."}
					{/forminput}
				</div>

				<div class="form-group">
					{formlabel label="Gallery List Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="treasury_gallery_list_thumb" selected=$gBitSystem->getConfig('treasury_gallery_list_thumb')}
						{formhelp note="This is the size of the gallery image when viewing the gallery list."}
					{/forminput}
				</div>

				{foreach from=$galleryListing key=feature item=output}
					<div class="form-group">
						{formlabel label=$output.label for=$feature}
						{forminput}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{formhelp note=$output.note page=$output.page}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Item Listing"}
			{legend legend="Item Listing"}
				<div class="form-group">
					{formlabel label="Item List Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="treasury_item_list_thumb" selected=$gBitSystem->getConfig('treasury_item_list_thumb')}
						{formhelp note="The size of icons displayed in the item list."}
					{/forminput}
				</div>

				{foreach from=$itemListing key=feature item=output}
					<div class="form-group">
						{formlabel label=$output.label for=$feature}
						{forminput}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{formhelp note=$output.note page=$output.page}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}

		{jstab title="Item Viewing"}
			{legend legend="Item Viewing"}
				<div class="form-group">
					{formlabel label="Item List Thumbnail Size"}
					{forminput}
						{html_options values=$imageSizes options=$imageSizes name="treasury_item_view_thumb" selected=$gBitSystem->getConfig('treasury_item_view_thumb')}
						{formhelp note="Size of the image displyed when viewing an item."}
					{/forminput}
				</div>

				{foreach from=$itemViewing key=feature item=output}
					<div class="form-group">
						{formlabel label=$output.label for=$feature}
						{forminput}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
							{formhelp note=$output.note page=$output.page}
						{/forminput}
					</div>
				{/foreach}
			{/legend}
		{/jstab}
	{/jstabs}

	<div class="form-group submit">
		<input type="submit" class="btn btn-default" name="treasury_settings" value="{tr}Change preferences{/tr}" />
	</div>
{/form}
{/strip}
