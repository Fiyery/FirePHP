<style type='text/css'>
	{$css}
</style>

<div id='debug_tool'>
	<div id='debug_barre'>
		<div class='bloc' id='debug_barre_title'>
			Debug Barre
		</div>
		<div class='bloc' id='debug_barre_console'>
			<div class='trigger'>
				<div class='label'>
					<img src='{$console_image}'/>
				</div>
			</div>
			<div class='bloc_info'>
				{$error}
				{$echo}
			</div>
		</div>
		<div class='bloc' id='debug_barre_time'>
			<div class='trigger'>
				<div class='label'>
					<img src='{$time_image}'/>
				</div>
				<div class='value'>{$time} ms</div>
			</div>
			<div class='bloc_info'>
				<label>Total temps serveur</label>
				<div>{$time} ms</div>
			</div>
		</div>
		<div class='bloc' id='debug_barre_base'>
			<div class='trigger'>
				<div class='label'>
					<img src='{$base_image}'/>
				</div>
				<div class='value'>{$query_count} q ({$query_time} ms)</div>
			</div>
			<div id='debug_tool_base' class='bloc_info'>
				<label>Compteur requête</label>
				<div>{$query_count}</div>
				<label>Temps requête</label>
				<div>{$query_time} ms</div>
				{$history_sql}
			</div>
		</div>
		<div class='bloc' id='debug_barre_memory'>
			<div class='trigger'>
				<div class='label'>
					<img src='{$memory_image}'/>
				</div>
				<div class='value'>{$memory_usage}</div>
			</div>
			<div id='debug_tool_memory' class='bloc_info'>
				<label>Mémoire max</label>
				<div>{$memory_limit}</div>
				<label>Mémoire utilisée</label>
				<div>{$memory_usage}</div>
			</div>
		</div>
		<div class='bloc' id='debug_barre_server'>
			<div class='trigger'>
				<div class='label'>
					<img src='{$server_image}'/>
				</div>
				<div class='value'>{$php_version}</div>
			</div>
			<div id='debug_tool_server' class='bloc_info'>
				<label>PHP version</label>
				<div>{$php_version}</div>
				<label>Apache version</label>
				<div>{$apache_version}</div>
				<label>Base version</label>
				<div>{$base_version}</div>
				<label>IP serveur</label>
				<div>{$ip_server}</div>
				<label>Nom serveur</label>
				<div>{$name_server}</div>
				<label>Cache</label>
				<div>{$cache_active}</div>
			</div>
		</div>
		<div class='bloc' id='debug_barre_env'>
			<div class='trigger'>
				<div class='label'>
					<img src='{$var_image}'/>
				</div>
			</div>
			<div id='debug_tool_env' class='bloc_info'>
				<div>
					<div class='title'>SESSION</div>
					{$session_vars}
				</div>
				<div>
					<div class='title'>GET</div>
					{$get_vars}
				</div>
				<div>
					<div class='title'>POST</div>
					{$post_vars}
				</div>
			</div>
		</div>
	</div>
</div>