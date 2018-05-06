<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author icmsdev <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
*/
defined('iPHP') OR exit('What are you doing?');

return array(
	'not_found' =>'未找到相关内容<b>%s:%s</b>',
	'!login'    =>'请先登陆！',
	'error'     =>'哎呀呀呀！非常抱歉,居然出错了！<br />请稍候再试试,我们的程序猿正在努力修复中...',
	'clicknext' =>'点击图片进入下一页',
	'first'     =>'已经是第一篇',
	'last'      =>'已经是最后一篇',
	'empty_id'  =>'ID不能为空',
	'!good'     =>'您已经点过赞了啦 ！',
	'good'      =>'谢谢您的赞，我会更加努力的',
	'!bad'      =>'您已经过踩了啦！',
	'bad'       =>'您已经过踩了啦！',

	'page'   =>array(
		'index'        =>'首页',
		'prev'         =>'上一页',
		'next'         =>'下一页',
		'last'         =>'末页',
		'other'        =>'共',
		'unit'         =>'页',
		'list'         =>'篇文章',
		'sql'          =>'条记录',
		'tag'          =>'个标签',
		'comment'      =>'条评论',
		'format_left'  =>'',
		'format_right' =>'',
		'di'           =>'第',
	),
	'report'=>array(
		'empty'   =>'请填写举报的原因！',
		'success' =>'谢谢您的反馈！我们会尽快处理的！',
	),
	'pm'=>array(
		'empty'   =>'请填写私信内容。',
		'success' =>'发送成功！',
		'nofollow'=>'发送失败！您无法给对方发送私信！',
	),
	'favorite'=>array(
		'create_empty'   =>'请输入标题！',
		'create_filter'  =>'您输入的内容中包含被系统屏蔽的字符，请重新填写！',
		'create_max'     =>'最多只能创建10个收藏夹！',
		'create_success' =>'添加成功！',
		'create_failure' =>'添加失败！',
		'update'  =>'更新成功！',
		'url'     =>'URL不能为空',
		'success' =>'收藏成功！',
		'failure' =>'您已经收藏过了！',
		'error' =>'收藏失败！',
	),
	'comment'=> array(
		'empty_id'=>'ID不能为空',
		'close'   =>'评论已关闭！',
		'empty'   =>'请输入内容！',
		'success' =>'感谢您的评论！',
		'examine' =>'您的评论已经提交，请等待管理审核通过后方可显示 ！',
		'!like'   =>'您已经点过赞了啦 ！',
		'like'    =>'谢谢您的赞',
		'filter'  =>'评论内容中包含被系统屏蔽的字符，请重新填写。',
	),
	'seccode'=> array(
		'empty'=>'请输入验证码！',
		'error'=>'验证码不正确！请更换一张再试试。',
	),
	'file'=> array(
		'invaild'=>'非法字符',
		'failure'=>'不允许的文件类型',
	),
	//导航
	'navTag'=>'»',
);
