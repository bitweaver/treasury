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
			{legend legend="Upload Files"}
				{formfeedback note=$quotaMessage}

				<p class="warning">{biticon ipackage="icons" iname="dialog-warning" iexplain=Warning iforce=icon} The maximum file size you can upload is {$uploadMax} Megabytes</p>
				{formfeedback hash=$feedback}

				<input type="hidden" name="treasury_store" value="true" />
				<input type="hidden" name="MAX_FILE_SIZE" value="{$max_file_size}" />

				<div id="uploadblock">
					{if $gBitSystem->isPackageActive( 'gigaupload' )}
						{$gigaPopup}
						{include file="bitpackage:gigaupload/form_inc.tpl"}
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

					<div class="row">
						{formlabel label="File Processing Options" for=""}
						{forminput}
							{foreach from=$gTreasurySystem->mPlugins item=plugin}
								{if $plugin.processing_options}{$plugin.processing_options}<br />{/if}
							{/foreach}
						{/forminput}
					</div>
				</div>

				{if $gBitSystem->isPackageActive( 'gigaupload' )}
					{include file="bitpackage:gigaupload/progress_container_inc.tpl"}
				{/if}

				<div class="row submit">
					<noscript><p class="highlight">{tr}Please don't press the save button more than once!<br />Depending on what you are uploading and the system, this can take a few minutes.{/tr}</p></noscript>
					<input type="submit" id="submitbutton" value="Upload File(s)" {if $submitClick}onclick="{$submitClick}"{/if}/>
				</div>
			{/legend}
		{/form}
	</div> <!-- end .body -->
</div> <!-- end .treasury -->
{/strip}
