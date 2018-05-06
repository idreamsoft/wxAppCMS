<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class articleApp extends appsApp {
	public function __construct() {
		parent::__construct('article');
	}
	public function article($fvar,$page = 1,$field='id', $tpl = true) {
        $article = $this->get_data($fvar,$field);
        if ($article === false) return false;
        $id = $article['id'];

		if ($article['chapter']) {
			$all = iDB::all("
				SELECT `id`,`subtitle`
				FROM `#iCMS@__article_data`
				WHERE aid='" . (int) $id . "';
			", ARRAY_A);
			if($all)foreach ($all as $akey => $value) {
				$article_data[] = $value;
			}
			unset($all);
			ksort($article_data);
		} else {
			$article_data = $this->data($id,'body,subtitle');
		}

		$vars = array(
			'tag'  => true,
			'user' => true,
		);
		$article = $this->value($article, $article_data, $vars, $page, $tpl);
		unset($article_data);
		if ($article === false) {
			return false;
		}
		self::custom_data($article,$vars);
		self::hooked($article);
		return self::render($article,$tpl);
	}
	public static function value($article, $data = "", $vars = array(), $page = 1, $tpl = false) {
		$category = array();
		$process = self::process($tpl,$category,$article);
		if ($process === false) return false;

		if ($data) {
			$pkey = intval($page - 1);
			if ($article['chapter']) {
				$chapterArray = $data;
				$count = count($chapterArray);
				$adid = $chapterArray[$pkey]['id'];
				$data = iDB::row("
					SELECT body,subtitle
					FROM `#iCMS@__article_data`
					WHERE aid='" . (int) $article['id'] . "'
					AND id='" . (int) $adid . "'
				", ARRAY_A);
			}


			$article['pics'] = filesApp::get_content_pics($data['body'],$pic_array);

			if ($article['chapter']) {
				$article['body'] = $data['body'];
			} else {
				$body = explode('#--iCMS.PageBreak--#', $data['body']);
				$count = count($body);
				$article['body'] = $body[$pkey];
				unset($body);
			}

			$article['subtitle'] = $data['subtitle'];
			unset($data);
			$total = $count + intval(self::$config['pageno_incr']);
			$article['page'] = iUI::page_content($article,$page,$total,$count,$category['mode'],$chapterArray);
			$article['PAGES'] = $article['page']['PAGES'];unset($article['page']['PAGES']);
			is_array($article['page']['next'])&& $next_url = $article['page']['next']['url'];
			$pic_array[0] && $article['body'] = self::body_pics_page($pic_array,$article,$page,$total,$next_url);
		}

		$vars['tag'] && tagApp::get_array($article,$category['name'],'tags');

        apps_common::init($article,'article',$vars);
        apps_common::link();
        apps_common::text2link();
        apps_common::user();
        apps_common::comment();
        apps_common::pic();
        apps_common::hits();
        apps_common::param();

		return $article;
	}
	//保留静态方法 articleFunc 中调用
    public static function data($ids=0,$fields=null){
        return apps_common::data($ids,'article','aid',$fields);
    }
}
