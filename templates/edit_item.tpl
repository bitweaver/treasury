{strip}
<div class="edit treasury">
	<div class="header">
		<h1>{tr}Edit File{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}

		{form enctype="multipart/form-data" legend="Edit File"}
			{jstabs}
				{jstab title="Edit File"}
					<input type="hidden" name="content_id" value="{$gContent->mContentId}" />
					<input type="hidden" name="refresh" value="1" />

					<div class="row">
						{formlabel label="File title" for=title}
						{forminput}
							<input type="text" size="40" id="title" value="{$gContent->mInfo.title|escape}" name="title" />
						{/forminput}
					</div>

					{textarea label="Description"}{$gContent->mInfo.data}{/textarea}

					{if $gBitUser->isAdmin() || $gContent->isOwner()}
						{include file=$gContent->getMimeTemplate('edit',$gContent->mInfo.attachment_plugin_guid) attachment=$gContent->mInfo}
					{/if}

					<div class="row">
						{formfeedback warning="{tr}Uploading a new file will replace the currently existing one.{/tr}"}
						{formlabel label="Replace File" for="fileupload"}
						{forminput}
							<input type="file" name="file" id="fileupload" />
							{formhelp note="Upload a new file to replace the current one."}
							<input type="submit" name="reprocess_upload" value="{tr}Re-process uploaded File{/tr}" />
							{formhelp note="This will process the already uploaded file as if you're uploading it for the first time. This will allow you to apply spcific file processing options if available."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Thumbnail Maintenance" for="icon"}
						{forminput}
							<input type="file" id="icon" name="icon" />
							{formhelp note="Upload an image that represents this file. The image will be scaled automatically."}
							<input type="submit" name="delete_thumbnails" value="{tr}Delete Thumbnail{/tr}" />
							{formhelp note="This will remove the current thumbnail and it will use the appropriate mimetype icon instead."}
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

					{capture assign=options}
						{foreach from=$gLibertySystem->mPlugins item=plugin}
							{if $plugin.processing_options}{$plugin.processing_options}<br />{/if}
						{/foreach}
					{/capture}

					{if $options}
						<div class="row">
							{formlabel label="File Processing Options" for=""}
							{forminput}
								{foreach from=$gLibertySystem->mPlugins item=plugin}
									{if $plugin.processing_options}{$plugin.processing_options}<br />{/if}
								{/foreach}
							{/forminput}
						</div>
					{/if}

					{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl}

					<div class="row submit">
						<input type="submit" name="update_file" value="{tr}Update File{/tr}" />
					</div>
				{/jstab}

				{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_tab_tpl no_help_tab=1}
			{/jstabs}
		{/form}

		<br />
		<a href="{$gContent->mInfo.display_url}">Return to file</a>

		<h2>{tr}Preview{/tr}</h2>
		<p class="formhelp">{tr}Please note items remaining in the browser cache might still display the previous item even if the upload worked. If this occurs, empty out the browser cache and return to the file page.{/tr}</p>

		<div class="preview">
			{include file=$gContent->getMimeTemplate('view',$gContent->mInfo.attachment_plugin_guid) attachment=$gContent->mInfo}
		</div>
	</div><!-- end .body -->
</div><!-- end .treasury -->
{/strip}
