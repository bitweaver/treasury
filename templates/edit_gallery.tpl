{strip}
<div class="edit treasury">
	<div class="header">
		<h1>{tr}Create File Gallery{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}

		{form enctype="multipart/form-data" legend="Edit Gallery"}
			{if $gContent->mStructureId}
				<input type="hidden" name="structure_id" value="{$gContent->mStructureId}" />
				<input type="hidden" name="content_id" value="{$galInfo.content_id}" />
				<input type="hidden" name="action" value="{$smarty.request.action}" />

				{if !$galInfo.structure_id || $galInfo.structure_id != $galInfo.root_structure_id}
					<div class="row">
						{formlabel label="Parent" for="treasury-parent"}
						{forminput}
							{if $galInfo.content_id}
								{html_options id="treasury-parent" name="treasury[parent_id]" values=$galleryStructure options=$galleryStructure selected=$galInfo.parent_id disabled=disabled}
							{else}
								{html_options id="treasury-parent" name="treasury[parent_id]" values=$galleryStructure options=$galleryStructure selected=$gContent->mStructureId}
							{/if}
							{formhelp note="Pick where you would like to create a new sub-category. To change the hierarchy of the categories, please visit the change structure page."}
						{/forminput}
					</div>
				{/if}
			{/if}

			<div class="row">
				{formlabel label="Title" for="treasury-title"}
				{forminput}
					<input type="text" size="50" id="treasury-title" name="treasury[title]" value="{$galInfo.title|escape}" />
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Description" for="treasury-desc"}
				{forminput}
					<textarea id="treasury-desc" name="treasury[edit]" rows="3" cols="50">{$galInfo.data|escape}</textarea>
					{formhelp note="A description of the category. This will be visible when users view this particular category."}
				{/forminput}
			</div>

			{* not yet implemented - xing
			<div class="row">
				{formlabel label="Private Gallery" for="treasury-is_private"}
				{forminput}
				<input type="checkbox" id="treasury-is_private" name="treasury[is_private]" value="y" {if $galInfo.is_private}checked="checked" {/if}/>
					{formhelp note="Checking this box will only allow you to upload files to this gallery. Other users can only view and downloaded the files."}
				{/forminput}
			</div>
			*}

			<div class="row">
				{formlabel label="Thumbnail Size" for="gallery_thumb_size"}
				{forminput}
					{html_options values=$imageSizes options=$imageSizes name="treasury[preferences][gallery_thumb_size]" id="gallery_thumb_size" selected=$gContent->getPreference('gallery_thumb_size')}
					{formhelp note="Pick the size of file icons."}
				{/forminput}
			</div>

			{if $gBitSystem->isFeatureActive( 'treasury_gallery_list_thumb' )}
				{if $galInfo.thumbnail_url}
					<div class="row">
						{formlabel label="Gallery Thumbnail" for=""}
						{forminput}
						<img alt="Gallery Thumbnail" src="{$galInfo.thumbnail_url}{$refresh}" />
							{formhelp note=""}
						{/forminput}
					</div>
				{/if}

				<div class="row">
					{formlabel label="Gallery Image" for="icon"}
					{forminput}
						<input type="file" name="icon" id="icon" />
						{formhelp note="Upload image used to identify this gallery. the image will only appear to identify the file gallery and will not be used anywhere else."}
					{/forminput}
				</div>
			{/if}

			<div class="row submit">
				<input type="submit" name="treasury_store" value="{tr}Save Gallery{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .treasury -->
{/strip}
