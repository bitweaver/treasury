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

							<p class="warning">{biticon ipackage="icons" iname="dialog-warning" iexplain=Warning iforce=icon} The maximum file size you can upload is {$uploadMax} Megabytes</p>
							{formfeedback hash=$feedback}

							<input type="hidden" name="treasury_store" value="true" />
							<input type="hidden" name="MAX_FILE_SIZE" value="{$max_file_size}" />

							{if $gBitSystem->isPackageActive( 'gigaupload' )}
								{$gigaPopup}
								{include file="bitpackage:gigaupload/form_inc.tpl"}
							{elseif $gBitSystem->isFeatureActive( 'treasury_extended_upload_slots' )}
								<br />
								<h2>{tr}Upload Files{/tr}</h2>

								<div id="slot0">
									<div class="row">
										{formlabel label="Title" for="title0"}
										{forminput}
											<input type="text" name="filedata[0][title]" id="title0" size="50" />
											<br />
											<input type="file" name="upload0" id="upload0" size="35" />
										{/forminput}
									</div>

									<div class="row">
										{formlabel label="Description" for="edit0"}
										{forminput}
											<textarea rows="2" cols="50" name="filedata[0][edit]" id="edit0"></textarea>
										{/forminput}
									</div>

									<script type="text/javascript">/* <![CDATA[ */
										document.write( "<input id=\"button1\" type=\"button\" onclick=\"javascript:showById('slot1');hideById('button1')\" value=\"{tr}Add slot{/tr}\" />" );
									/* ]]> */</script>
								</div>

								{foreach from=$uploadSlots item=dummy key=key}
									{assign var=slot value=$key+2}

									<script type="text/javascript">/* <![CDATA[ */
										document.write( "<div id=\"slot{$slot-1}\" style=\"display:none;\">" );
									/* ]]> */</script>

									<hr />

									<div class="row">
										{formlabel label="Title" for="title$slot"}
										{forminput}
										<input type="text" name="filedata[{$slot}][title]" id="title{$slot}" size="50" />
											<br />
											<input type="file" name="upload{$slot}" id="upload{$slot}" size="35" />
										{/forminput}
									</div>

									<div class="row">
										{formlabel label="Description" for="edit$slot"}
										{forminput}
										<textarea rows="2" cols="50" name="filedata[{$slot}][edit]" id="edit{$slot}"></textarea>
										{/forminput}
									</div>

									<script type="text/javascript">/* <![CDATA[ */
										{if count($uploadSlots) gt $slot-1}
											document.write( "<input id=\"button{$slot}\" type=\"button\" onclick=\"javascript:showById('slot{$slot}');hideById('button{$slot}')\" value=\"{tr}Add slot{/tr}\" />" );
										{else}
											document.write( "</div>" );
										{/if}
									/* ]]> */</script>
								{/foreach}
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

							<div class="row">
								{formlabel label="Add File(s) to these Galleries"}
								{forminput}
									{foreach from=$galleryList item=gallery}
										{include file="bitpackage:treasury/structure_inc.tpl" subtree=$gallery.subtree ifile="view.php" checkbox=1}
										<br />
									{foreachelse}
										<p class="norecords">
											{tr}No Galleries Found{/tr}.<br />
											{tr}The following gallery will automatically be created for you{/tr}: <strong>File Gallery</strong>
										</p>
									{/foreach}
								{/forminput}
							</div>

							{capture assign=options}
								{foreach from=$gTreasurySystem->mPlugins item=plugin}
									{if $plugin.processing_options}{$plugin.processing_options}<br />{/if}
								{/foreach}
							{/capture}

							{if $options}
								<div class="row">
									{formlabel label="File Processing Options" for=""}
									{forminput}
										{foreach from=$gTreasurySystem->mPlugins item=plugin}
											{if $plugin.processing_options}{$plugin.processing_options}<br />{/if}
										{/foreach}
									{/forminput}
								</div>
							{/if}

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
				<input type="submit" id="submitbutton" value="Upload File(s)" {if $submitClick}onclick="{$submitClick}"{/if}/>
			</div>
		{/form}
	</div> <!-- end .body -->
</div> <!-- end .treasury -->
{/strip}
