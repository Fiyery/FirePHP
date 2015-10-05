<style type='text/css'>
	{$css}
</style>

<script type='text/javascript'>
	{$javascript}
</script>

<div id='debug_tool'>

	<div id='debug_tool_console' class='bloc_info'>
		{$error}
		{$echo}
	</div>
	

	<div id='debug_tool_time' class='bloc_info'>
		<label>Total time server</label>
		<div>{$time} ms</div>
	</div>

	<div id='debug_tool_base' class='bloc_info'>
		<label>Query count</label>
		<div>{$query_count}</div>
		<label>Query time</label>
		<div>{$query_time} ms</div>
		{$history_sql}
	</div>

	<div id='debug_tool_memory' class='bloc_info'>
		<label>Memory limit</label>
		<div>{$memory_limit}</div>
		<label>Memory usage</label>
		<div>{$memory_usage}</div>
	</div>

	<div id='debug_tool_server' class='bloc_info'>
		<label>PHP version</label>
		<div>{$php_version}</div>
		<label>Apache version</label>
		<div>{$apache_version}</div>
		<label>Db version</label>
		<div>{$base_version}</div>
		<label>Ip server</label>
		<div>{$ip_server}</div>
		<label>Name server</label>
		<div>{$name_server}</div>
	</div>

	<div id='debug_barre'>
		<div class='bloc' id='debug_barre_title'>
			Debug Barre
		</div>
		<div class='bloc' id='debug_barre_console'>
			<div class='label'>
				<img src='{$console_image}'/>
			</div>
		</div>
		<div class='bloc' id='debug_barre_time'>
			<div class='label'>
				<img src='{$time_image}'/>
			</div>
			<div class='value'>{$time} ms</div>
		</div>
		<div class='bloc' id='debug_barre_base'>
			<div class='label'>
				<img src='{$base_image}'/>
			</div>
			<div class='value'>{$query_count} q ({$query_time} ms)</div>
		</div>
		<div class='bloc' id='debug_barre_memory'>
			<div class='label'>
				<img src='{$memory_image}'/>
			</div>
			<div class='value'>{$memory_usage}</div>
		</div>
		<div class='bloc' id='debug_barre_server'>
			<div class='label'>
				<img src='{$server_image}'/>
			</div>
			<div class='value'>{$php_version}</div>
		</div>
		<div class='bloc' id='debug_barre_var'>
			<div class='label'>
				<img src='{$var_image}'/>
			</div>
		</div>
	</div>

	
</div>