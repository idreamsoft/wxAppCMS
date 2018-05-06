<?php
/**
 * Template Lite plugin converted from iPHP
 * @package iPHP
 * @subpackage plugins
 */


/**
 * iPHP explode modifier plugin
 *
 * Type:     modifier<br>
 * Name:     explode<br>
 * Date:     11:28 2008-10-26
 * Purpose:  Split a string by string
 * Input:    string to catenate
 * Example:  {$var|explode:",}
 * @author   coolmoo
 * @version 1.0
 * @param string
 * @param string
 * @return string
 */
function tpl_modifier_explode($string, $delimiter){
    return explode($delimiter,$string);
}

