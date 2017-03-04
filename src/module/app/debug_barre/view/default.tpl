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
				<div class="debug_tool_echo">
                    Affichage erreur : <br/>
                    {foreach $errors as $e}
                        {if $e.type === "Exception"}
                            <div style="color:red">
                                <b>{$e.file}</b> : {$e.line} {$e.string}
                            </div>
                        {else}
                            <div>
                                <b>{$e.file}</b> : {$e.line} {$e.string}
                            </div>
                        {/if}
                    {/foreach}
                </div>

                <div class='debug_tool_echo'>
                    Affichage echo / print : <br/>
                    {$echos}
                </div>
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
				<table class='table_debug'>
                    <thead>
                        <tr><th>N°</th>
                        <th>Temps (ms)</th>
                        <th>SQL</th></tr>
                    </thead>
                    <tbody>
                        {foreach $queries as $s}
                            <tr>
                                <td>{$s.num}</td>
                                <td>{$s.time}</td>
                                <td>{$s.sql}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
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
					<table class='table_debug'>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Valeur</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $session as $s}
                                <tr>
                                    <td>{$s.name}</td>
                                    <td>{$s.value}</td>
                                    <td>{$s.type}</td>
                                </tr>
                            {/foreach}
                        </tbody>
					</table>
				</div>
				<div>
					<div class='title'>GET</div>
					<table class='table_debug'>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Valeur</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $get as $s}
                                <tr>
                                    <td>{$s.name}</td>
                                    <td>{$s.value}</td>
                                    <td>{$s.type}</td>
                                </tr>
                            {/foreach}
                        </tbody>
					</table>
				</div>
				<div>
					<div class='title'>POST</div>
					<table class='table_debug'>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Valeur</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $post as $s}
                                <tr>
                                    <td>{$s.name}</td>
                                    <td>{$s.value}</td>
                                    <td>{$s.type}</td>
                                </tr>
                            {/foreach}
                        </tbody>
					</table>
				</div>
			</div>
		</div>
		<div class='bloc' id='app_call'>
			{$app_controller}/{$app_module}/{$app_action}
		</div>
	</div>
</div>