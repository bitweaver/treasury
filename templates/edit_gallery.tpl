{strip}
<div class="edit treasury">
	<div class="header">
		<h1>{tr}Create File Gallery{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}

		{form enctype="multipart/form-data"}
			{jstabs}
				{jstab title="Edit Gallery"}
					{legend legend="Edit Gallery"}
						{if $gContent->mStructureId}
							<input type="hidden" name="structure_id" value="{$gContent->mStructureId}" />
							<input type="hidden" name="gallery_content_id" value="{$galInfo.content_id}" />
							<input type="hidden" name="action" value="{$smarty.request.action}" />

							{if !$galInfo.structure_id || $galInfo.structure_id != $galInfo.root_structure_id}
								<div class="row">
									{formlabel label="Parent" for="treasury-parent"}
									{forminput}
										{if $galInfo.content_id}
											{html_options id="treasury-parent" name="parent_id" values=$galleryStructure options=$galleryStructure selected=$galInfo.parent_id disabled=disabled}
										{else}
											{html_options id="treasury-parent" name="parent_id" values=$galleryStructure options=$galleryStructure selected=$gContent->mStructureId}
										{/if}
										{formhelp note="Pick where you would like to create a new sub-category. To change the hierarchy of the categories, please visit the change structure page."}
									{/forminput}
								</div>
							{/if}
						{/if}

						<div class="row">
							{formlabel label="Title" for="treasury-title"}
							{forminput}
								<input type="text" size="50" id="treasury-title" name="title" value="{$galInfo.title|escape}" />
							{/forminput}
						</div>

						{textarea label="Description"}{$galInfo.data}{/textarea}

						<div class="row">
							{formlabel label="File Comments" for="treasury-comments"}
							{forminput}
								<input type="checkbox" id="treasury-comments" name="preferences[allow_comments]" value="y" {if $gContent->getPreference('allow_comments')}checked="checked" {/if}/>
								{formhelp note="Allow users to leave comments to files in this gallery."}
							{/forminput}
						</div>

						{* not yet implemented - xing
						<div class="row">
							{formlabel label="Private Gallery" for="treasury-is_private"}
							{forminput}
								<input type="checkbox" id="treasury-is_private" name="is_private" value="y" {if $galInfo.is_private}checked="checked" {/if}/>
								{formhelp note="Checking this box will only allow you to upload files to this gallery. Other users can only view and downloaded the files."}
							{/forminput}
						</div>
						*}

						{if $gBitSystem->isFeatureActive( 'treasury_item_list_thumb_custom' )}
							<div class="row">
								{formlabel label="File Thumbnail Size" for="item_list_thumb_size"}
								{forminput}
									{html_options values=$imageSizes options=$imageSizes name="preferences[item_list_thumb_size]" id="item_list_thumb_size" selected=$gContent->getPreference('item_list_thumb_size')|default:$gBitSystem->getConfig('treasury_item_list_thumb')}
									{formhelp note="Pick the size of preview icon in the file list."}
								{/forminput}
							</div>
						{else}
							<input type="hidden" name="preferences[item_list_thumb_size]" value="{$gBitSystem->getConfig('treasury_item_list_thumb')}" />
						{/if}

						{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_thumb' )}
							{if $galInfo.thumbnail_url}
								<div class="row">
									{formlabel label="Gallery Thumbnail" for=""}
									{forminput}
									<img alt="Gallery Thumbnail" src="{$galInfo.thumbnail_url}{$refresh}" />
										{formhelp note=""}
									{/forminput}
								</div>

								{formfeedback warning="Uploading a new image will replace the one displayed above."}
							{/if}

							<div class="row">
								{formlabel label="Gallery Image" for="icon"}
								{forminput}
									<input type="file" name="icon" id="icon" />
									{formhelp note="Upload image used to identify this gallery. the image will only appear to identify the file gallery and will not be used anywhere else."}
								{/forminput}
							</div>
						{/if}

						{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl}

						<div class="row submit">
							<input type="submit" name="treasury_store" value="{tr}Save Gallery{/tr}" />
						</div>
					{/legend}
				{/jstab}

				{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_tab_tpl}
			{/jstabs}
		{/form}
	</div><!-- end .body -->
</div><!-- end .treasury -->
{/strip}
