<table class="table table-striped">
	<tr><th colspan="2">Template Lite Debug Console</th></tr>
	<tr><th colspan="2"><b>Included templates (load time in seconds):</b></th></tr>
	<!--{foreach key=key value=templates from=$_debug_tpls}-->
	<tr>
		<td colspan="2">
			<!--{for start=0 stop=$_debug_tpls[$key].depth}-->&nbsp;&nbsp;&nbsp;&nbsp;<!--{/for}-->
			<font color=<!--{if $_debug_tpls[$key].type eq "template"}-->brown<!--{elseif $_debug_tpls[$key].type eq "insert"}-->black<!--{else}-->green<!--{/if}-->>
			<!--{$_debug_tpls[$key].filename}--></font>
			<!--{if isset($_debug_tpls[$key].exec_time)}-->
			(<!--{$_debug_tpls[$key].exec_time|string_format:"%.5f"}--> seconds)
			<!--{if $key eq 0}--> (total)<!--{/if}-->
			<!--{/if}-->
		</td>
	</tr>
	<!--{foreachelse}-->
	<tr><td colspan="2"><i>No template assigned</i></td></tr>
	<!--{/foreach}-->
	<tr><td colspan="2"><b>Assigned template variables:</b></td></tr>
	<!--{foreach key=key value=vars from=$_debug_keys}-->
	<tr>
		<td width="120px"><font color="blue">{$<!--{$_debug_keys[$key]}-->}</font></td>
		<td><font color="green"><!--{$_debug_vals[$key]|print_var}--></font></td>
	</tr>
	<!--{foreachelse}-->
	<tr><td colspan="2"><i>No template variables assigned</i></td></tr>
	<!--{/foreach}-->
</table>
