{strip}
<div class="admin treasury">
	<div class="header">
		<h1>{tr}Admin Tresury Plugins{/tr}</h1>
	</div>

	<div class="body">
		{form}
			{formfeedback hash=$feedback}

			<table class="panel">
				<caption>{tr}Treasury Plugins{/tr}</caption>
				<tr>
					<th style="width:70%;">{tr}Plugin{/tr}</th>
					<th style="width:20%;">{tr}GUID{/tr}</th>
					<th style="width:20%;">{tr}Comments{/tr}</th>
					<th style="width:10%;">{tr}Active{/tr}</th>
				</tr>

				{foreach from=$gTreasurySystem->mPlugins item=plugin key=guid}
					<tr class="{cycle values="odd,even"}">
						<td>
							<h3>{$plugin.title|escape}</h3>
							<label for="{$guid}">
								{$plugin.description|escape}
							</label>
						</td>
						<td>{$guid}</td>
						<td style="text-align:center;">
							{assign var=comment value="treasury_`$guid`_comments"}
							<input type="checkbox" name="comments[{$guid}]" value="y" {if $gBitSystem->isFeatureActive($comment)}checked="checked"{/if} />
						</td>
						<td align="center">
							{if $plugin.is_active == 'x'}
								{tr}Missing{/tr}
							{elseif $guid == $smarty.const.TREASURY_DEFAULT_MIME_HANDLER}
								{tr}Default{/tr}
								<input type="hidden" name="plugins[{$guid}]" value="y" />
							{else}
								{html_checkboxes name="plugins[`$guid`]" values="y" checked=`$plugin.is_active` labels=false id=$guid}
							{/if}
						</td>
					</tr>
				{/foreach}
			</table>

			<div class="row submit">
				<input type="submit" name="pluginsave" value="{tr}Save Plugin Settings{/tr}" />
			</div>

			<div class="row">
				{formlabel label="Reset all plugin settings" for=""}
				{forminput}
					<input type="submit" name="reset_all_plugins" value="{tr}Reset Plugins{/tr}" />
					{formhelp note="This will remove all plugin settings from the database and reset them to the default values. This can be useful if some plugins don't seem to work or you simply want to reset all values on this page."}
				{/forminput}
			</div>
		{/form}

		{include file="bitpackage:treasury/admin_plugins_flv_inc.tpl"}
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
