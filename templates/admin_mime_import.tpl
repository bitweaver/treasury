{* $Header: /cvsroot/bitweaver/_bit_treasury/templates/admin_mime_import.tpl,v 1.1 2008/07/15 09:28:44 squareing Exp $ *}
{strip}
<div class="admin liberty">
	<div class="header">
		<h1>{tr}Import Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Import specific settings"}
			{formfeedback hash=$feedback}
			{foreach from=$settings key=feature item=output}
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

			<div class="row submit">
				<input type="submit" name="settings_store" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}
