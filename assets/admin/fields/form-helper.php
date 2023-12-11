<?php
/**
* @Copyright   Copyright (C) 2010 BestAddon . All rights reserved.
* @license     GNU General Public License version 2 or later
* @link        http://www.bestaddon.com
**/
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

trait BestAddonFormElements
{
    private static $propertyName = 'data-name'; // name OR data-name
    public static function div_open($tagClass = '')
    {
        return '<div class="ba-control '.$tagClass.'">';
    }
    public static function div_close()
    {
        return '</div>'."\n";
    }
    public static function group_open($tagClass = '', $groupLabel = '')
    {
        return '<div class="ba-controls clearfix '.$tagClass.'">'.($groupLabel !='' ? '<label>'.$groupLabel.'</label>': '')."\n";
    }
    public static function group_close()
    {
        return '</div>'."\n";
    }
    public static function fieldset_open($attributes = '', $legend_text = '')
    {
        $fieldset = '<fieldset'.self::_attributes_to_string($attributes).">\n";
        if ($legend_text !== '') {
            return $fieldset.'<legend>'.$legend_text."</legend>\n";
        }
        return $fieldset;
    }
    public static function fieldset_close($extra = '')
    {
        return '</fieldset>'.$extra;
    }
    public static function label($label_text = '', $id = '', $attributes = array())
    {
        return '<label '.($id !== '' ? 'for="'.$id.'" ' : '').self::_attributes_to_string($attributes).'>'.$label_text.'</label>';
    }
    //input('name', 'Name text', 'value', 'type="checkbox"', 'Label')
    //input('name', 'Name text', '0', 'type="checkbox" data-rel="switch"', 'Label')
    //input('name', 'Name text', '0', 'type="radio"', 'Label')
    public static function input($name = '', $label ='', $value = '', $extra = '', $groupLabel = '', $linked = false)
    {
        $output = stripos($groupLabel, 'notag') === false ? self::div_open() : '';
        $output .= ($groupLabel !='' && stripos($groupLabel, 'notag') === false ? '<label>'.$groupLabel.'</label>': '');
        if (is_array($name)) {
            $label = is_array($label) ? $label : array($label);
            $value = is_array($value) ? $value : array($value);
            $output .= '<div class="list-flush '.($linked ? 'group' : '').'">';
            if ($linked) {
                $output .= '<div class="item"><button type="button" data-rel="group-linked" class="fas"></button></div>';
            }
            foreach ($name as $i => $val) {
                $typeArr = (stripos((isset($extra[$i]) ? $extra[$i] : ''), 'type') === false ? 'type="text"' : '');
                $classArr = (stripos((isset($extra[$i]) ? $extra[$i] : ''), 'class') === false ? 'class="ba-input"' : '');
                $inputExtraArr = self::_attributes_to_string((isset($extra[$i]) ? $extra[$i] : ''));
                $output .= '<div class="item">'.
                            (stripos($extra[$i], 'checkbox') !== false || stripos($extra[$i], 'radio') !== false ? '<span class="radio-check">' : ''). //BEGIN checkbox or radio input
                            (isset($label[$i]) ? '<label>'.$label[$i].'</label>': '').
                            (stripos($extra[$i], '"range"') !== false ? '<div class="ba-range-wrap"><div class="ba-range-inside"><input type="range" class="ba-range" '.$inputExtraArr.'/></div>' : ''). //Tag for range input
                            (stripos($extra[$i], 'tinycolor') !== false ? '<div class="color-append">' : ''). //BEGIN color input
                                '<input '.self::$propertyName.'="'.$val.'" value="'.(isset($value[$i]) ? $value[$i] : '').'" '.$typeArr.' '.$classArr.' '.$inputExtraArr.' />'.
                            (stripos($extra[$i], 'tinycolor') !== false ? '</div>' : ''). //END color input
                            (stripos($extra[$i], '"range"') !== false ? '</div>' : ''). //Tag for range input
                            (stripos($extra[$i], 'checkbox') !== false || stripos($extra[$i], 'radio') !== false ? '<i>&nbsp;</i></span>' : '').//END checkbox or radio input
                            '</div>';
            }
            $output .= '</div>';
        } else {
            $type = (stripos($extra, 'type') === false ? 'type="text"' : '');
            $class = (stripos($extra, 'class') === false ? 'class="ba-input"' : '');
            $inputExtra = self::_attributes_to_string($extra);
            $output .= (stripos($extra, 'checkbox') !== false || stripos($extra, 'radio') !== false ? '<label class="radio-check">' : ''); //Tag for checkbox or radio input
            $output .= ($label !='' ? '<label>'.$label.'</label>': '');
            $output .= (stripos($extra, '"range"') !== false ? '<div class="ba-range-wrap"><div class="ba-range-inside"><input type="range" class="ba-range" '.$inputExtra.'/></div>' : ''). //BEGIN range input
                        (stripos($extra, 'media') !== false ? '<div class="media-append">' : '').
                        (stripos($extra, 'tinycolor') !== false ? '<div class="color-append">' : ''). //BEGIN color input
                            '<input '.self::$propertyName.'="'.$name.'" value="'.$value.'" '.$type.' '.$class.' '.$inputExtra.' />'.
                        (stripos($extra, 'tinycolor') !== false ? '</div>' : ''). //END color input
                        (stripos($extra, 'media') !== false ? '<a class="fas add-on" data-toggle="media">&#xf002;</a></div>' : '').
                        (stripos($extra, '"range"') !== false ? '</div>' : ''); //END range input
                $output .= (stripos($extra, 'checkbox') !== false || stripos($extra, 'radio') !== false ? '<i>&nbsp;</i></label>' : '');//Tag for checkbox or radio input
        }
        $output .= stripos($groupLabel, 'notag') === false ? self::div_close() : '';
        return $output."\n";
    }
    
    public static function inputResponsive($name = [], $label =[], $value = [], $extra = '', $groupLabel = '', $linked = false)
    {
        $isUniqid = [];
        $output = stripos($groupLabel, 'notag') === false ? self::div_open() : '';
        $output .= ($groupLabel !='' && stripos($groupLabel, 'notag') === false ? '<label>'.$groupLabel.'</label>': '');
        $nameArrs = ['lg' => $name, 'md' => $name, 'sm' => $name];
        $output .= '<div class="ba---devices best-tablist-device" data-rel="tablist">';
        $output .= '<ul class="tab-flush">';
        foreach (array_keys($nameArrs) as $key) {
            $isUniqid[$key] = uniqid().$key;
            $output .= '<li class="fas device'.$key.'" data-id="device'.$key.'"><a href="'.$isUniqid[$key].'">'.
                                ($key == 'lg' ? '&#xf108;' : '').
                                ($key == 'md' ? '&#xf3fa;' : '').
                                ($key == 'sm' ? '&#xf3cd;' : '')
                           .'</a></li>';
        }
        $output .= '</ul>';
        foreach ($nameArrs as $i => $val) {
            if (is_array($val)) {
                $output .= '<div id="'.$isUniqid[$i].'"><div class="'.(stripos($extra, '"range"') !== false ? 'range-box' : '').' list-flush '.($linked ? 'group' : '').'" data-id="device'.$i.'">';
                if ($linked) {
                    $output .= '<div class="item"><button type="button" data-rel="group-linked" class="fas">&nbsp;</button></div>';
                }
                foreach ($val as $k => $v) {
                    $typeArrs = (stripos((isset($extra) ? $extra : ''), 'type') === false ? 'type="text"' : '');
                    $classArrs = (stripos((isset($extra) ? $extra : ''), 'class') === false ? 'class="ba-input"' : '');
                    $inputExtraArrs = self::_attributes_to_string($extra);
                    $output .= '<div class="item">';
                    $output .= (isset($label[$k]) ? '<label>'.$label[$k].'</label>': '');
                    $output .= (stripos($extra, '"range"') !== false ? '<div class="ba-range-wrap"><div class="ba-range-inside"><input type="range" class="ba-range" '.$inputExtraArrs.'/></div>' : ''); //BEGIN Tag for range input
                    $output .= '<input '.self::$propertyName.'="'.($i != 'lg' ? $i.'-' : '').$v.'" value="'.(isset($value[$k]) ? $value[$k] : '').'" '.$typeArrs.' '.$classArrs.' '.$inputExtraArrs.' />';
                    $output .= (stripos($extra, '"range"') !== false ? '</div>' : ''); //END Tag for range input
                    $output .= '</div>';
                }
                $output .= '</div></div>';
            }
        }
        $output .= '</div>';
        $output .= stripos($groupLabel, 'notag') === false ? self::div_close() : '';
        return $output."\n";
    }

    /**
     * Textarea field
     */
    public static function textarea($name = '', $label ='', $value = '', $extra = '')
    {
        $class = (stripos($extra, 'class') === false ? 'class="ba-input"' : '');
        $inputExtra = self::_attributes_to_string($extra);
        $output = stripos($extra, 'notag') === false ? self::div_open() : '';
        $output .= ($label !='' ? '<label>'.$label.'</label>': '');
        $output .= '<textarea '.self::$propertyName.'="'.$name.'" cols="50" rows="5" '.$class.' '.$inputExtra.'>'.$value.'</textarea>';
        $output .= stripos($extra, 'notag') === false ? self::div_close() : '';
        return $output."\n";
    }

    /**
     * Select field
     */
    public static function select($name = '', $label ='', $options = array(), $extra = '', $selected = array(), $addNone = '')
    {
        $class = (stripos($extra, 'class') === false ? 'class="ba-input"' : '');
        $extra = self::_attributes_to_string($extra);
        $output = stripos($extra, 'notag') === false ? self::div_open() : '';
        $output .= ($label !='' ? '<label>'.$label.'</label>': '');
        $output .= (stripos($extra, 'data-rel') !== false ? '<div class="select-group-wrap">' : '').'<select '.self::$propertyName.'="'.$name.'" '.$class.' '.$extra.'>';
        $output .= ($addNone !='' ? '<option value="">'.$addNone.'</option>' : '');
        foreach ($options as $key => $val) {
            $sel = (($key == 'solid') ? 'fas' : (($key == 'regular') ? 'far' : 'fab'));
            if (is_array($val)) {
                $output .= '<optgroup label="'.$key.'">';
                foreach ($val as $i => $opt_val) {
                    $v = (stripos($extra, 'fontWeASome') !== false ? $sel.' fa-'.$opt_val : $i);
                    $output .= '<option value="'.$v.'"'.(in_array($v, $selected) ? ' selected="selected"' : '').'>'.(stripos($extra, 'fontWeASome') !== false ? ' &lt;i class="'.$sel.' fa-'.$opt_val.'"&gt;&lt;/i&gt;' : (string) $opt_val).'</option>';
                }
                $output .= '</optgroup>';
            } else {
                $va = (stripos($extra, 'fontWeASome') !== false ? $sel.' fa-'.$val : $key);
                $output .= '<option value="'.$va.'"'.(in_array($va, $selected) ? ' selected="selected"' : '').'>'.(stripos($extra, 'fontWeASome') !== false ? ' &lt;i class="fas fa-'.$val.'"&gt;&lt;/i&gt;' : (string) $val)."</option>";
            }
        }
        $output .= '</select>'.(stripos($extra, 'data-rel') !== false ? '</div>' : '');
        $output .= stripos($extra, 'notag') === false ? self::div_close() : '';
        return $output."\n";
    }

    /**
     * Form Button
     */
    public static function button($name = '', $content = '', $extra = '')
    {
        return '<button '.self::$propertyName.'="'.$name.'" type="button" '.self::_attributes_to_string($extra).'>'.$content.'</button>'."\n";
    }

    /**
     * Attributes To String
     * Helper public static function used by some of the form helpers
     * @param	mixed
     * @return	string
     */
    public static function _attributes_to_string($attributes)
    {
        if (empty($attributes)) {
            return '';
        }
        if (is_object($attributes)) {
            $attributes = (array) $attributes;
        }
        if (is_array($attributes)) {
            $atts = '';
            foreach ($attributes as $key => $val) {
                $atts .= ' '.$key.'="'.$val.'"';
            }
            return $atts;
        }
        if (is_string($attributes)) {
            return ' '.$attributes;
        }
        return false;
    }

    /**
     * //fontawesome.com?v5.2
     */
    public static function fontAwesome($addNone = 0)
    {
        $fontJson = '{"solid":["ad","address-book","address-card","adjust","air-freshener","align-center","align-justify","align-left","align-right","allergies","ambulance","american-sign-language-interpreting","anchor","angle-double-down","angle-double-left","angle-double-right","angle-double-up","angle-down","angle-left","angle-right","angle-up","angry","ankh","apple-alt","archive","archway","arrow-alt-circle-down","arrow-alt-circle-left","arrow-alt-circle-right","arrow-alt-circle-up","arrow-circle-down","arrow-circle-left","arrow-circle-right","arrow-circle-up","arrow-down","arrow-left","arrow-right","arrow-up","arrows-alt","arrows-alt-h","arrows-alt-v","assistive-listening-systems","asterisk","at","atlas","atom","audio-description","award","backspace","backward","balance-scale","ban","band-aid","barcode","bars","baseball-ball","basketball-ball","bath","battery-empty","battery-full","battery-half","battery-quarter","battery-three-quarters","bed","beer","bell","bell-slash","bezier-curve","bible","bicycle","binoculars","birthday-cake","blender","blender-phone","blind","bold","bolt","bomb","bone","bong","book","book-dead","book-open","book-reader","bookmark","bowling-ball","box","box-open","boxes","braille","brain","briefcase","briefcase-medical","broadcast-tower","broom","brush","bug","building","bullhorn","bullseye","burn","bus","bus-alt","business-time","calculator","calendar","calendar-alt","calendar-check","calendar-minus","calendar-plus","calendar-times","camera","camera-retro","campground","cannabis","capsules","car","car-alt","car-battery","car-crash","car-side","caret-down","caret-left","caret-right","caret-square-down","caret-square-left","caret-square-right","caret-square-up","caret-up","cart-arrow-down","cart-plus","cat","certificate","chair","chalkboard","chalkboard-teacher","charging-station","chart-area","chart-bar","chart-line","chart-pie","check","check-circle","check-double","check-square","chess","chess-bishop","chess-board","chess-king","chess-knight","chess-pawn","chess-queen","chess-rook","chevron-circle-down","chevron-circle-left","chevron-circle-right","chevron-circle-up","chevron-down","chevron-left","chevron-right","chevron-up","child","church","circle","circle-notch","city","clipboard","clipboard-check","clipboard-list","clock","clone","closed-captioning","cloud","cloud-download-alt","cloud-moon","cloud-sun","cloud-upload-alt","cocktail","code","code-branch","coffee","cog","cogs","coins","columns","comment","comment-alt","comment-dollar","comment-dots","comment-slash","comments","comments-dollar","compact-disc","compass","compress","concierge-bell","cookie","cookie-bite","copy","copyright","couch","credit-card","crop","crop-alt","cross","crosshairs","crow","crown","cube","cubes","cut","database","deaf","desktop","dharmachakra","diagnoses","dice","dice-d20","dice-d6","dice-five","dice-four","dice-one","dice-six","dice-three","dice-two","digital-tachograph","directions","divide","dizzy","dna","dog","dollar-sign","dolly","dolly-flatbed","donate","door-closed","door-open","dot-circle","dove","download","drafting-compass","dragon","draw-polygon","drum","drum-steelpan","drumstick-bite","dumbbell","dungeon","edit","eject","ellipsis-h","ellipsis-v","envelope","envelope-open","envelope-open-text","envelope-square","equals","eraser","euro-sign","exchange-alt","exclamation","exclamation-circle","exclamation-triangle","expand","expand-arrows-alt","external-link-alt","external-link-square-alt","eye","eye-dropper","eye-slash","fast-backward","fast-forward","fax","feather","feather-alt","female","fighter-jet","file","file-alt","file-archive","file-audio","file-code","file-contract","file-csv","file-download","file-excel","file-export","file-image","file-import","file-invoice","file-invoice-dollar","file-medical","file-medical-alt","file-pdf","file-powerpoint","file-prescription","file-signature","file-upload","file-video","file-word","fill","fill-drip","film","filter","fingerprint","fire","fire-extinguisher","first-aid","fish","fist-raised","flag","flag-checkered","flask","flushed","folder","folder-minus","folder-open","folder-plus","font","font-awesome-logo-full","football-ball","forward","frog","frown","frown-open","funnel-dollar","futbol","gamepad","gas-pump","gavel","gem","genderless","ghost","gift","glass-martini","glass-martini-alt","glasses","globe","globe-africa","globe-americas","globe-asia","golf-ball","gopuram","graduation-cap","greater-than","greater-than-equal","grimace","grin","grin-alt","grin-beam","grin-beam-sweat","grin-hearts","grin-squint","grin-squint-tears","grin-stars","grin-tears","grin-tongue","grin-tongue-squint","grin-tongue-wink","grin-wink","grip-horizontal","grip-vertical","h-square","hammer","hamsa","hand-holding","hand-holding-heart","hand-holding-usd","hand-lizard","hand-paper","hand-peace","hand-point-down","hand-point-left","hand-point-right","hand-point-up","hand-pointer","hand-rock","hand-scissors","hand-spock","hands","hands-helping","handshake","hanukiah","hashtag","hat-wizard","haykal","hdd","heading","headphones","headphones-alt","headset","heart","heartbeat","helicopter","highlighter","hiking","hippo","history","hockey-puck","home","horse","hospital","hospital-alt","hospital-symbol","hot-tub","hotel","hourglass","hourglass-end","hourglass-half","hourglass-start","house-damage","hryvnia","i-cursor","id-badge","id-card","id-card-alt","image","images","inbox","indent","industry","infinity","info","info-circle","italic","jedi","joint","journal-whills","kaaba","key","keyboard","khanda","kiss","kiss-beam","kiss-wink-heart","kiwi-bird","landmark","language","laptop","laptop-code","laugh","laugh-beam","laugh-squint","laugh-wink","layer-group","leaf","lemon","less-than","less-than-equal","level-down-alt","level-up-alt","life-ring","lightbulb","link","lira-sign","list","list-alt","list-ol","list-ul","location-arrow","lock","lock-open","long-arrow-alt-down","long-arrow-alt-left","long-arrow-alt-right","long-arrow-alt-up","low-vision","luggage-cart","magic","magnet","mail-bulk","male","map","map-marked","map-marked-alt","map-marker","map-marker-alt","map-pin","map-signs","marker","mars","mars-double","mars-stroke","mars-stroke-h","mars-stroke-v","mask","medal","medkit","meh","meh-blank","meh-rolling-eyes","memory","menorah","mercury","microchip","microphone","microphone-alt","microphone-alt-slash","microphone-slash","microscope","minus","minus-circle","minus-square","mobile","mobile-alt","money-bill","money-bill-alt","money-bill-wave","money-bill-wave-alt","money-check","money-check-alt","monument","moon","mortar-pestle","mosque","motorcycle","mountain","mouse-pointer","music","network-wired","neuter","newspaper","not-equal","notes-medical","object-group","object-ungroup","oil-can","om","otter","outdent","paint-brush","paint-roller","palette","pallet","paper-plane","paperclip","parachute-box","paragraph","parking","passport","pastafarianism","paste","pause","pause-circle","paw","peace","pen","pen-alt","pen-fancy","pen-nib","pen-square","pencil-alt","pencil-ruler","people-carry","percent","percentage","phone","phone-slash","phone-square","phone-volume","piggy-bank","pills","place-of-worship","plane","plane-arrival","plane-departure","play","play-circle","plug","plus","plus-circle","plus-square","podcast","poll","poll-h","poo","poop","portrait","pound-sign","power-off","pray","praying-hands","prescription","prescription-bottle","prescription-bottle-alt","print","procedures","project-diagram","puzzle-piece","qrcode","question","question-circle","quidditch","quote-left","quote-right","quran","random","receipt","recycle","redo","redo-alt","registered","reply","reply-all","retweet","ribbon","ring","road","robot","rocket","route","rss","rss-square","ruble-sign","ruler","ruler-combined","ruler-horizontal","ruler-vertical","running","rupee-sign","sad-cry","sad-tear","save","school","screwdriver","scroll","search","search-dollar","search-location","search-minus","search-plus","seedling","server","shapes","share","share-alt","share-alt-square","share-square","shekel-sign","shield-alt","ship","shipping-fast","shoe-prints","shopping-bag","shopping-basket","shopping-cart","shower","shuttle-van","sign","sign-in-alt","sign-language","sign-out-alt","signal","signature","sitemap","skull","skull-crossbones","slash","sliders-h","smile","smile-beam","smile-wink","smoking","smoking-ban","snowflake","socks","solar-panel","sort","sort-alpha-down","sort-alpha-up","sort-amount-down","sort-amount-up","sort-down","sort-numeric-down","sort-numeric-up","sort-up","spa","space-shuttle","spider","spinner","splotch","spray-can","square","square-full","square-root-alt","stamp","star","star-and-crescent","star-half","star-half-alt","star-of-david","star-of-life","step-backward","step-forward","stethoscope","sticky-note","stop","stop-circle","stopwatch","store","store-alt","stream","street-view","strikethrough","stroopwafel","subscript","subway","suitcase","suitcase-rolling","sun","superscript","surprise","swatchbook","swimmer","swimming-pool","synagogue","sync","sync-alt","syringe","table","table-tennis","tablet","tablet-alt","tablets","tachometer-alt","tag","tags","tape","tasks","taxi","teeth","teeth-open","terminal","text-height","text-width","th","th-large","th-list","theater-masks","thermometer","thermometer-empty","thermometer-full","thermometer-half","thermometer-quarter","thermometer-three-quarters","thumbs-down","thumbs-up","thumbtack","ticket-alt","times","times-circle","tint","tint-slash","tired","toggle-off","toggle-on","toilet-paper","toolbox","tooth","torah","torii-gate","tractor","trademark","traffic-light","train","transgender","transgender-alt","trash","trash-alt","tree","trophy","truck","truck-loading","truck-monster","truck-moving","truck-pickup","tshirt","tty","tv","umbrella","umbrella-beach","underline","undo","undo-alt","universal-access","university","unlink","unlock","unlock-alt","upload","user","user-alt","user-alt-slash","user-astronaut","user-check","user-circle","user-clock","user-cog","user-edit","user-friends","user-graduate","user-injured","user-lock","user-md","user-minus","user-ninja","user-plus","user-secret","user-shield","user-slash","user-tag","user-tie","user-times","users","users-cog","utensil-spoon","utensils","vector-square","venus","venus-double","venus-mars","vial","vials","video","video-slash","vihara","volleyball-ball","volume-down","volume-mute","volume-off","volume-up","walking","wallet","warehouse","weight","weight-hanging","wheelchair","wifi","wind","window-close","window-maximize","window-minimize","window-restore","wine-bottle","wine-glass","wine-glass-alt","won-sign","wrench","x-ray","yen-sign","yin-yang"],"regular":["address-book","address-card","angry","arrow-alt-circle-down","arrow-alt-circle-left","arrow-alt-circle-right","arrow-alt-circle-up","bell","bell-slash","bookmark","building","calendar","calendar-alt","calendar-check","calendar-minus","calendar-plus","calendar-times","caret-square-down","caret-square-left","caret-square-right","caret-square-up","chart-bar","check-circle","check-square","circle","clipboard","clock","clone","closed-captioning","comment","comment-alt","comment-dots","comments","compass","copy","copyright","credit-card","dizzy","dot-circle","edit","envelope","envelope-open","eye","eye-slash","file","file-alt","file-archive","file-audio","file-code","file-excel","file-image","file-pdf","file-powerpoint","file-video","file-word","flag","flushed","folder","folder-open","font-awesome-logo-full","frown","frown-open","futbol","gem","grimace","grin","grin-alt","grin-beam","grin-beam-sweat","grin-hearts","grin-squint","grin-squint-tears","grin-stars","grin-tears","grin-tongue","grin-tongue-squint","grin-tongue-wink","grin-wink","hand-lizard","hand-paper","hand-peace","hand-point-down","hand-point-left","hand-point-right","hand-point-up","hand-pointer","hand-rock","hand-scissors","hand-spock","handshake","hdd","heart","hospital","hourglass","id-badge","id-card","image","images","keyboard","kiss","kiss-beam","kiss-wink-heart","laugh","laugh-beam","laugh-squint","laugh-wink","lemon","life-ring","lightbulb","list-alt","map","meh","meh-blank","meh-rolling-eyes","minus-square","money-bill-alt","moon","newspaper","object-group","object-ungroup","paper-plane","pause-circle","play-circle","plus-square","question-circle","registered","sad-cry","sad-tear","save","share-square","smile","smile-beam","smile-wink","snowflake","square","star","star-half","sticky-note","stop-circle","sun","surprise","thumbs-down","thumbs-up","times-circle","tired","trash-alt","user","user-circle","window-close","window-maximize","window-minimize","window-restore"],"brands":["500px","accessible-icon","accusoft","acquisitions-incorporated","adn","adversal","affiliatetheme","algolia","alipay","amazon","amazon-pay","amilia","android","angellist","angrycreative","angular","app-store","app-store-ios","apper","apple","apple-pay","asymmetrik","audible","autoprefixer","avianex","aviato","aws","bandcamp","behance","behance-square","bimobject","bitbucket","bitcoin","bity","black-tie","blackberry","blogger","blogger-b","bluetooth","bluetooth-b","btc","buromobelexperte","buysellads","cc-amazon-pay","cc-amex","cc-apple-pay","cc-diners-club","cc-discover","cc-jcb","cc-mastercard","cc-paypal","cc-stripe","cc-visa","centercode","chrome","cloudscale","cloudsmith","cloudversify","codepen","codiepie","connectdevelop","contao","cpanel","creative-commons","creative-commons-by","creative-commons-nc","creative-commons-nc-eu","creative-commons-nc-jp","creative-commons-nd","creative-commons-pd","creative-commons-pd-alt","creative-commons-remix","creative-commons-sa","creative-commons-sampling","creative-commons-sampling-plus","creative-commons-share","creative-commons-zero","critical-role","css3","css3-alt","cuttlefish","d-and-d","dashcube","delicious","deploydog","deskpro","dev","deviantart","digg","digital-ocean","discord","discourse","dochub","docker","draft2digital","dribbble","dribbble-square","dropbox","drupal","dyalog","earlybirds","ebay","edge","elementor","ello","ember","empire","envira","erlang","ethereum","etsy","expeditedssl","facebook","facebook-f","facebook-messenger","facebook-square","fantasy-flight-games","firefox","first-order","first-order-alt","firstdraft","flickr","flipboard","fly","font-awesome","font-awesome-alt","font-awesome-flag","font-awesome-logo-full","fonticons","fonticons-fi","fort-awesome","fort-awesome-alt","forumbee","foursquare","free-code-camp","freebsd","fulcrum","galactic-republic","galactic-senate","get-pocket","gg","gg-circle","git","git-square","github","github-alt","github-square","gitkraken","gitlab","gitter","glide","glide-g","gofore","goodreads","goodreads-g","google","google-drive","google-play","google-plus","google-plus-g","google-plus-square","google-wallet","gratipay","grav","gripfire","grunt","gulp","hacker-news","hacker-news-square","hackerrank","hips","hire-a-helper","hooli","hornbill","hotjar","houzz","html5","hubspot","imdb","instagram","internet-explorer","ioxhost","itunes","itunes-note","java","jedi-order","jenkins","joget","joomla","js","js-square","jsfiddle","kaggle","keybase","keycdn","kickstarter","kickstarter-k","korvue","laravel","lastfm","lastfm-square","leanpub","less","line","linkedin","linkedin-in","linode","linux","lyft","magento","mailchimp","mandalorian","markdown","mastodon","maxcdn","medapps","medium","medium-m","medrt","meetup","megaport","microsoft","mix","mixcloud","mizuni","modx","monero","napster","neos","nimblr","nintendo-switch","node","node-js","npm","ns8","nutritionix","odnoklassniki","odnoklassniki-square","old-republic","opencart","openid","opera","optin-monster","osi","page4","pagelines","palfed","patreon","paypal","penny-arcade","periscope","phabricator","phoenix-framework","phoenix-squadron","php","pied-piper","pied-piper-alt","pied-piper-hat","pied-piper-pp","pinterest","pinterest-p","pinterest-square","playstation","product-hunt","pushed","python","qq","quinscape","quora","r-project","ravelry","react","readme","rebel","red-river","reddit","reddit-alien","reddit-square","rendact","renren","replyd","researchgate","resolving","rev","rocketchat","rockrms","safari","sass","schlix","scribd","searchengin","sellcast","sellsy","servicestack","shirtsinbulk","shopware","simplybuilt","sistrix","sith","skyatlas","skype","slack","slack-hash","slideshare","snapchat","snapchat-ghost","snapchat-square","soundcloud","speakap","spotify","squarespace","stack-exchange","stack-overflow","staylinked","steam","steam-square","steam-symbol","sticker-mule","strava","stripe","stripe-s","studiovinari","stumbleupon","stumbleupon-circle","superpowers","supple","teamspeak","telegram","telegram-plane","tencent-weibo","the-red-yeti","themeco","themeisle","trade-federation","trello","tripadvisor","tumblr","tumblr-square","twitch","twitter","twitter-square","typo3","uber","uikit","uniregistry","untappd","usb","ussunnah","vaadin","viacoin","viadeo","viadeo-square","viber","vimeo","vimeo-square","vimeo-v","vine","vk","vnv","vuejs","weebly","weibo","weixin","whatsapp","whatsapp-square","whmcs","wikipedia-w","windows","wix","wizards-of-the-coast","wolf-pack-battalion","wordpress","wordpress-simple","wpbeginner","wpexplorer","wpforms","xbox","xing","xing-square","y-combinator","yahoo","yandex","yandex-international","yelp","yoast","youtube","youtube-square","zhihu"]}';
        /*//////////////////////////////////////////////////////////////
        1) Open page in Google chrome: https://fontawesome.com/cheatsheet
        2) Run code in Script snippet of browser(F12 -> Source -> Snippet)
        //////////////////
        var groups = {};
        var sections = document.getElementsByClassName('cheatsheet-set');
        for (const section of sections) {
        //const names = {};
        const names = [];
        groups[section.id] = names;
        var icons = section.getElementsByClassName('icon');
        for (const icon of icons) {
            const name = icon.getElementsByClassName('icon-name')[0].innerText;
            //const code = icon.getElementsByClassName('icon-unicode')[0].innerText;
            //names[((section.id == 'solid') ? 'fas ' : (section.id == 'regular') ? 'far ' : 'fab ')+'fa-'+name]='&#x'+code+';';
            names.push(name);
        }
        }
        console.log(JSON.stringify(groups));
        *////////////////////////////////////////////////////////////
        $fontArray = json_decode($fontJson, true);
        return (bool)$addNone ? $fontArray + [""=>"None"] : $fontArray;
    }

    /**
     * //github.com/animate-css/animate.css
     */
    public static function cssAnimation($isOut=false)
    {
        $in = array("fadeIn","fadeInDown","fadeInDownBig","fadeInLeft","fadeInLeftBig","fadeInRight","fadeInRightBig","fadeInUp","fadeInUpBig","rollIn","flipInX","flipInY","rotateIn","rotateInDownLeft","rotateInDownRight","rotateInUpLeft","rotateInUpRight","zoomIn","zoomInDown","zoomInLeft","zoomInRight","zoomInUp","bounceIn","bounceInDown","bounceInLeft","bounceInRight","bounceInUp","flash","pulse","rubberBand","shake","swing","tada","wobble","jello", "heartBeat","slideInUp","slideInDown","slideInLeft","slideInRight", "hinge","jackInTheBox");
        $out = array("fadeOut","fadeOutDown","fadeOutDownBig","fadeOutLeft","fadeOutLeftBig","fadeOutRight","fadeOutRightBig","fadeOutUp","fadeOutUpBig","rollOut","flipOutX","flipOutY","rotateOut","rotateOutDownLeft","rotateOutDownRight","rotateOutUpLeft","rotateOutUpRight","zoomOut","zoomOutDown","zoomOutLeft","zoomOutRight","zoomOutUp","bounceOut","bounceOutDown","bounceOutLeft","bounceOutRight","bounceOutUp","slideOutUp","slideOutDown","slideOutLeft","slideOutRight");
        return $isOut ? self::arrayCombine($out, 1) : self::arrayCombine($in, 1);
    }

    /**
     * ARROW Buttons
     */
    public static function buttonArrows()
    {
        return array(""=>"None","plus,minus"=>"&#xefc2; &#xef9a;","arrow-right,arrow-down"=>"&#xea5d; &#xea5b;","block-right,block-down"=>"&#xea61; &#xea5f;","bubble-right,bubble-down"=>"&#xea65; &#xea63;","caret-right,caret-down"=>"&#xea69; &#xea67;","circled-right,circled-down"=>"&#xea6d; &#xea6b;","curved-right,curved-down"=>"&#xea75; &#xea73;","dotted-right,dotted-down"=>"&#xea79; &#xea77;","hand-right,hand-down"=>"&#xea8c; &#xea7e;","hand-drawn-right,hand-drawn-down"=>"&#xea88; &#xea86;","line-block-right,line-block-down"=>"&#xea90; &#xea8e;","long-arrow-right,long-arrow-down"=>"&#xea94; &#xea92;","rounded-right,rounded-down"=>"&#xeaa0; &#xea99;","scroll-long-right,scroll-long-down"=>"&#xeaae; &#xeaac;","simple-right,simple-down"=>"&#xeab8; &#xeab2;","square-right,square-down"=>"&#xeabc; &#xeaba;","stylish-right,stylish-down"=>"&#xeac0; &#xeabe;","swoosh-right,swoosh-down"=>"&#xeac4; &#xeac2;","thin-right,thin-down"=>"&#xeac9; &#xeac8;");
    }

    public static function arrayCombine($array = [], $default = false)
    {
        $ac = array_combine($array, $array);
        return (bool)$default ? [''=>"Default"]+$ac : $ac;
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
     * SET LANGEAGE FOR LABEL FORM
     */
    public static function l($label = '', $desc = false)
    {
        return Text::_('BAEXT_'.$label).((bool)$desc ? '<i class="ba-tip" title="'.Text::_('BAEXT_'.$label.'_DESC').'">?</i>' : '');
    }

    /**
     * Get Authors for joomla articles
     */
    public static function getAuthors()
    {
        // Create a new query object.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
            ->from('#__users AS u')
            ->join('INNER', '#__content AS c ON c.created_by = u.id')
            ->group('u.id, u.name')
            ->order('u.name');

        // Setup the query
        $db->setQuery($query);

        // Return the result
        return $db->loadObjectList();
    }
}
