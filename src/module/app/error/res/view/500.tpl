<h1>Erreur 500</h1>

{if isset($error_msg)}
	<table>
		<tr>
			<th>Message</th>
			<td>{$error_msg}</td>
		</tr>
		<tr>
			<th>Code</th>
			<td>{$error_code}</td>
		</tr>
		<tr>
			<th>Fichier</th>
			<td>{$error_file}:{$error_line}</td>
		</tr>
		<tr>
			<th>Trace</th>
			<td>
				<table>
					<thead>
						<tr>
							<th>Ficher</th>
							<th>Ligne</th>
							<th>Classe</th>
							<th>Type d'appel</th>
							<th>Fonction</th>
							<th>Paramètre(s)</th>
						</tr>
					</thead>
					{foreach $error_trace as $trace}
						<tr>
							<td>{$trace.file}</td>
							<td>{$trace.line}</td>
							<td>{if isset($trace.class)} {$trace.class} {/if}</td>
							<td>{if isset($trace.type)} {$trace.type} {/if}</td>
							<td>{$trace.function}()</td>
							<td>
								<ol>
									{foreach $trace.args as $arg}
										<li>
											{if is_array($arg)} 
												#Array
											{else}
												{if is_object($arg)} 
													#Object
												{else}
													{$arg}
												{/if}
											{/if}
										</li>
									{/foreach}
								</ol>
							</td>
						</tr>
					{/foreach}
				</table>
				
			</td>
		</tr>
		<tr>
	</table>
{else}
	Une erreur inconnue est survenue empêchant le fonctionnement normal.
{/if}