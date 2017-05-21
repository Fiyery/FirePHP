{if !isset($ajax)}
	<!DOCTYPE html>	
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr-FR" lang="fr-FR">
		<head>
			<title>{$site_title}</title>
			<meta name='description' content='{$site_description}' />
			<meta name='author' content='{$site_author}' />
			<meta name='keywords' content='{$site_keyword}' />
			<meta name='robots' content='all' />
			<meta http-equiv='content-type' content='text/html; charset=utf-8' />
			<link rel="alternate" hreflang="{$site_language}" href="?">
			<link rel='icon' type='image/x-icon' href='{$root_image}design/favicon.ico'/>
			<meta name="viewport" content="width=device-width" />
			{if isset($bloc_css)}
				{$bloc_css} 
			{/if}
		</head>
		<body>
			<header>
				<nav></nav>
			</header>

			{if isset($bloc_messages)}	
				<div id='zonemessage'>
					{foreach $bloc_messages as $m}
						<div class='{$m.type}'>{$m.message}</div>
					{/foreach}
				</div>
			{/if}
	
			{$bloc_content}
			
			<footer></footer>
			
			{if isset($bloc_javascript)}
				{$bloc_javascript} 
			{/if}

			{$debug_barre}
		</body>
	</html>
{else}
	{$ajax}
{/if}