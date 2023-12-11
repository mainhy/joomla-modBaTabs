<?php
/**
* @Copyright   Copyright (C) 2010 BestAddon . All rights reserved.
* @license     GNU General Public License version 2 or later
* @link        http://www.bestaddon.com
**/
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

require_once(dirname(__FILE__).'/form-helper.php');
class BestFormRender
{
    //////////////////// BEGIN CLASS ////////////////////
    use BestAddonFormElements;
    
    //////////////////////////////////
    ///////// BEGIN DATA ///////////////////////
    /////////////////////////////////
    public static function renderData($node)
    {
        return $node['id'] == 'source-basic' ? self::renderDataBasic($node) : self::renderDataArticle($node);
    }

    public static function renderDataBasic($node)
    {
        //self::input('image', self::l('IMAGE'), ($values['image']?:''), 'class="ba-input width-lg" data-rel="media"')
        //self::select('icon', self::l('ICON'), self::fontAwesome(), 'data-rel="dropdown" title="fontWeASome"', (array)($values['icon']?:''))
        $html = '<header class="ba-header panel-heading clearfix"><i class="btn ba__basic-add">'.self::l('ADD_NEW_ITEM').'</i></header><div class="ba__basic_data">';
        foreach (isset($node['children']) && is_array($node['children']) ? $node['children'] : [] as $i => $values) {
            $html .= '<div class="ba-group accordion-basic clearfix"><h3 class="panel-heading"><i class="ba__move">&nbsp;</i><span>'.$values['header'].'</span><i class="ba__button ba__edit tooltip" title="Edit">&#x270E;</i><i class="ba__button ba__clone tooltip" title="Clone">&#x2398</i><i class="ba__button ba__remove tooltip" title="Remove">&times;</i></h3><div class="panel-body"><div class="ba-controls clearfix">'.'
            '.self::input('header', self::l('TITLE'), (!empty($values['header'])?$values['header']:'')).'
            '.self::select('icon', self::l('TITLE').' '.self::l('ICON'), self::fontAwesome(1), 'data-rel="dropdown" title="fontWeASome"', (array)(!empty($values['icon'])?$values['icon']:'fab fa-None')).'   
            '.self::textarea('main', self::l('CONTENT', 1), (!empty($values['main'])?$values['main']:''), 'class="ba-input ba-editor"').'
            </div></div></div>';
        }
        $html .='</div>';
        return $html;
    }
    public static function renderDataArticle($node)
    {
        //JModelLegacy::addIncludePath(JPATH_BASE.'/components/com_content/models', 'ContentModel');
        return '<div class="ba-control clearfix">
                    <label>'.self::l('SELECT_CATEGORY', 1).'</label>
                    '.(function_exists('__') ? preg_replace('/<select/', '<select data-name="catid"', wp_dropdown_categories(['name'=>'catid','class'=>'ba-input','echo'=>0])) : '<select class="ba-input" data-name="catid" multiple="multiple"><option value="">- '.self::l('ALL_CATEGORIES').' -</option>'.HTMLHelper::_('select.options', HTMLHelper::_('category.options', 'com_content')).'</select>').'                    
                </div>'.'
                '.self::select('child_category', self::l('CHILD_CATEGORY', 1), ["1"=>self::l('YES'),"0"=>self::l('NO')], 'data-rel="button"').'
                '.self::select('author_filtering_type', self::l('AUTHOR_FILTERING', 1), ["1"=>self::l('INCLUSIVE'),"0"=>self::l('EXCLUSIVE')], 'data-rel="button"').'
                '.'<div class="ba-control clearfix">
                    <label>'.self::l('AUTHORS', 1).'</label>
                    <select class="ba-input" data-name="created_by" multiple="multiple">
                        <option value="">- '.Text::_('JNONE').' -</option>
                        '.HTMLHelper::_('select.options', self::getAuthors()).'
                    </select>
                </div>'.'
                '.self::input('article_ids', self::l('EXCLUDED_ARTICLES', 1), '', 'class="ba-input width-md" placeholder="1,2,3"').'                
                '.'<hr/>'.'
                '.self::select('article_ordering', self::l('ORDER_BY', 1), ["a.ordering"=>self::l('DEFAULT'),"a.title"=>self::l('TITLE'),"a.id"=>self::l('ID'),"a.created"=>self::l('CREATEDDATE'),"modified"=>self::l('MODIFIEDDATE'),"rand()"=>self::l('RANDOM')]).'
                '.self::select('article_direction', self::l('ORDERING_DIRECTION', 1), ["ASC"=>self::l('ASCENDING'),"DESC"=>self::l('DESCENDING')], 'data-rel="button"').'                
                '.'<hr/>'.'
                '.self::input('count', self::l('MAX_OF_ITEMS', 1), '5', 'data-rel="spinner" data-no-unit="1"').'
                '.self::select('show_title', self::l('TITLE', 1), ["1"=>self::l('YES'),"0"=>self::l('NO')], 'data-rel="button"').'
                '.self::select('show_date', self::l('DATE', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'data-rel="button"').'
                '.self::select('show_category', self::l('CATEGORY', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'data-rel="button"').'
                '.self::select('show_author', self::l('AUTHOR', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'data-rel="button"').'
                '.self::select('show_introtext', self::l('INTROTEXT', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'class="ba-input select-group" data-rel="button"').'
                '.'<div class="ba-subcontrols show_introtext-1">
                    '.self::input('introtext_limit', self::l('INTROTEXTLIMIT', 1), '36', 'data-rel="spinner" data-no-unit="1"').'
                </div>'.'
                '.self::select('show_readmore', self::l('READMORE', 1), ["1"=>self::l('SHOW'),"0"=>self::l('HIDE')], 'class="ba-input select-group" data-rel="button"').'
                '.'<div class="ba-subcontrols show_readmore-1">
                    '.self::input('readmore_text', self::l('READMORETEXT', 1), 'Read more ->', 'class="ba-input width-md"').'
                </div>';
    }
    //////////////////////////////////
    ///////// END DATA ///////////////////////
    /////////////////////////////////



    //////////////////////////////////
    ///////// BEGIN OPTIONS ///////////////////////
    /////////////////////////////////
    public static function renderOptions()
    {
        $output = self::fieldset_open('class="ba-controls group-inline"', self::l('OPTIONS')).'
        '.self::input('width', self::l('WIDTH', 1), '100%', 'data-rel="range" max="500"').'
        './*self::input('height', self::l('HEIGHT', 1), 'auto', 'data-rel="range" max="500"')*/''.'
        '.self::select('displayMode', self::l('MODE', 1), ["0"=>"Vertical","1"=>"Horizontal"], 'data-rel="button"').'
        '.self::select('effect', self::l('EFFECT', 1), self::cssAnimation()).'
        '.self::select('nextPrev', self::l('NAVIGATION', 1), ["0"=>self::l('NO'), "1"=>self::l('YES')], 'data-rel="button"').'
        '.self::select('keyNav', self::l('KEYNAV', 1), ["0"=>self::l('NO'), "1"=>self::l('YES')], 'data-rel="button"').'
        '.self::input('defaultId', self::l('DEFAULT_ITEM', 1), '1', 'data-rel="spinner" data-no-unit="1"').'
        '.self::select('trigger', self::l('TRIGGER', 1), ["click"=>"Click","mouseenter"=>"Mouse Over"], 'data-rel="button"').'
        '.self::input('speed', self::l('SPEED', 1), '900ms', 'data-rel="range" max="5000" data-unit="ms"').'
        '.self::select('autoPlay', self::l('AUTOPLAY', 1), ["0"=>self::l('NO'),"1"=>self::l('YES')], 'class="ba-input select-group" data-rel="button"').'
        '.self::group_open('autoPlay-1 ba-sub-controls').'
        '.self::input('autoplayDelay', self::l('AUTOPLAY_DELAY', 1), '3000ms', 'data-rel="range" max="15000" data-unit="ms"').'
        '.self::select('pauseOnHover', self::l('PAUSE_ON_HOVER', 1), ["0"=>self::l('NO'), "1"=>self::l('YES')], 'data-rel="button"').'
        '.self::group_close().'
        '.self::select('breakPoint', self::l('BREAKPOINT_LAYOUT', 1), self::arrayCombine(['default', 'accordion','dropdown'])).'
        '.self::input('breakPointWidth', self::l('BREAKPOINT', 1), '576', 'data-rel="range" max="1500" data-no-unit').'
        '.self::fieldset_close();
        $output .= self::fieldset_open('class="ba-controls group-inline"', self::l('SKINS')).'            
            '.self::select('style', self::l('SKIN'), self::arrayCombine(explode(',', 'style'.implode(',style', range(1, 1))))).'
            '.self::select('styleMode', self::l('SKIN_EDIT', 1), ["0"=>self::l('NO'),"1"=>self::l('YES')], 'class="ba-input select-group" data-rel="button"').'
                <div class="styleMode-1 edit-style">
                '.self::fieldset_open('class="style-custom" data-batype="title"', self::l('TITLE').'<b><i class="ba-css-action active">Normal</i><i class="ba-css-action hover">Active</i></b>').'
                    '.self::customStyle().'
                '.self::fieldset_close().'
                '.self::fieldset_open('class="style-custom" data-batype="description"', self::l('CONTENT')).'
                    '.self::customStyle().'
                '.self::fieldset_close().'
                </div>
        '.self::fieldset_close();
        $output1 = self::fieldset_open('class="ba-controls group-inline"', self::l('ICON')).'
            '.self::select('arrowType', self::l('TYPE', 1), self::buttonArrows(), 'class="ba-input icofont" data-rel="list"').'
            <hr/>
            '.self::input('iconSize', self::l('SIZE'), '16px', 'data-rel="range" max="500"').'
            '.self::select('iconOrder', self::l('ALIGNMENT'), ['1'=>self::l('LEFT'), '0'=>self::l('RIGHT')], 'data-rel="button"').'
        '.self::fieldset_close();
        $output .= self::fieldset_open('class="ba-controls group-inline"', self::l('ADVANCED')).'
        '.self::input('tagClass', self::l('CLASS', 1), '', 'class="ba-input width-md"').'
        '.self::textarea('tagCSS', self::l('CSS', 1)).'
        '.self::fieldset_close();
        return $output;
    }

    public static function customStyle($prefix = '')
    {
        $fontFamily = ["Arial", "Helvetica", "Times", "Times New Roman", "Palatino", "Garamond", "Bookman", "Avant Garde", "Courier", "Verdana", "Georgia"];
        $fontWeight = ["normal", "bold","100", "200", "300", "400", "500", "600", "700", "800", "900"];
        return self::input($prefix.'background', self::l('BACKGROUND'), '#721775', 'class="ba-input tinycolor"').'
        '.self::input($prefix.'color', self::l('COLOR'), '#ff0', 'class="ba-input tinycolor"').'
        '.self::input($prefix.'borderColor', self::l('BORDER').' '.self::l('COLOR'), '#333', 'class="ba-input tinycolor"').'
        '.self::select($prefix.'fontFamily', self::l('FONT_FAMILY'), self::arrayCombine($fontFamily, 1)).'
        '.self::input($prefix.'fontSize', self::l('FONT_SIZE'), '18px', 'data-rel="range"').'
        '.self::select($prefix.'fontWeight', self::l('FONT_WEIGHT'), self::arrayCombine($fontWeight, 1)).'
        '.self::select($prefix.'textTransform', self::l('TEXT_TRANSFORM'), self::arrayCombine(["none","uppercase","lowercase","capitalize"]));
    }
    //////////////////////////////////
    ///////// END OPTIONS ///////////////////////
    /////////////////////////////////



    public static function defalutData()
    {
        return '[{"id":"data-source","data-mode":"source-basic","children":[{"id":"source-basic","children":[{"header":"Sports","icon":"fab fa-None","main":"<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. ad</p>"},{"header":"Health","icon":"fas fa-allergies","main":"<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>"},{"header":"Travel","icon":"fas fa-ambulance","main":"<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>"}]},{"id":"source-article","catid":"8,9","child_category":"0","author_filtering_type":"0","created_by":"","article_ids":"","article_ordering":"a.ordering","article_direction":"ASC","count":"5","show_title":"1","show_date":"0","show_category":"0","show_author":"0","show_introtext":"1","introtext_limit":"0","show_readmore":"0","readmore_text":"Read more"}]},{"id":"setting-source","width":"100%","height":"auto","displayMode":"1","collapsible":"0","defaultId":"1","trigger":"click","speed":"900ms","autoPlay":"0","autoplayDelay":"6000ms","pauseOnHover":"1","nextPrev":"1","keyNav":"1","effect":"fadeIn","styleMode":"0","style":"style1","children":[{"id":"title","background":"#F0F0F2§§#F25252","color":"#404040§§#fff","borderColor":"#BFBFBD§§#025959","fontFamily":"","fontSize":"18px§§18px","fontWeight":"","textTransform":"none§§none"},{"id":"description","background":"#fff","color":"#404040","borderColor":"#BFBFBD","fontFamily":"","fontSize":"16px","fontWeight":"","textTransform":"none"}],"arrowType":"plus,minus","iconSize":"18px","iconOrder":"1","tagClass":"ba-mod","tagCSS":"#ID{border-color:#333}","breakPoint":"accordion","breakPointWidth":"576"}]';
    }
    //////////////////// END CLASS ////////////////////
}
