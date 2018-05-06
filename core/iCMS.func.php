<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

function autoformat($html){
    $html = stripslashes($html);
    $html = preg_replace(array(
    '@on(\w+)=(["\']?)+\\1@is','@style=(["|\']?)+\\1@is',
    '@<script[^>]*>.*?</script>@is','@<style[^>]*>.*?</style>@is',

    '@<br[^>]*>@is',
    '@<div[^>]*>(.*?)</div>@is','@<p[^>]*>(.*?)</p>@is',
    '@<b[^>]*>(.*?)</b>@is','@<strong[^>]*>(.*?)</strong>@is',
    '@<h([1-6])[^>]*>(.*?)</h([1-6])>@is',
    '@<img[^>]+src=(["\']?)(.*?)\\1[^>]*?>@is',
    ),array('','','','',
    "\n[br]\n",
    "$1\n","$1\n",
    "[b]$1[/b]","[b]$1[/b]",
    "[h$1]$2[/h$1]",
    "\n[img]$2[/img]\n",
    ),$html);

    if (stripos($html,'<embed') !== false){
        preg_match_all("@<embed[^>]*>@is", $html, $embed_match);
        foreach ((array)$embed_match[0] as $key => $value) {
            preg_match("@.*?src\s*=[\"|'|](.*?)[\"|'|]@is", $value, $src_match);
            preg_match("@.*?class\s*=[\"|'|](.*?)[\"|'|]@is", $value, $class_match);
            preg_match("@.*?width\s*=[\"|'|](\d+)[\"|'|]@is", $value, $width_match);
            preg_match("@.*?height\s*=[\"|'|](\d+)[\"|'|]@is", $value, $height_match);
            $embed_width = $width_match[1];
            $embed_height = $height_match[1];
            if($class_match[1]=='edui-faked-music'){
                empty($embed_width) && $embed_width = "400";
                empty($embed_height) && $embed_height = "95";
                $html = str_replace($value,'[music='.$embed_width.','.$embed_height.']'.$src_match[1].'[/music]',$html);
            }else{
                empty($embed_width) && $embed_width = "500";
                empty($embed_height) && $embed_height = "450";
                $html = str_replace($value,'[video='.$embed_width.','.$embed_height.']'.$src_match[1].'[/video]',$html);
            }
        }
    }
    $html = str_replace(array("&nbsp;","　"),'',$html);
    $html = preg_replace('@<[/\!]*?[^<>]*?>@is','',$html);
    $html = ubb2html($html);
    $html = autoclean($html);
    return $html;
}
function ubb2html($content){
    return preg_replace(array(
    '@\[br\]@is',
    '@\[img\](.*?)\[/img\]@is',
    '@\[b\](.*?)\[/b\]@is',
    '@\[h([1-6])\](.*?)\[/h([1-6])\]@is',
    '@\[url=([^\]]+)\](.*?)\[/url\]@is',
    '@\[url=([^\]|#]+)\](.*?)\[/url\]@is',
    '@\[music=(\d+),(\d+)\](.*?)\[/music\]@is',
    '@\[video=(\d+),(\d+)\](.*?)\[/video\]@is',
    ),array(
    '<br />',
    '<img src="$1" />',
    '<strong>$1</strong>',
    '<h$1>$2</h$1>',
    '<a target="_blank" href="$1">$2</a>',
    '$2',
    '<embed type="application/x-shockwave-flash" class="edui-faked-music" pluginspage="http://www.macromedia.com/go/getflashplayer" src="$3" width="$1" height="$2" wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true"/>',
    '<embed type="application/x-shockwave-flash" class="edui-faked-video" pluginspage="http://www.macromedia.com/go/getflashplayer" src="$3" width="$1" height="$2" wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true"/>'
    ),$content);
}
function autoclean($html){
    $elArray = explode("\n",$html);
    $elArray = array_map("trim", $elArray);
    $elArray = array_filter($elArray);
    if(empty($elArray)){
        return false;
    }

    $stack     = array();
    $htmlArray = array();
    foreach($elArray as $hkey=>$el){
        $el = preg_replace('@<img\ssrc=""\s/>@is','',$el);
        $el = trim($el);
        if($el===''){
            continue;
        }
        if($el=="#--iCMS.PageBreak--#"){
            $htmlArray[$hkey] = $el;
            continue;
        }
        if($el=='<br />'){
            $stack['br']++;
            if($stack['br']===1){
                $htmlArray[$hkey] = '<p><br /></p>';
            }
            continue;
        }
        $stack['br'] = 0;
        if(preg_match('@^<[/]*(\w+)>$@is', $el)){
            $stack['el']++;
            if (stripos($ek,'</') !== false){
                $stack['el'] = 0;
            }
            $htmlArray[$hkey] = $el;
            continue;
        }
        if(preg_match('@^<(\w+)>\s*</\\1>$@is', $el)){
            continue;
        }
        $el = preg_replace(array(
            '@(<(\w+)>\s*</\\2>\n*)*@is',
            '@(<[/]*(\w+)></\\1>\n*)*@is',
            '@(<(\w+)>\s*</\\1>\n*)*@is',
        ),'',$el);

        if($el){
            if($stack['el']===1){
                $htmlArray[$hkey] = $el;
            }else{
                $htmlArray[$hkey] = '<p>'.$el.'</p>';
            }
        }
    }
    reset ($htmlArray);
    $html = implode('',(array)$htmlArray);
    return $html;
}
function cnum($subject){
    $searchList = array(
        array('ⅰ','ⅱ','ⅲ','ⅳ','ⅴ','ⅵ','ⅶ','ⅷ','ⅸ','ⅹ'),
        array('㈠','㈡','㈢','㈣','㈤','㈥','㈦','㈧','㈨','㈩'),
        array('①','②','③','④','⑤','⑥','⑦','⑧','⑨','⑩'),
        array('一','二','三','四','五','六','七','八','九','十'),
        array('零','壹','贰','叁','肆','伍','陆','柒','捌','玖','拾'),
        array('Ⅰ','Ⅱ','Ⅲ','Ⅳ','Ⅴ','Ⅵ','Ⅶ','Ⅷ','Ⅸ','Ⅹ','Ⅺ','Ⅻ'),
        array('⑴','⑵','⑶','⑷','⑸','⑹','⑺','⑻','⑼','⑽','⑾','⑿','⒀','⒁','⒂','⒃','⒄','⒅','⒆','⒇'),
        array('⒈','⒉','⒊','⒋','⒌','⒍','⒎','⒏','⒐','⒑','⒒','⒓','⒔','⒕','⒖','⒗','⒘','⒙','⒚','⒛')
    );
    $replace = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);
    foreach ($searchList as $key => $search) {
        $subject = str_replace($search, $replace, $subject);
    }

    return $subject;
}
function archive_date($date){
    $limit = time() - $date;
    if($limit <= 86400){
        return '今天';
    }else if($limit > 86400 && $limit<=172800){
        return '昨天';
    }else{
        //return get_date($date,'dm');
        return '<span class="day">'.get_date($date,'d').'</span><span class="mon">'.get_date($date,'m').'月</span>';
    }
}
function key2num($resource){
    $sort_key = 0;
    // $_release = array();
    foreach ((array)$resource as $key => $value) {
        $_resource[$sort_key]= $value;
        // $_release[$sort_key] = $value['items'][0];
        ++$sort_key;
    }
    return $_resource;
}

function text2link($text=null){
    if (strpos($text, '||') !== false) {
        list($title, $url) = explode('||', $text);
        return '<a href="' . $url . '" target="_blank">' . $title . '</a>';
    }else{
        return $text;
    }
}
function metadata($data=null) {
    $mdArray = array();
    $data    = json_decode($data,true);
    foreach((array)$data as $key => $value){
        $mdArray[$key] = $value;
    }
    return $mdArray;
}
function put_php_file($path,$data){
    $data ="<?php defined('iPHP') OR exit('What are you doing?');?>\n".$data;
    file_put_contents($path, $data);
}
function get_php_file($path){
    if(is_file($path)){
        $json = file_get_contents($path);
        $json = get_php_content($json);
    }
    return $json;
}
function get_php_content($content){
    $content = str_replace("<?php defined('iPHP') OR exit('What are you doing?');?>\n", '', $content);
    return $content;
}
function check_priv($p,$priv){
    return is_array($p)?array_intersect((array)$p,(array)$priv):in_array((string)$p,(array)$priv);
}

function orderby_option($array,$by="DESC"){
    $opt = '';
    $byText = ($by=="ASC"?"升序":"降序");
    foreach ($array as $key => $value) {
        $opt.='<option value="'.$key.' '.$by.'">'.$value.'['.$byText.']</option>';
    }
    return $opt;
}
function get_orderby($array=null){
    empty($array) && $array = array('id' =>"ID");

    list($order,$by) = explode(' ', $_GET['orderby']);

    if($by!='DESC' && $by!='ASC'){
        $by ='DESC';
    }

    $default = array_keys($array);
    $orderby = isset($array[$order])?' `'.$order.'` '.$by:$default[0]." DESC";
    $option = array(
        'DESC' => orderby_option($array,"DESC"),
        'ASC'  => orderby_option($array,"ASC")
    );
    return array($orderby,$option);
    //
    // $obj = new stdClass();
    // $obj->sql = $orderby;
    // $obj->option = $option;
    // return $obj;
}
