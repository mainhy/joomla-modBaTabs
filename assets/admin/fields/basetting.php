<?php
defined('JPATH_BASE') or die;
use Joomla\CMS\Factory;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldBasetting extends JFormField
{
    public $type = 'Basetting';
    public function getInput()
    {
        require_once dirname(__FILE__).'/form-render.php';
        $baRender = new BestFormRender;
        $doc = JFactory::getDocument();
        $db = JFactory::getDbo();
        $modName = basename(dirname(dirname(dirname(__DIR__))));
        $base_url = ((int)JVERSION >= 4 ? '' : JURI::root(true).'/').'modules/'.$modName.'/assets/admin';
        if ((int)JVERSION >= 4) {
            $wa = $doc->getWebAssetManager();
            $wa->registerAndUseScript('ba-jquery-ui', $base_url.'/js/jquery-ui-custom.min.js', [], [], ['jquery']);
            $wa->registerAndUseScript('ba-tinymce-js', 'media/vendor/tinymce/tinymce.min.js');
            $wa->registerAndUseScript('ba-modadmin0-js', $base_url.'/js/color-picker.js');
            $wa->registerAndUseScript('ba-modadmin1-js', $base_url.'/js/form.js');
            $wa->registerAndUseScript('ba-modadmin2d-js', $base_url.'/js/admin.js');
            $wa->registerAndUseStyle('ba-modadmin-css', $base_url.'/css/admin.css', [], [], ['fontawesome']);
            $wa->addInlineScript('var modName="'.$modName.'";');
        } else {
            $doc->addStyleSheet('//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css');
            $doc->addScript(JURI::root(true).'/media/editors/tinymce/tinymce.min.js');
            $doc->addScript($base_url.'/js/jquery-ui-custom.min.js');
            $doc->addScript($base_url.'/js/color-picker.js');
            $doc->addScript($base_url.'/js/form.js');
            $doc->addScript($base_url.'/js/admin.js');
            $doc->addStyleSheet($base_url.'/css/admin.css');
            $doc->addScriptDeclaration('var systemPathRoot="'.JURI::root(true).'";var modID='.JFactory::getApplication()->input->get('id').'; var modName="'.$modName.'";function jModalClose(){return false};function jInsertFieldValue(value, id){return false};'); // SET Module ID & Name for AJAX via JS
        }
        
        $getVal = $this->form->getValue('content');
        if (empty($getVal)) {
            $db->setQuery('ALTER TABLE `#__modules` MODIFY `content` LONGTEXT')->execute();
        }
        $getVal = empty($getVal) ? $baRender->defalutData() : $getVal;
        $baData = json_decode($getVal, true);

        
        $baTabLabels = $baTabBody = $output = '';
        $output .= '<div id="ba-modal-preview" title="Preview"></div>
                    <div class="ba-manager clearfix" data-jversion="'.JVERSION.'"><a href="#" class="ba-preview">Preview &rarr;</a>';
        $output .= '<div class="para-tabs main-tabs" data-rel="tablist">';
        $output .= '<ul>';
        foreach (is_array($baData) ? $baData : [] as $key => $value) {
            $output .= '<li><a href="#'.$value['id'].$key.'">'.substr($value['id'], 0, -7).'</a></li>';
        }
        $output .= '</ul>';
        foreach (is_array($baData) ? $baData : [] as $key => $value) {
            $output .= '<div id="'.$value['id'].$key.'" class="'.$value['id'].'"><div '.(($value['id'] != 'data-source1') ?'data-batype="'.$value['id'].'"' : '').'>';
            if ($value['id'] == 'data-source') {
                /////  DATA SOURCE /////////////////////
                $output .= '<div class="source-wrap">';
                $output .= '<div class="source-bar ba-controls clearfix">'.
                        $baRender->select('data-mode', 'Data source', ['source-basic'=>'Basic', 'source-article'=>(function_exists('__') ? 'Wordpress Posts' : 'Joomla Articles')], 'class="ba-input select-group" data-rel="button"').'
                    </div>';
                foreach (is_array($value['children']) ? $value['children'] : [] as $data) {
                    $output .= '<div class="data-mode-'.$data['id'].' '.$data['id'].' ba-controls data-content clearfix" data-batype="'.$data['id'].'">';
                    $output .= (is_array($data) ? $baRender->renderData($data) : '');
                    $output .= '</div>';
                }
                $output .= '</div>';
            }
            if ($value['id'] == 'setting-source') {
                $output .= $baRender->renderOptions();
            }
            $output .= '</div></div>';
        }
        $output .= '</div>';
        $output .= '</div>';
        $output .= '<textarea id="ba-form-content" name="jform[content]" style="display:none!important">'.$getVal.'</textarea>';
        return $output;
    }
}
