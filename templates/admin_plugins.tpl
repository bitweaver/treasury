{strip}
<div class="admin treasury">
	<div class="header">
		<h1>{tr}Admin Tresaury Plugins{/tr}</h1>
	</div>

	<div class="body">
		{form}
			{formfeedback hash=$feedback}

			<table class="panel">
				<caption>{tr}Treasury Plugins{/tr}</caption>
				<tr>
					<th style="width:70%;">{tr}Plugin{/tr}</th>
					<th style="width:20%;">{tr}GUID{/tr}</th>
					<th style="width:10%;">{tr}Active{/tr}</th>
				</tr>

				{foreach from=$gTreasurySystem->mPlugins item=plugin key=guid}
					<tr class="{cycle values="odd,even"}">
						<td>
							<h3>{$plugin.title|escape}</h3>
							<label for="{$guid}">
								{$plugin.plugin_description|escape}
							</label>
						</td>
						<td>{$guid}</td>
						{if $plugin_type eq 'format'}
							<td align="center">{if $plugin.is_active == 'y'}{html_radios values=$guid name="default_format" checked=$gBitSystem->getConfig('default_format')}{/if}</td>
						{/if}
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
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->

{/strip}
