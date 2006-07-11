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
					<input type="text" id="title" value="{$gContent->mInfo.title}" name="treasury[title]" />
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Description" for="edit"}
				{forminput}
					<textarea cols="50" rows="3" id="edit" name="treasury[edit]" />{$gContent->mInfo.data|escape}</textarea>
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Custom Icon" for="icon"}
				{forminput}
					<input type="file" id="icon" name="icon" />
					{formhelp note="Upload an image that represents this file. The image will be scaled automatically."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Reset Icon" for="reset_thumbnails"}
				{forminput}
					<input type="checkbox" id="reset_thumbnails" name="reset_thumbnails" value="1" />
					{formhelp note="Check this if you want to reset the file icon to its original state."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Add File(s) to these Galleries"}
				{forminput}
					{foreach from=$galleryList item=gallery}
						{include file="bitpackage:treasury/structure_inc.tpl" subtree=$gallery.subtree ifile="view.php" noicons=1 checkbox=1}
					{/foreach}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="update_file" value="{tr}Apply Settings{/tr}" />
			</div>
		{/form}

		<h2>{tr}Preview{/tr}</h2>

		<div class="preview">
			{assign var=guid value=$gContent->mInfo.plugin_guid}
			{include file=$gTreasurySystem->mPlugins.$guid.view_tpl}
		</div>
	</div><!-- end .body -->
</div><!-- end .treasury -->
{/strip}
