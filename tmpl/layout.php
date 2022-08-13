<?php
/**
* @Copyright   Copyright (C) 2010 BestAddon . All rights reserved.
* @license     GNU General Public License version 2 or later
* @link        http://www.bestaddon.com
**/
defined('_JEXEC') or die;
use Joomla\String\Normalise;

$modName = Normalise::toCamelCase(basename(dirname(__DIR__)));
$helper = $modName.'Helper'; // Call Helper class
$moduleid = !empty($module->id) ? $module->id : 0;
$modID = 'modID'.$moduleid;
$modData = !empty($module->content) ? json_decode($module->content, true) : $baData;

$jList = $helper::getList($modData, $params);
$helper::getobj($modData, 'data-mode', $dataSelect);
$helper::getobj($modData, $dataSelect, $dataBasic);
$helper::getobj($modData, 'setting-source', $setting);

// RENDER VARIABLES BY ARRAY AND GET DATA OBJECT
$css = '';
$css .= str_replace(['{ID}', '[ID]', 'ID'], $modID, $setting['tagCSS']);

// CHECK AJAX BY PREVIEW($ajaxData) & SITE
$assetPath = ((int)JVERSION >= 4 ? '' : '/').'modules/'.basename(dirname(__DIR__)).'/assets/front/';
$listCss = [
    'ba-animate'=>$assetPath.'css/animate.min.css',
    $modName.'-css'=>$assetPath.'css/styles.css'
];
$listJs = [
    'ba-tabs-js'=>$assetPath.'js/baTabs.js'
];
$listAsset = array_merge($listCss, $listJs); //'/assets/front/'
$helper::assets($listAsset, empty($ajaxData) ? true : false);
$helper::assets($css, empty($ajaxData) ? true : false);
$options = '{'.
                '"width":"'.(string)$helper::is($setting['width']).'"'.
                ',"height":"auto"'.
                ',"orient":"'.((bool)$helper::is($setting['displayMode']) ? 'horizontal' : 'vertical').'"'.
                ',"defaultid":'.((int)$helper::is($setting['defaultId']) - 1).
                ',"speed":"'.(string)$helper::is($setting['speed']).'"'.
                ',"interval":'.((bool)$helper::is($setting['autoPlay']) ? (int)$helper::is($setting['autoplayDelay']) : 0).
                ',"hoverpause":'.(int)$helper::is($setting['pauseOnHover']).
                ',"event":"'.(string)$helper::is($setting['trigger']).'"'.
                ',"nextPrev":'.(int)$helper::is($setting['nextPrev']).
                ',"keyNav":'.(int)$helper::is($setting['keyNav']).
                ',"effect":"'.(string)$helper::is($setting['effect']).'"'.
                ',"breakPoint":"'.(string)$helper::is($setting['breakPoint']).'"'.
                ',"breakPointWidth":'.(int)$helper::is($setting['breakPointWidth']).
                ',"style":"'.(string)$helper::is($setting['style']).'"'.
            '}';
$list = ($dataSelect == "source-article" && $jList) ? $jList : $dataBasic['children'];
$tabNavs = '';
$tabPanels = '';
foreach ($list ?: [] as $key => $item) {
    $tabNavs .= '<li role="presentation"><a href="#baTab'.$key.'" role="tab" class="ba--title"><span>'.(isset($item->title) ? $item->title : (!empty($item['icon']) ? '<i class="'.$item['icon'].'"></i>' : '').$item['header']).'</span></a></li>';
    $tabPanels .= '<div role="tabpanel" class="ba--description" id="baTab'.$key.'">'.(isset($item->maintext) ? $item->maintext : $helper::isMod($item['main'], $moduleid)).'</div>';
}
$html = '<div class="baContainer clearfix '.$setting['tagClass'].' '.(empty($ajaxData) ? '' : 'ba-dialog-body').'">'.
            '<div id="ba-'.$modID.'-wrap">'.
                '<div id="'.$modID.'" class="ba--general '.($helper::is($setting['styleMode']) ? 'custom' : '').'" data-ba-tabs="true" data-options=\''.$options.'\'>';
$html .=            '<nav><ul class="ba__nav-tabs" role="tablist">'.$tabNavs.'</ul></nav>'.
                    '<div class="ba__panel-tabs">'.$tabPanels.'</div>';
$html .=        '</div>
            </div>
        </div>';
echo $html;
