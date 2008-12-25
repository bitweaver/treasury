{if $gBitSystem->isPackageActive('gigaupload')}
	{include file="bitpackage:gigaupload/js_inc.tpl"}
{else}
	{assign var=onSubmit value="javascript:disableSubmit('submitbutton');"}
	{assign var=id value=treasure}
{/if}

{strip}
<div class="edit treasury">
	<div class="header">
		<h1>{tr}Upload Files{/tr}</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data" onsubmit=$onSubmit id=$id target=$target action=$action}
			<div id="uploadblock">
				{jstabs}
					{jstab title="Upload Files"}
						{legend legend="Upload Files"}
							{formfeedback note=$quotaMessage}

							<p class="warning">{biticon ipackage="icons" iname="dialog-warning" iexplain=Warning iforce=icon} {tr}The maximum file size you can upload is {$uploadMax} Megabytes{/tr}</p>
							{formfeedback hash=$feedback}

							<input type="hidden" name="treasury_store" value="true" />
							<input type="hidden" name="MAX_FILE_SIZE" value="{$max_file_size}" />

							{if $gBitSystem->isPackageActive( 'gigaupload' )}
								{$gigaPopup}
								{include file="bitpackage:gigaupload/form_inc.tpl"}
							{elseif $gBitSystem->isFeatureActive( 'treasury_extended_upload_slots' )}
								<h2>{tr}Upload Files{/tr}</h2>
								{include file="bitpackage:liberty/edit_format.tpl"}
								{include file="bitpackage:kernel/upload_slot_inc.tpl"}
							{else}
								<div class="row">
									{formlabel label="Select File(s)"}
									{forminput}
										<input type="file" name="file0" id="fileupload" />
										{formhelp note="To upload more than one file, please click on choose repeatedly<br />(javascript has to be enabled for this to work)."}
									{/forminput}
								</div>

								<div class="row">
									{formlabel label="Selected File(s)" for=""}
									{forminput}
										<div id="fileslist"></div>
										<div class="clear"></div>
										{formhelp note="These files will be uploaded when you hit the upload button below."}
										<script type="text/javascript">/* <![CDATA[ Multi file upload */
											var multi_selector = new MultiSelector( document.getElementById( 'fileslist' ), 10 );
											multi_selector.addElement( document.getElementById( 'fileupload' ) );
										/* ]]> */</script>
									{/forminput}
								</div>
							{/if}

							{if $gBitSystem->isFeatureActive( 'treasury_file_import_path' ) && $gBitUser->hasPermission( 'p_treasury_import_item' )}
								<h2>{tr}Import File{/tr}</h2>
								<div class="row">
									{formlabel label="Import File Title" for="import_title"}
									{forminput}
										<input type="text" name="import[title]" id="import_title" size="40" />
									{/forminput}
								</div>

								<div class="row">
									{formlabel label="Import file" for="ajax_input"}
									{forminput}
										<input type="text" name="import[file]" id="ajax_input" size="40" />
										{formhelp note="You can click on the load files link below to display available files and it will also insert the correct path to the desired file you wish to import. e.g.: public/video.mpg"}
									{/forminput}
								</div>

								<div class="row">
									{formlabel label="Description" for="import_edit"}
									{forminput}
										<textarea rows="2" cols="40" name="import[edit]" id="import_edit"></textarea>
									{/forminput}
								</div>

								{include file="bitpackage:kernel/ajax_file_browser_inc.tpl" ajax_path_conf=treasury_file_import_path}
								<hr />
							{/if}

							<div class="row">
								{formlabel label="Add File(s) to these Galleries"}
								{forminput}
									{foreach from=$galleryList item=gallery}
										{include file="bitpackage:treasury/structure_inc.tpl" subtree=$gallery.subtree checkbox=1}
									{foreachelse}
										<p class="norecords">
											{tr}No Galleries Found{/tr}.<br />
											{tr}The following gallery will automatically be created for you{/tr}: <strong>File Gallery</strong>
										</p>
									{/foreach}
								{/forminput}
							</div>

							{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl}

							{if $gBitSystem->isPackageActive( 'gigaupload' )}
								{include file="bitpackage:gigaupload/progress_container_inc.tpl"}
							{/if}
						{/legend}
					{/jstab}

					{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_tab_tpl}

					{include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_upload_tab_tpl}
				{/jstabs}
			</div> <!-- end #uploadblock -->

			<div class="row submit">
				<noscript><p class="highlight">{tr}Please don't press the save button more than once!<br />Depending on what you are uploading and the system, this can take a few minutes.{/tr}</p></noscript>
				<input type="submit" id="submitbutton" value="{tr}Upload File(s){/tr}" {if $submitClick}onclick="{$submitClick}"{/if}/>
			</div>
		{/form}
	</div> <!-- end .body -->
</div> <!-- end .treasury -->
{/strip}
