<?php

class iUtils {
    public static function INPUT($input=null,$name=false){
        $input===null && $input = file_get_contents("php://input");
        $name===null && self::LOG($input,'input');

        if ($input){
            if(strpos($input,'<xml>')!==false){
                $data = self::xmlToArray($input);
            }else{
                $data = json_decode($input,true);
                if(empty($data) && strpos($input,'&')!==false){
                    parse_str($input, $data);
                }
            }
            iSecurity::_addslashes($data);
            iWAF::check_data($data);
            return $data;
        }else{
            return false;
        }
    }

    public static function LOG($output=null,$name='debug'){
        if(iPHP_DEBUG){
            if($output==='RAW'){
                $output = file_get_contents("php://input");
            }
            $sub = substr(sha1(md5(iPHP_KEY)), 8,16);
            is_array($output) && $output = var_export($output,true);
            iFS::write(iPHP_APP_CACHE.'/'.$name.'.'.$sub.'.log',$output."\n",1,'ab+');
        }
    }
    /**
     * 将xml转为array
     * @param string $xml
     * @return array|false
     */
    public static function xmlToArray($xml){
        if (!$xml) {
            return false;
        }

        // 检查xml是否合法
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $xml, true)) {
            xml_parser_free($xml_parser);
            return false;
        }

        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $data;
    }
    /**
     * 输出xml字符
     * @param array $values
     * @return string|bool
     **/
    public static function arrayToXml($values)
    {
        if (!is_array($values) || count($values) <= 0) {
            return false;
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml.="<".$key.">".$val."</".$key.">";
            } else {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
}
