<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

class apps_hook {

    /**
     * 获取带钩子APP
     * @param  [type] $app [description]
     * @return [type]      [description]
     */
    public static function app_select($app=null) {
        foreach (apps::get_array(array("!table"=>0)) as $key => $value) {
            list($path,$obj_name)= apps::get_path($value['app'],'app',true);
            if(is_file($path) && method_exists($obj_name,'hooked')){
                $option[]='<option '.($app==$value['app']?' selected="selected"':'').' value="'.$value['app'].'">'.$value['app'].':'.$value['name'].'</option>';
            }
        }
        return implode('', (array)$option);
    }
    /**
     * 获取钩子APP字段 select
     */
    public static function app_fields_select() {
        foreach (apps::get_array(array("!table"=>0)) as $a => $app) {
            $option = array();
            list($path,$obj_name)= apps::get_path($app['app'],'app',true);
            if($app['table'] && is_file($path) && method_exists($obj_name,'hooked')){
                foreach ((array)$app['table'] as $key => $table) {
                    $tbn = $table['table'];
                    if(iDB::check_table($tbn,false)){
                        $option[] = '<optgroup label="'.$table['label'].'表">';
                        $orig_fields  = apps_db::fields($tbn);
                        foreach ((array)$orig_fields as $field => $value) {
                            $option[]='<option value="'.$field.'">'.($value['comment']?$value['comment'].' ('.$field.')':$field).'</option>';
                        }
                        $option[] = '</optgroup>';
                    }
                }
                if($option){
                    echo '<select id="app_'.$app['app'].'_select" class="hide">'.implode('', (array)$option).'</select>';
                }
            }
        }
    }

    /**
     * 获取APP 插件等可用钩子
     * @return [type] [description]
     */
    public static function app_method() {
        $option = '';
        foreach (apps::get_array(array("status"=>'1')) as $key => $value) {
            list($path,$obj_name)= apps::get_path($value['app'],'app',true);
            if(is_file($path)){
                $option.= self::app_hook_method($obj_name);
            }
        }
        //plugins
        $plugin = apps::get('plugin','app');
        if($plugin['status'])foreach (glob(iPHP_APP_DIR."/plugin/plugin_*.class.php") as $filename) {
            $path     = str_replace(iPHP_APP_DIR.'/','',$filename);
            $obj_name = basename($filename,'.class.php');
            $option  .= self::app_hook_method($obj_name);
        }
        return $option;
    }
    /**
     * 获取app钩子
     * @param  [type] $obj_name [description]
     * @return [type]           [description]
     */
    public static function app_hook_method($obj_name=null) {
        $class_methods = get_class_methods ($obj_name);
        foreach ($class_methods as $key => $method) {
            if(stripos($method, 'HOOK_') !== false||$method=="HOOK"){
                $doc = self::get_doc($obj_name,$method);
                if($doc){
                    $title = $doc[0];
                }else{
                    $title = $obj_name.'::'.$method;
                }
                $option[]='<option value="'.$obj_name.'::'.$method.'">'.$title.'</option>';
            }
        }
        return implode('', (array)$option);
    }
    /**
     * 获取注释
     * @param  [type] $class  [description]
     * @param  [type] $method [description]
     * @return [type]         [description]
     */
    public static function get_doc($class,$method) {
        $reflection = new ReflectionMethod($class,$method);
        $docblockr  = $reflection->getDocComment();
        preg_match_all ( '#^\s*\s(.+)\n#m', $docblockr, $lines );
        $doc = array();
        foreach ($lines[1] as $key => $line) {
            $doc[$key]= self::parseLine($line);
        }
        return $doc;
    }
    /**
     * 解析注释
     * @param  [type] $line [description]
     * @return [type]       [description]
     */
    private static function parseLine($line) {
        // trim the whitespace from the line
        $line = trim ( $line );

        if (empty ( $line ))
            return null; // Empty line

        if (strpos ( $line, '@' ) !== false) {
            preg_match ('#\*\s@(\w+)\s+\[(\w+)\]\s(.+)\s\[(.+)\]#is', $line, $match );
            $rs = array(
                'desc'=>$match[4],
                'type'=>$match[1],
                'var' => '('.$match[2].')'.$match[3]
            );
        }else{
            preg_match ('#^\*\s\[(.+)\]#is',$line,$match);
            $rs = $match[1];
        }
        if($rs){
            return $rs;
        }
    }
    public static function get_app_priv(){
        $privArray = array();
        foreach (apps::get_array(array("status"=>'1')) as $key => $value) {
            list($path,$obj_name)= apps::get_path($value['app'],'admincp',true);
            $apriv = array();
            if(is_file($path)||$value['apptype']=="2"||$value['app']=="admincp"){
                if($value['apptype']=="2"){
                  $obj_name = 'contentAdmincp';
                }
                if($value['app']=="admincp"){
                    $obj_name = 'admincpApp';
                }
                $apriv = self::method_priv($obj_name,$value,$privArray);
            }
            $html.= self::app_priv_html($apriv,$value);

            // if($value['apptype']=="2"){
            //     $obj_name = 'categoryAdmincp';
            //     $apriv = self::method_priv($obj_name,$value);
            //     $html.= self::app_priv_html($apriv,$value);
            // }
        }
        iCache::set('app/priv',$privArray,0);
        return $html;
    }

    public static function app_priv_html($apriv,$value){
        if($apriv){
            ksort($apriv);
            $html= '<tr id="'.$value['app'].'_apriv">';
            $html.= '<td><input type="checkbox" class="checkAll checkbox" data-target="#'.$value['app'].'_apriv"/></td>';
            $html.= '<td>'.$value['id'].'</td>';
            $html.= '<td>'.$value['app'].'</td>';
            $html.= '<td>'.$value['name'].'</td>';
            $html.= '<td>';
            $html.= '<div class="input-prepend input-append">';
            $html.= implode('', $apriv);
            $html.= '</div>';
            $html.= '</td>';
            $html.= '<td>';
            $html.= '<div class="input-prepend input-append">';
            $html.= '  <span class="add-on"><input type="checkbox" name="config[apriv][]" value="'.$value['app'].'.VIEW" /> 查看所有'.$value['title'].'</span>';
            $html.= '  <span class="add-on"><input type="checkbox" name="config[apriv][]" value="'.$value['app'].'.EDIT" /> 编辑所有'.$value['title'].'</span>';
            $html.= '  <span class="add-on"><input type="checkbox" name="config[apriv][]" value="'.$value['app'].'.DELETE" /> 删除所有'.$value['title'].'</span>';
            $html.= '</div>';
            $html.= '</td>';
            $html.= '</tr>';
        }
        // echo '<div class="input-prepend input-append" id="'.$value['app'].'_apriv">';
        // echo '<span class="add-on"><i class="fa fa-folder"></i> '.$value['name'].'权限 <input type="checkbox" class="checkAll checkbox" data-target="#'.$value['app'].'_apriv"/></span>';
        // echo '<span class="add-on">::</span>';
        // echo implode('', $apriv);
        // // echo '<select name="config[apriv][]" id="apriv" multiple="multiple" class="chosen-select span6" data-placeholder="请设置应用权限">';
        // // echo '</select>';
        // echo '</div><div class="clearfloat mb10"></div>';
        return $html;
    }
    public static function method_priv($obj_name,$value,&$privArray){
        $docMap = array(
            'iCMS'        => "{title}列表",
            'manage'      => "{title}列表",
            'add'         => "添加{title}",
            'save'        => "保存{title}",
            'update'      => "更新操作",
            'batch'       => "批量操作",
            'cache'       => "更新{title}缓存",
            'copy'        => "克隆{title}",
            'del'         => "删除{title}",
            'ajaxtree'    => "ajax树状数据",
            'inbox'       => "草稿箱",
            'trash'       => "回收站",
            'examine'     => "审核{title}",
            'off'         => "淘汰{title}",
            'config'      => "{title}配置",
            'save_config' => "保存{title}配置",
            'user'        => "用户{title}列表",
            'updateorder' => "更新排序",
        );
        $class_methods = get_class_methods ($obj_name);
        foreach ($class_methods as $key => $method) {
            if(stripos($method, 'do_') !== false){
                $doc = apps_hook::get_doc($obj_name,$method);
                if($doc){
                    $title = $doc[0];
                    if(stripos($title, '[NOPRIV]') !== false){
                      continue;
                    }
                }else{
                    $title = $method;
                }
                $do = str_replace('do_', '', $method);
                if($docMap[$do] && empty($doc)){
                  $title = str_replace('{title}', $value['title'],$docMap[$do]);
                }
                $url = __ADMINCP__.'='.$value['app'].($do != 'iCMS' ? '&do=' . $do : '');
                // $apriv[$do]='<option value="'.$obj_name.'::'.$method.'">'.$title.'</option>';
                $apriv[$do]='<span class="add-on tip" title="网址:'.$url.'"><input type="checkbox" name="config[apriv][]" value="'.$value['app'].'.'.$do.'"/> '.$title.'</span>';
                $privArray[$url] = $value['name'].'('.$title.')';
            }
        }
        return $apriv;
    }
}

