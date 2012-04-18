<?php
/**
 *
 * License, TERMS and CONDITIONS
 *
 * This software is lisensed under the GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * Please read the license here : http://www.gnu.org/licenses/lgpl-3.0.txt
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * ATTRIBUTION REQUIRED
 * 4. All web pages generated by the use of this software, or at least
 * 	  the page that lists the recent questions (usually home page) must include
 *    a link to the http://www.lampcms.com and text of the link must indicate that
 *    the website\'s Questions/Answers functionality is powered by lampcms.com
 *    An example of acceptable link would be "Powered by <a href="http://www.lampcms.com">LampCMS</a>"
 *    The location of the link is not important, it can be in the footer of the page
 *    but it must not be hidden by style attibutes
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This product includes GeoLite data created by MaxMind,
 *  available from http://www.maxmind.com/
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2011 (or current year) ExamNotes.net inc.
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * @link       http://www.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */


namespace Lampcms\Category;

use Lampcms\Registry;

/**
 * Class for rendering
 * various html strings
 * from array of categories.
 * Used for making drop-down menu
 * with categories,
 * breadcrumb naviations,
 * html for nested <ul><li> for the
 * HTML of sub-categories
 * for one category for the
 * 'categories' block of the category page view
 *
 * @author Dmitri Snytkine
 *
 */
class Renderer
{
	/**
	 * Registry object
	 *
	 * @var Object of type Lampcms\Registry
	 */
	protected $Registry;

	/**
	 * Array of categories
	 * rekeyd by 'id'
	 * and ordered by i_parent, i_weight
	 * so root categories are first
	 * in array, then ordering by i_weight, lowest weight first
	 *
	 * @var array
	 */
	protected $aCategories = array();

	protected $ul = '';

	/**
	 * Id of category
	 * this should be rendered as "selected"
	 * inside the categories menu html
	 *
	 * @var int
	 */
	protected $selectedId;

	/**
	 * Separator string used
	 * for separating links
	 * inside the breadcrumb
	 * The value is defined in the
	 * !config.ini as CATEGORY_SEPARATOR
	 * @var string
	 */
	protected $sep;

	/**
	 *
	 * Maximum level of nesting
	 * after this level the tplCategoryMinDiv template
	 * is used for rendering nested category.
	 * This template has fewer details to render, usually used
	 * to render deep nested categories.
	 * This comes from the settings in !config.ini file
	 * CATEGORY_DETAILED_LEVEL
	 *
	 * @var int
	 */
	protected $maxDetailedLevel;

	protected $latestQuestion;

	/**
	 * Translated work "Question"
	 *
	 * @var string
	 */
	protected $labelQuestion;

	/**
	 * Translated work "Answers"
	 *
	 * @var string
	 */
	protected $labelAnswer;

	/**
	 * Constructor
	 * @param Registry $Registry
	 */
	public function __construct(Registry $Registry){
		$this->Registry = $Registry;
		$c = $this->Registry->Cache->categories;
		if(is_array($c)){
			$this->aCategories = $c;
		}
		$this->maxDetailedLevel = $this->Registry->Ini->CATEGORY_DETAILED_LEVEL;
		$this->latestQuestion = $this->Registry->Tr->get('Latest Question');
		$this->labelQuestion = $this->Registry->Tr->get('Question');
		$this->labelAnswer = $this->Registry->Tr->get('Answer');

		d('$this->aCategories: '.print_r($this->aCategories, 1));

	}

	public function getCategories(){
		return $this->aCategories;
	}

	/**
	 * If passed array of category data
	 * has 'a_sub' key with array of sub-categories
	 * then add an extra element ['childred'] to it
	 * and populate with arrays of normalized
	 * category data
	 *
	 *
	 * @param int category id
	 *
	 * @return array, which may have tested
	 * array of children, each child may have nested children, etc.
	 */
	public function getNormalizedCategory($id){
		$ret = $this->aCategories[$id];

		if( !empty($ret['a_sub']) ){
			$tmp = array();
			foreach($ret['a_sub'] as $subId){
				$tmp[] = $this->getNormalizedCategory($subId);
			}

			$children = \array_sort($tmp, function($v, $a){

			});

			unset($ret['a_sub']);
			$ret['children'] = $children;

		}

		return $ret;
	}



	public function getByFilter($func){
		$a = array_filter($this->aCategories, $func);

		return $a;
	}


	/**
	 * HTML for the nested sortable
	 * page
	 *
	 *
	 * @param $func The function will be used
	 * as array_filter function. It will be passed to array_filter
	 * on the $this->aCategories and will return array
	 * of categories to iterate over "this time"
	 * This function may then recurse but pass different
	 * function so a different set of categories are selected
	 * (usually child nodes of the currently processed category)
	 * That callback function will be a closure, encapsulating
	 * the value of a_sub array of the currently processed category
	 */
	public function getSortableList($func = null){
		$olStart = '';
		$olEnd = '';

		if(is_callable($func)){
		 $olStart = "\n<ol class=\"sub\">";
		 $olEnd = '</ol>';
		} else {
			$func = function($var){
				return ($var['i_parent'] === 0);
			};
		}

		$a = array_filter($this->aCategories, $func);


		$ret = "\n$olStart";
		if(!empty($a)){
			foreach($a as $cat){
				$subList = '';
				if(!empty($cat['a_sub'])){
					$subs = $cat['a_sub'];
					$pid = 	$cat['id'];
					d('pid: '.var_export($pid, true).' has subs: '.print_r($subs, 1));
					$func = function($var) use($subs, $pid) {
						return (in_array($var['id'], $subs) && (array_key_exists('i_parent', $var))  &&  ($var['i_parent'] === $pid)); //&& (array_key_exists('i_parent', $var))  &&  ($var['i_parent'] === $pid)
					};

					$cat['subs'] = $this->getSortableList($func);
				}

				$ret .= "\n".\tplSortedCategory::parse($cat);
			}
		}

		$ret .= "\n$olEnd";

		return $ret;
	}


	/**
	 * Get HTML for the breadcrumn of the category
	 * Bread crumb is
	 * Category > SubCategory > This category
	 * Where This category is passed as $categoryID
	 * and Category and Subcategory are
	 * parents of this category and they will both
	 * be links, while This category is not always
	 * a link.
	 *
	 * @param int $id id of current category
	 * @param bool $isLink if true then the category
	 * with $categoryId is also a link, otherwise not a link.
	 * @param string $prev do not pass anything here yourself
	 * it's used only when function recursively calls itself
	 *
	 */
	public function getBreadCrumb($id, $isLink = true, $prev = ''){
		if(!isset($this->sep)){
			$this->sep = $this->Registry->Ini->CATEGORY_SEPARATOR;
		}

		/**
		 * If the category has been deleted, the
		 * old questions may still have category_id
		 * of deleted category.
		 * This is why we must check if category with this id exists,
		 * if not then just return empty string - otherwise we would
		 * be php error 'undefined offset'
		 */
		if(!array_key_exists($id, $this->aCategories)){
			return '';
		}

		$categ = $this->aCategories[$id];

		$tpl = '<a href="/category/%s/" class="bc_categ">%s</a>';
		$home = '<a href="%s" class="bc_categ bc_home">%s</a>';

		if($isLink){
			$res = \sprintf($tpl, $categ['slug'], $categ['title'] );
		} else {
			$res = '<span class="bc_current">'.$categ['title'].'</span>';
		}

		$res = $res.$prev;
		if(!empty($categ['i_parent'])){
			$parent = $this->aCategories[$categ['i_parent']];

			return $this->getBreadCrumb($parent['id'], true, $this->sep.$res);
		} else {
			$home = \sprintf($home, $this->Registry->Ini->SITE_URL, $this->Registry->Tr->get('Home'));

			return '<div class="bcnav">'.$home.$this->sep.$res.'</div>';
		}
	}


	/**
	 * Get HTML of the select menu
	 * with categories
	 *
	 * @return html of the select input
	 */
	public function getSelectMenu($selected = 0, $addEmptyItem = null, $required = true){
		if(!is_int($selected)){
			throw new \InvalidArgumentException('Invalid type of $selected param. Must be int, was: '.gettype($selected));
		}

		if(empty($this->aCategories)){
			return '';
		}

		$id = "categories_menu";
		$this->selectedId = $selected;
		if($addEmptyItem && $required && 0 === $selected){
			$required =  ' required';
		}
		$ul = "\n<select id=\"$id\" name=\"category\" class=\"csmenu\" $required>";
		if(is_string($addEmptyItem)){
			$ul .= '<option value="">'.$addEmptyItem.'</option>';
		}

		foreach($this->aCategories as $category){
			if(0 === $category['i_parent'] && $category['b_active']){
				$ul .= $this->getSelectOption($category);
			}
		}

		$ul .= "\n<select>";

		return $ul;
	}


	public function getSelectOption(array $category, $level = 0){
		if(!$category['b_active']){
			return '';
		}

		$tpl = "\n".'<option value="%s"%s %s>%s</option>';
		$selected = '';
		$title = $category['title'];

		if($this->selectedId === $category['id']){
			$selected = ' selected="selected"';
		}

		$disabled = ('true' != $category['b_catonly']) ? '' : 'disabled';
		$title = \str_repeat("&nbsp;&nbsp;&nbsp;", $level).$title;

		$ret = \sprintf($tpl, $category['id'], $selected, $disabled, $title);
		if(!empty($category['a_sub'])){
			foreach($category['a_sub'] as $id){
				/**
				 * Extra check just in case the parent category
				 * has been deleted but the item not removed
				 * from a_sub array (normally this does not happend, but just in case must check)
				 */
				if(array_key_exists($id, $this->aCategories)){
					$ret .= $this->getSelectOption($this->aCategories[$id], ($level + 1));
				}
			}
		}

		return $ret;
	}

	/**
	 * Get array of categories whose parent id is
	 * $id and also sort then by 'weight'
	 * actually the subs are already sorted by weight.
	 *
	 *
	 * @param int $id id of category for which
	 * we want to find array of sub-categories
	 *
	 * @param bool $activeOnly if true (default) then remores
	 * non-active categories from result array
	 *
	 * @return mixed null | array of sub-categories result may also
	 * be an empty array if all sub-categories are not active
	 */
	public function getSubCategoriesOf($id, $activeOnly = true){
		if(!empty($this->aCategories[$id]) && !empty($this->aCategories[$id]['a_sub'])){

			$subKeys = array_flip($this->aCategories[$id]['a_sub']);

			$ret = array_intersect_key($this->aCategories, $subKeys);
			if($activeOnly){
				return array_filter($ret, function($var){
					return $var['b_active'];
				});
			}

			return $ret;
		}

		return null;
	}


	public function _getNestedDivs(array $categories = null){

		$categories = ($categories) ? $categories : $this->aCategories;

		$ret = '<div class="cats2">';
		foreach($categories as $c){
			if($c['b_active']){
				if(!empty($c['a_sub'])){
					$subs = array_intersect_key($this->aCategories, array_flip($c['a_sub']));
					$ret .= $this->getNestedDivs($subs);
				} else {
					$ret .= \tplCategoryDiv::parse($c);
				}
			}
		}

		$ret .= "\n</div>";

		return $ret;
	}

	/**
	 *
	 * Create html for showing div with categories
	 * where each category may have own nested group
	 * of sub-categories.
	 *
	 * @param array $categories
	 * @param int $parentId parent_id do not pass this manually, used during recursion
	 * @param int $level level of nesting of groups
	 * of sub-categories currentlyl being parsed.
	 * Do not pass this manually, used only during recursion
	 */
	public function getNestedDivs(array $categories = null, $parentId = 0, $level = 0){

		$categories = ($categories) ? $categories : $this->aCategories;

		//print_r($categories);
		//exit;

		$ret = '<div class="cats_w fl cb" id="parent_'.$parentId.'">';
		foreach($categories as $c){
			if($c['b_active'] && $c['i_parent'] === $parentId){
				$c['latest_label'] = $this->latestQuestion;
				
				if(!empty($c['a_sub'])){
					$subs = \array_intersect_key($this->aCategories, \array_flip($c['a_sub']));
					$c['subs'] = $this->getNestedDivs($subs, $c['id']);
				} 

				/**
				 * Here can use value of $level
				 * to use different template when level > some preset
				 * level after which we just want a very basic template
				 * with just a link to sub category
				 */
				$tpl = ($c['i_level'] < $this->maxDetailedLevel) ? 'tplCategoryDiv' : 'tplCategoryMinDiv';

				$ret .= $tpl::parse($c, true);
				

			}
		}

		$ret .= "\n</div> <!-- // div parent $parentId -->\n";

		return $ret;
	}

}

