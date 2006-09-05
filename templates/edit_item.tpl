{strip}
<div class="edit treasury">
	<div class="header">
		<h1>{tr}Edit File{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}

		{form enctype="multipart/form-data" legend="Edit File"}
			<input type="hidden" name="content_id" value="{$gContent->mContentId}" />
			<input type="hidden" name="refresh" value="1" />

			<div class="row">
				{formlabel label="File title" for=title}
				{forminput}
					<input type="text" size="40" id="title" value="{$gContent->mInfo.title}" name="treasury[title]" />
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Description" for="edit"}
				{forminput}
					<textarea cols="50" rows="3" id="edit" name="treasury[edit]" />{$gContent->mInfo.data|escape}</textarea>
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Replace File" for="fileupload"}
				{forminput}
					<input type="file" name="file" id="fileupload" />
					{formhelp note="Upload a new file to replace the current one."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Thumbnail Maintenance" for="icon"}
				{forminput}
					<input type="file" id="icon" name="icon" />
					{formhelp note="Upload an image that represents this file. The image will be scaled automatically."}

					<br />
					<input type="submit" name="reset_thumbnails" value="{tr}Recreate original Thumbnail{/tr}" />
					<br />
					<input type="submit" name="delete_thumbnails" value="{tr}Delete Thumbnail{/tr}" />
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Add File to these Galleries"}
				{forminput}
					{foreach from=$galleryList item=gallery}
						{include file="bitpackage:treasury/structure_inc.tpl" subtree=$gallery.subtree ifile="view.php" checkbox=1}
					{/foreach}
				{/forminput}
			</div>

			{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl}

			<div class="row submit">
				<input type="submit" name="update_file" value="{tr}Update File{/tr}" />
			</div>
		{/form}

		<br />
		<a href="{$gContent->mInfo.display_url}">Return to file</a>

		<h2>{tr}Preview{/tr}</h2>

		<div class="preview">
			{assign var=guid value=$gContent->mInfo.plugin_guid}
			{include file=$gTreasurySystem->mPlugins.$guid.view_tpl}
		</div>
	</div><!-- end .body -->
</div><!-- end .treasury -->
{/strip}
