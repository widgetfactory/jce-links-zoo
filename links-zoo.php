<?php
/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */

defined('_WF_EXT') or die('RESTRICTED');

class WFLinkBrowser_Zoo extends JObject
{
    public $_option = array('com_zoo');

    public function __construct($options = array())
    {
    }

    public function getInstance()
    {
        static $instance;

        if (!is_object($instance)) {
            $instance = new WFLinkBrowser_Zoo();
        }
        return $instance;
    }

    public function display()
    {
    }

    public function isEnabled()
    {
        $wf = WFEditorPlugin::getInstance();
        
        return $wf->checkAccess($wf->getName().'.links.zoolinks', 1);
    }

    public function getOption()
    {
		return $this->_option;
    }

    public function getList()
    {
        $wf = WFEditorPlugin::getInstance();
        
		$list = '';
		
		if ($wf->checkAccess($wf->getName().'.links.zoolinks', 1)) {
			$list = '<li id="index.php?option=com_zoo" class="folder nolink"><div class="uk-tree-row"><a href="#"><span class="uk-tree-icon folder content nolink"></span><span class="uk-tree-text">' . JText::_('PLG_JCE_LINKS_ZOO') . '</span></a></div></li>';
		}
		return $list;
    }

    public function getLinks($args)
    {
        $links 	= array();
		$view 	= isset($args->task) ? $args->task : '';
		
		// load config
		require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

		// get app
		$zoo = App::getInstance('zoo');

		switch ($view) {
			default:
				$apps = $zoo->table->application->find();

				foreach ($apps as $app) {
					$links[] = array(
						'id'		=>	'index.php?option=com_zoo&task=application&application_id=' . $app->id,
						'name'		=>	$app->name,
						'class'		=>	'folder application nolink'
					);
				}
				break;
			case 'application':			
				if (!$application = $zoo->table->application->get($args->application_id)) {
					return $links;
				}

				$categories = $application->getCategories(true);
				
				foreach ($categories as $category) {
					$url = 'index.php?option=com_zoo&task=category&category_id=' . $category->id;

					$links[] = array(
						'id'		=>	$url,
						'url'		=> 	$zoo->route->category($category, false),
						'name'		=>	$category->name . ' / ' . $category->alias,
						'class'		=>	'folder category'
					);
				}
				
				break;
			case 'category':
				if (!$category = $zoo->table->category->get($args->category_id)) {
					return $links;
				}	
			
				$categories = $category->getChildren();
				
				foreach ($categories as $category_item) {
					$url = 'index.php?option=com_zoo&task=category&category_id=' . $category_item->id;
					
					$links[] = array(
						'id'		=>	$url,
						'url'		=> 	$zoo->route->category($category, false),
						'name'		=>	$category_item->name . ' / ' . $category_item->alias,
						'class'		=>	'folder category'
					);
				}

				$items = $category->getItems(true);
					
				if (!empty($items)) {
					foreach ($items as $item) {

						$url = 'index.php?option=com_zoo&task=item&item_id=' . $item->id;

						$links[] = array(
							'id'		=>	$url,
							'url'		=> 	$zoo->route->item($item, false),
							'name'		=>	$item->name . ' / ' . $item->alias,
							'class'		=>	'file'
						);
					}	
				}

				break;
		}

		return $links;
	}
}