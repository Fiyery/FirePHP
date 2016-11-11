<?php
/**
 * Hierarchy gère les hiérarchies récursives sur plusieurs niveaux.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Hierarchy
{
	/**
	 * Contient la liste 
	 * @var unknown
	 */
	private $_list = [];
	
	private $_list_hierarchied = [];
	
	private $_list_format = [];
	
	public function __construct($list)
	{
		$this->_list = $list;
	
		// Clé avec l'id pour retrouver facilement l'objet.
		$tmp = [];
		foreach ($this->_list as $c)
		{
			$tmp[$c->id] = $c;
		}
		$this->_list = $tmp;

		foreach($this->_list as $c)
		{
			$tmp = self::_search_parent($tmp, $c);
		}
		$this->_list_hierarchied = $tmp;
	}
	
	private static function _search_parent($list, $child, &$find = FALSE)
	{
		if ($child->id_parent === NULL)
		{
			return $list;
		}
		foreach($list as &$c)
		{
			if ($find)
			{
				continue;
			}
			if ($c->id === $child->id_parent)
			{
				if (isset($c->list) === FALSE)
				{
					$c->list = [];
				}
				$c->list[$child->id] = $child;
				$find = TRUE;
	
				// On supprime l'instance du tableau pour la placer au bon endroit.
				if (isset($list[$child->id]))
				{
					unset($list[$child->id]);
				}
			}
			elseif (isset($c->list))
			{
				$c->list = self::_search_parent($c->list, $child, $find);
				if ($find && isset($list[$child->id]))
				{
					unset($list[$child->id]);
				}
			}
		}
		return $list;
	}
	
	private static function _format_recursive($list, $level = 0)
	{
		$tmp = [];
		foreach ($list as $o)
		{
			$o->level = $level;
			$tmp[] = $o;
			if (isset($o->list))
			{
				$tmp = array_merge($tmp, self::_format_recursive($o->list, $level + 1));
				unset($o->list);
			}
		}
		return $tmp;
	}
	
	public function children($id)
	{
		$target = (isset($this->_list[$id])) ? ($this->_list[$id]) : (NULL);
		if ($target === NULL)
		{
			return [];
		}
	
		if (count($this->_list_format) === 0)
		{
			$this->_list_format = $this->_format_recursive($this->_list_hierarchied);
		}
	
		$children = [];
		$find_level = -1;
		foreach ($this->_list_format as $o)
		{
			if ($o->id === $target->id)
			{
				$find_level = $o->level;
			}
			else
			{
				if ($find_level > -1)
				{
					if ($o->level > $find_level)
					{
						$children[] = $o;
					}
					else
					{
						$find_level = -1;
					}
				}
			}
		}
		return $children;
	}
}
?>