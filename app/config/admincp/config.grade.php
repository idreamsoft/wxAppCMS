<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
defined('iPHP') OR exit('What are you doing?');
?>

        <div class="input-prepend"> <span class="add-on">sphinx服务器</span>
            <input type="text" name="config[sphinx][host]" class="span3" id="sphinx_host" value="<?php echo $config['sphinx']['host'] ; ?>" />
        </div>
        <span class="help-inline">UNIX SOCK:unix:///tmp/sphinx.sock<br />
            HOST:127.0.0.1:9312</span>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend"> <span class="add-on">sphinx 索引</span>
            <input type="text" name="config[sphinx][index]" class="span3" id="sphinx_index" value="<?php echo $config['sphinx']['index'] ; ?>" />
        </div>
        <span class="help-inline"></span>
        <div class="clearfloat mb10"></div>
        <h3 class="title">sphinx 配置示例</h3>
        <pre>
source iCMS_article
{
  type    = mysql
  sql_host  = localhost
  sql_user  = root
  sql_pass  = 123456
  sql_db    = iCMS
  sql_port  = 3306  # optional, default is 3306
  sql_query_pre =  SET NAMES utf8
  sql_query_pre   = REPLACE INTO icms_sph_counter SELECT 1, MAX(id) FROM icms_article

  sql_query = SELECT a.id, a.cid,a.userid, a.comments, a.pubdate,a.hits_today, a.hits_yday, a.hits_week, a.hits_month,a.hits, a.haspic, a.title, a.keywords, a.tags, a.status FROM icms_article a,icms_category c WHERE a.cid=c.cid AND a.status ='1' AND a.id<=( SELECT max_doc_id FROM icms_sph_counter WHERE counter_id=1 )
  sql_attr_uint   = cid
  sql_attr_uint   = userid
  sql_attr_uint   = comments
  sql_attr_uint   = hits
  sql_attr_uint   = hits_week
  sql_attr_uint   = hits_month
  sql_attr_uint   = status
  sql_attr_timestamp  = pubdate
  sql_attr_bool   = haspic

  sql_ranged_throttle = 0
  sql_query_info    = SELECT * FROM icms_article WHERE id=$id

}
source iCMS_article_delta : iCMS_article
{
  sql_query_pre =  SET NAMES utf8
  sql_query = SELECT a.id, a.cid,a.userid, a.comments, a.pubdate,a.hits_today, a.hits_yday, a.hits_week, a.hits_month,a.hits, a.haspic, a.title, a.keywords, a.tags, a.status FROM icms_article a,icms_category c WHERE a.cid=c.cid AND a.status ='1' AND a.id>( SELECT max_doc_id FROM icms_sph_counter WHERE counter_id=1 )
}
index iCMS_article
{
  source      = iCMS_article
  path      = /var/sphinx/iCMS_article
        docinfo                 = extern
        mlock                   = 0
        morphology              = none
        min_word_len            = 1
        charset_type            = utf-8
        min_prefix_len          = 0
        html_strip              = 1
        charset_table           = 0..9, A..Z->a..z, _, a..z, U+410..U+42F->U+430..U+44F, U+430..U+44F
        ngram_len               = 1
        ngram_chars             = U+3000..U+2FA1F
}
index iCMS_article_delta : iCMS_article
{
  source  = iCMS_article_delta
  path  = /var/sphinx/iCMS_article_delta
}
##sphinx使用问题,请自行Google上百度一下
          </pre>

