<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='fr-FR' lang='fr-FR' ng-app='application' ng-controller='main_ctrl'>
	<head>
		<title>Club-Manga V2</title>
		<meta name='description' content='{$site_description}' />
		<meta name='author' content='{$site_author}' />
		<meta name='keywords' content='{$site_keyword}' />
		<meta name='robots' content='none' />
		<meta http-equiv='content-type' content='text/html; charset=utf-8' />
		<meta name='viewport' content='width=device-width' />
		<link rel='alternate' hreflang='{$site_language}' href='?'>
		<link rel='icon' type='image/png' href='{$root_image}design/favicon.png'/>

		<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Roboto:400,500'/>
		<link rel='stylesheet' type='text/css' href='{$root_www}res/dist/min.css'/>
	</head>
	<body>
		<header>
			<nav>
				<a class='menu sidebar_call'>
					<img alt='Menu' src='{$root_image}buttons/menu.svg'/>
				</a>
				<div class='title'>
					Club-Manga <span>- Une passion, une communauté, un club</span>
				</div>
				<div id='search_barre'>
					<a>
						<img alt='Recherche' src='{$root_image}buttons/search.svg'/>
					</a>
					<input type='text' placeholder='Rechercher'/>
				</div>
			</nav>
		</header>

		<div id='sidebarre_background' class='background_mask'></div>

		<div id='sidebar'>
			<header data-url='{$root_www}login/'/>
				<a class='sidebar_call'>
					<img alt='Menu' src='{$root_image}buttons/back.svg'/>
				</a>
				<img class='avatar' src='{$root_image}buttons/avatar.svg'/>
				<p>Non connecté</p>
			</header>
			<div class='menu text'>
				<a href='{$root_www}'>
					<img alt='Accueil' src='{$root_image}buttons/home.svg'/>
					<div>Accueil</div>
				</a>
				<a href='{$root_www}forum/'>
					<img alt='Forum' src='{$root_image}buttons/forum.svg'/>
					<div>Forum</div>
				</a>
				<a href='{$root_www}wiki/'>
					<img alt='Wiki' src='{$root_image}buttons/wiki.svg'/>
					<div>Wiki</div>
				</a>
				<a href='{$root_www}game/'>
					<img alt='Jeux' src='{$root_image}buttons/game.svg'/>
					<div>Jeux</div>
				</a>
				<a href='{$root_www}inbox/'>
					<img alt='Messagerie' src='{$root_image}buttons/mailbox.svg'/>
					<div>Messagerie</div>
				</a>
				<a href='{$root_www}help/'>
					<img alt='Foire aux questions' src='{$root_image}buttons/faq.svg'/>
					<div>Foire aux questions</div>
				</a>
				<a href='{$root_www}team/'>
					<img alt='Equipe' src='{$root_image}buttons/team.svg'/>
					<div>Equipe</div>
				</a>
				<a href='{$root_www}contact/'>
					<img alt='Contact' src='{$root_image}buttons/contact.svg'/>
					<div>Contact</div>
				</a>
			</div>
		</div>

		<div id='page'>
			<div id='container' class='grid'>
				{$bloc_content}
			</div>
		</div>

		<footer></footer>

		<script src="{$root_www}res/dist/min.js"></script>

		{$debug_barre}

	</body>
</html>