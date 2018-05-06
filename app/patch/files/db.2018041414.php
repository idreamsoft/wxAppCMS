<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
  $storeArray = iDB::all("SELECT * FROM `#iCMS@__apps_store` where `data`=''");
  $json = iHttp::post(
    apps_store::STORE_URL.'/sys_upgrade?host='.$_SERVER['HTTP_HOST'],
    array('data'=>serialize($storeArray))
  );
  if($json){
    $data = json_decode($json,true);
    if($data)foreach ($data as $sid => $value) {
      iDB::update('apps_store',array('data'=>$value),array('sid'=>$sid));
    }
  }
  return $msg;
});

