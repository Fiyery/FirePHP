<?php
class CrawlerAnimeSaijou extends Crawler
{
	public function get_animes()
	{
		$html = $this->scan('http://www.anime-saijou.com/');
		$html = substr($html, strpos('<div id = "contents">', $html));
		$html_query = new HTMLQuery($html);
		$list = $html_query->get_elements_by_css_path('#contents .news_and_comments .news');
		$animes = array();
		foreach ($list as $html)
		{
			$html_query = new HTMLQuery($html);
			$name = $html_query->get_elements_by_css_path('table .title_news a strong');
			if (isset($name[0]))
			{
				$name = $this->_parse_name($name[0]);
				$date = $html_query->get_elements_by_css_path('.date_news');
				if (isset($date[0]))
				{
					$date = $this->_parse_date($date[0]);
					$episode = $html_query->get_elements_by_css_path('.episode_news');
					if (isset($episode[0]))
					{
						$episode = $this->_parse_episode($episode[0]);
						$animes[] = array('name' => $name, 'date' => $date, 'episode' => $episode);
					}
				}
			}
		}
		var_dump($animes);
	}
	
	private function _parse_name($name)
	{
		return preg_replace('#(<[^>]*>)#', '', $name);
	}
	
	private function _parse_episode($episode)
	{
		return preg_replace('#[^0-9]+#', '', preg_replace('#(<[^>]*>)#', '', $episode));
	}
	
	private function _parse_date($date)
	{
		$date = trim(preg_replace('#(<[^>]*>)#', '', $date));
		$pos = max(stripos($date, 'pm'), stripos($date, 'am'));
		$hour = substr($date, $pos-9, 2);
		if (stripos($date, 'pm') !== FALSE)
		{
			$hour = substr($date, $pos-9, 2)+12;
			$hour = ($hour == 24) ? ($hour - 12) : ($hour);
		}
		else 
		{
			$hour = substr($date, $pos-9, 2);
		}	
		$min = substr($date, $pos-6, 2);
		$sec = substr($date, $pos-3, 2);
		if (stripos($date, "Aujourd'hui") !== FALSE)
		{
			$day = date('d');
		}
		elseif (stripos($date, "Hier") !== FALSE)
		{
			$day = date('d') - 1;
		}
		$date = mktime($hour, $min, $sec, date('m'), $day, date('Y'));
		$date = date('d/m/Y H:i:s', $date);
		return $date;
	}
}
?>