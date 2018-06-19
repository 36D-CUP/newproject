<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;

Class Allow extends Controller
{
	public $imgPath = DS.'uploads'.DS;		//普通上传的图片路径
	public $aid = '1';

	protected function _initialize()
	{

	}

}
