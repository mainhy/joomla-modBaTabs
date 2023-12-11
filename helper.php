<?php
/**
* @Copyright   Copyright (C) 2010 - 2021 BestAddon . All rights reserved.
* @license     GNU General Public License version 2 or later
* @link        http://www.bestaddon.com
**/
defined('_JEXEC') or die;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\String\Normalise;
use Joomla\String\StringHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;

class modBaTabsHelper
{
    public static function getList(&$modData, &$params)
    {
        $app        = Factory::getApplication();
        self::getobj($modData, 'data-mode', $dataSelect);
        self::getobj($modData, $dataSelect, $jData);

        $catids = self::is($jData['catid']) ?: ["1","3","5","8","9"];
        $child_category = (int)self::is($jData['child_category']);
        //$levels = $jData['levels'];
        $author_filtering_type = (int)self::is($jData['author_filtering_type']);
        $created_by = self::is($jData['created_by']) ?: '';
        $excluded_articles = self::is($jData['article_ids']) ?: '';
        // Ordering
        $article_ordering = self::is($jData['article_ordering']) ?: 'a.ordering';
        $article_direction = self::is($jData['article_direction']) ?: 'ASC';
        $show_front = self::is($jData['show_front']) ?: 'show';
        $show_title = (int) self::is($jData['show_title']);
        $show_date = (int) self::is($jData['show_date']);
        $show_category = (int) self::is($jData['show_category']);
        $show_author = (int) self::is($jData['show_author']);
        $show_introtext = (int) self::is($jData['show_introtext']);
        $introtext_limit = (int) self::is($jData['introtext_limit']);
        $show_readmore = (int) self::is($jData['show_readmore']);
        $readmore_text = self::is($jData['readmore_text']) ?: 'Readmore';

        //joomla specific
        if ($dataSelect == "source-article") {
            if ((int)JVERSION >= 4) {
                $articles = $app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Site', ['ignore_request' => true]);
            } else {
                JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
                JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_content/models', 'ContentModel');
                $articles = JModelLegacy::getInstance('Articles', 'ContentModel', ['ignore_request' => true]);
            }
            // Set application parameters in model
            $appParams = method_exists($app, 'getParams') ? $app->getParams() : '';
            $articles->setState('params', $appParams);
            // Set the filters based on the module params
            $articles->setState('list.start', 0);
            $articles->setState('list.limit', (int) $jData['count']);
            $articles->setState('filter.published', 1);
            // Access filter
            $access = !ComponentHelper::getParams('com_content')->get('show_noauth');
            $authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
            $articles->setState('filter.access', $access);
            $articles->setState('filter.category_id.include', 1);
            $catids = !is_array($catids) ? explode(',', $catids) : $catids;
            // Category filter
            if ($child_category) {
                if ((int)JVERSION >= 4) {
                    $categories = $app->bootComponent('com_content')->getMVCFactory()->createModel('Categories', 'Site', ['ignore_request' => true]);
                } else {
                    $categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
                }
                $categories->setState('params', $appParams);
                $categories->setState('filter.get_children', 9999);
                $categories->setState('filter.published', 1);
                $categories->setState('filter.access', $access);
                $additional_catids = array();
                foreach ($catids as $catid) {
                    $categories->setState('filter.parentId', $catid);
                    $recursive = true;
                    $cats = $categories->getItems($recursive);
                    if ($cats) {
                        foreach ($cats as $category) {
                            $condition = (($category->level - $categories->getParent()->level) <= 9999);
                            if ($condition) {
                                $additional_catids[] = $category->id;
                            }
                        }
                    }
                }
                $catids = array_unique(array_merge($catids, $additional_catids));
            }
            $articles->setState('filter.category_id', empty($catids) ? null : $catids);

            // Ordering
            $articles->setState('list.ordering', $article_ordering);
            $articles->setState('list.direction', $article_direction);
            // New Parameters
            $articles->setState('filter.featured', $show_front);
            $articles->setState('filter.author_id', $created_by);
            $articles->setState('filter.author_id.include', $author_filtering_type);

            $items = $articles->getItems() ?: [];
            if (!empty($jData['article_ids'])) {
                $excluded_articles = explode(",", $jData['article_ids']);
                $items = array_filter($items, function ($item) use ($excluded_articles) {
                    return !in_array($item->id, $excluded_articles);
                });
            }

            // Prepare data for display using display options
            foreach ($items as $key => &$item) {
                $item->slug = $item->id . ':' . $item->alias;
                $item->catslug = $item->catid ? $item->catid . ':' . (isset($item->category_alias) ? $item->category_alias : '') : $item->catid;
                if ($access || in_array($item->access, $authorised)) { // We know that user has the privilege to view the article
                    $item->link = Route::_((int)JVERSION >= 4 ? RouteHelper::getArticleRoute($item->slug, $item->catslug) : ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
                } else {
                    // Angie Fixed Routing
                    $menu	= $app->getMenu();
                    $menuitems	= $menu->getItems('link', 'index.php?option=com_users&view=login');
                    if (isset($menuitems[0])) {
                        $Itemid = $menuitems[0]->id;
                    } elseif ($app->input->getInt('Itemid') > 0) { //use Itemid from requesting page only if there is no existing menu
                        $Itemid = $app->input->getInt('Itemid');
                    }
                    $item->link = Route::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
                }
                // Used for styling the active article
                $images = json_decode($item->images);
                if (isset($images) && !empty($images->image_fulltext)) {
                    $item->image = Uri::root() . $images->image_fulltext;
                    $item->imageAlt = $images->image_fulltext_alt;
                } elseif (isset($images) && !empty($images->image_intro)) {
                    $item->image = Uri::root() . $images->image_intro;
                    $item->imageAlt = $images->image_intro_alt;
                } else {
                    preg_match('/<img\s.*?\bsrc="(.*?)".*?>/si', $item->introtext, $matches);
                    $item->image = isset($matches[1]) ? Uri::root() . $matches[1] : '';
                    $item->imageAlt = '';
                }
                if ($item->catid) {
                    $item->displayCategoryLink  = Route::_((int)JVERSION >= 4 ? RouteHelper::getCategoryRoute($item->catid) : ContentHelperRoute::getCategoryRoute($item->catid));
                    $item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
                } else {
                    $item->displayCategoryTitle = $show_category ? $item->category_title : '';
                }
                $item->title = $show_title ? $item->title : '';
                $item->introtext = HTMLHelper::_('content.prepare', $item->introtext);
                if ((int)$introtext_limit > 0) {
                    $item->introtext = self::wordLimit(trim(strip_tags($item->introtext)), $introtext_limit);
                }
                $item->maintext = '<div class="ba-infor">' .
                ($show_category ? '<span class="ba-category">' . $item->displayCategoryTitle . '</span>' : '') .
                ($show_author ? '<span class="ba-author">' . (isset($item->author) ? $item->author : $item->author_name) . '</span>' : '') .
                ($show_date ? '<span class="ba-date">' . HTMLHelper::_('date', $item->created, 'Y-m-d') . '</span>' : '') . '</div>' .
                '<div>' . ($show_introtext > 0 ? $item->introtext : '') . '</div>' .
                ($show_readmore ? '<a class="ba-readmore btn btn-primary" href="' . $item->link . '"><span>' . $readmore_text . '</span></a>' : '');
            }
            return $items;
        }
    }

    /**
     * Use Ajax in admin to auto save the data
     */
    public static function baPreviewAjax()
    {
        $input = Factory::getApplication()->input;
        $ajaxData  = $input->getString('ba-form-content');
        $baData = json_decode($ajaxData, true);
        require dirname(__FILE__) . '/tmpl/layout.php';
    }

    /**
     * ADD YOUR ASSETS
     */
    public static function assets($path = '', $addRoot = true, $options = [], $attributes = [], $dependencies = [])
    {
        if (empty($path)) {
            return;
        }
        $doc = Factory::getDocument();
        $rootPath = dirname(__FILE__);
        $webPath = ((int)JVERSION >= 4 ? '' : Uri::root(true)) . '/modules/' . basename($rootPath);
        if (!is_array($path) && strpos($path, '{') !== false && strpos($path, '}') !== false) {
            if ($addRoot) {
                if ((int)JVERSION >= 4) {
                    $doc->getWebAssetManager()->addInlineStyle($path);
                } else {
                    $doc->addStyleDeclaration($path);
                }
            } else {
                echo '<style>' . $path . '</style>';
            }
        } else {
            $realPath = is_array($path) ? $path : glob($rootPath . $path . '{,*/,*/*/}{*.js,*.css}', GLOB_BRACE);
            foreach ($realPath as $key => $filename) {
                $basePath = is_array($path) ? $filename : $webPath . $path . (preg_match("/\.(js|jsx)$/", $filename) ? 'js/' : 'css/') . basename($filename);
                $baseName = is_array($path) ? $key : basename($rootPath) . '-' . basename($filename);
                if ($addRoot) {
                    if ((int)JVERSION >= 4) {
                        $wa = $doc->getWebAssetManager();
                        if (preg_match("/\.(js|jsx)$/", $filename)) {
                            if ($wa->assetExists('script', $baseName)) {
                                $wa->useScript($baseName);
                            } else {
                                $wa->registerAndUseScript($baseName, $basePath, $options, $attributes, $dependencies);
                            }
                        } else {
                            if ($wa->assetExists('style', $baseName)) {
                                $wa->useStyle($baseName);
                            } else {
                                $wa->registerAndUseStyle('ba-fontawesome', 'media/system/css/joomla-fontawesome.min.css');
                                $wa->registerAndUseStyle($baseName, $basePath, $options, $attributes, $dependencies);
                            }
                        }
                    } else {
                        if (preg_match("/\.(js|jsx)$/", $filename)) {
                            $doc->addScript(Uri::root(true) . $basePath);
                        } else {
                            $doc->addStyleSheet('//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css');
                            $doc->addStyleSheet(Uri::root(true) . $basePath);
                        }
                    }
                } else {
                    if (preg_match("/\.(js|jsx)$/", $filename)) {
                        echo '<script src="' . ((int)JVERSION >= 4 ? Uri::root() : '') . $basePath . '"></script>';
                    } else {
                        echo '<link href="' . ((int)JVERSION >= 4 ? Uri::root() : '') . $basePath . '" rel="stylesheet">';
                    }
                }
            }
        }
    }

    /**
     * Render a module by id in CONTENT
     */
    public static function isMod($content, $thisID)
    {
        $document = Factory::getDocument();
        // Find all instances of plugin and put in $matchesmodid for loadmoduleid
        preg_match_all('/{loadmoduleid\s([1-9][0-9]*)}/i', $content, $matchesmodid, PREG_SET_ORDER);
        // If no matches, skip this
        if ($matchesmodid) {
            foreach ($matchesmodid as $match) {
                if (!in_array((string)$thisID, $match)) {
                    $renderer = $document->loadRenderer('module');
                    $module = ModuleHelper::getModuleById(trim($match[1])); //$id = trim($match[1]);
                    $params = array('style' => 'none');
                    $output = $renderer->render($module, $params);
                } else {
                    $output = '<i style="color:red">Invalid ID: It is of the module!</i>';
                }
                // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
                if (($start = strpos($content, $match[0])) !== false) {
                    //substr@replace($content, $output, $start, strlen($match[0]));
                    $content = substr($content, 0, $start) . $output . substr($content, $start + strlen($match[0]));
                }
            }
        }
        return $content;
    }

    /**
     * Get a array with Recursive Arrays
     */
    public static function getobj($data, $isID, &$node)
    {
        if (isset($data) && !empty($data) && is_array($data)) {
            foreach ($data as $key => $item) {
                if (($key === $isID) || (isset($item['id']) && $item['id'] === $isID)) {
                    $node = $item;
                } elseif (is_array($item)) {
                    self::getobj($item, $isID, $node);
                }
            }
            return $node;
        }
    }

    /**
     * Cut string by specified by a number
     */
    public static function wordLimit($string, $word_limit)
    {
        $words = explode(' ', strip_tags($string));
        return implode(' ', array_splice($words, 0, $word_limit));
    }



    //////////////////////////////////
    ///////// BEGIN ADD STYLES ///////////////////////
    /////////////////////////////////
    public static function is(&$name, $val = '')
    {
        return !empty(is_string($name) ? trim($name) : $name) ? (!empty($val) ? $val : $name) : null;
    }
    public static function prop($attr, $prop, $prefix = '', $suffix = '', $hover = false)
    {
        $val = self::is($attr[$prefix . $prop . $suffix]);
        $val = strpos($val, '§§') ? explode('§§', $val) : $val;
        return is_array($val) ? ($hover ? self::is($val[1]) : self::is($val[0])) : $val;
    }
    public static function css($attr, $properties = [], $prefix = '', $suffix = '', $hover = false)
    {
        $output = '';
        $properties = is_array($properties) ? $properties : explode(',', $properties);
        foreach ($properties as $prop) {
            //$isProp = strtolower(preg@replace('/([a-z])([A-Z])/', '$1-$2', $prop));
            $isProp = strtolower(Normalise::toDashSeparated(Normalise::fromCamelCase($prop)));
            $val = self::prop($attr, $prop, $prefix, $suffix, $hover);
            $splitGroupVal = str_replace("||", " ", $val);
            if (strpos($isProp, '-image') !== false) {
                $output .= self::is($val, $isProp . ':url(' . $splitGroupVal . ');');
            } elseif (strpos($isProp, '-shadow') !== false) {
                $shadows = explode('||', $val);
                foreach ($shadows as &$item) {
                    $item = ($item === end($shadows) ? ($item ? 'inset' : '') : (empty(trim($item)) ? 0 : $item));
                }
                $output .= $isProp . ':' . implode(" ", $shadows) . ';';
            } else {
                if (preg_match('/background|color/i', $isProp)) {
                    $output .= self::is($val, '--ba-' . $isProp . ':' . $splitGroupVal . ';');
                }
                $output .= trim($splitGroupVal) != '' ? self::is($val, $isProp . ':' . $splitGroupVal . ';') : '';
            }
        }
        return $output;
    }
    public static function bg($attr, $prefix = '', $suffix = '', $hover = false)
    {
        $output = '';
        $bgType = self::prop($attr, 'backgroundType', $prefix, $suffix, $hover);
        if ($bgType == 'color') {
            $output .= self::css($attr, ['backgroundColor'], $prefix, $suffix, $hover);
        }
        if ($bgType == 'gradient') {
            $props = ["gradientStartColor","gradientStartPoint","gradientEndColor","gradientEndPoint","gradientType","gradientAngle","gradientPosition"];
            foreach ($props as &$value) {
                $value = self::prop($attr, $value, $prefix, $suffix, $hover);
            }
            $output .= 'background:' . $props[4] . '-gradient(' . ($props[4] == 'linear' ? $props[5] : 'at ' . $props[6]) . ',' . $props[0] . ' ' . $props[1] . ',' . $props[2] . ' ' . $props[3] . ')' . ';';
        }
        if ($bgType == 'image') {
            $output .= self::css($attr, ['backgroundImage','backgroundSize','backgroundPosition', 'backgroundAttachment','backgroundRepeat'], $prefix, $suffix, $hover);
        }
        return $output;
    }
    //////////////////////////////
    ///////// END ADD STYLES ///////////////////////
    /////////////////////////////
}
