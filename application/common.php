<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use think\Db;
//----------------------------------------------------ajax操作-----------------------------------

	//单击修改状态
	function Click_updata_status($id,$val,$key,$table)
	{
		if(Db::table($table)->where('id',$id)->update([$key  => $val])){
			return $val;
		}else{
			return '失败';
		}
	}
//----------------------------------------------------ajax操作 end-----------------------------------


//----------------------------------------------------基础操作-----------------------------------

	//文件上传
	/*
		upload_file(文件 , 对应表 , 对应表id , 是否删除原图片);

		files  文件
		table  对应的数据表
		id     对应数据表数据的id
		del    判断是否要删除原图片
				1:将原数据所有图片删除,换上新图片,例如头像,
				2:不做任何删除图片的操作
	 */	
	function upload_file($files , $table , $id , $del=1)
	{
		//判断是否有图片;
		if(empty($files)){
			return false;
		}


		//判断是否删除原图片
    	if($del == '1'){
    		$old_data['img_surface_name'] = $table;
    		$old_data['img_surface_id']   = $id;
    		$old_img  = DB::table('n_img')->where($old_data)->select();    				 		  //找出原图片

    		//删除所有图片,数据
    		if(!empty($old_img)){
	    	    Db::table('n_img')->where($old_data)->delete();  									      //删除所有条件数据
	    		foreach ($old_img as $v) {
		        	@unlink(ROOT_PATH . 'public' . DS . 'uploads' . DS . $table . DS . $v['img_path']);	  //删除原图片
	    		}
    		}

        	//插入新图片
			foreach($files as $file){
    			$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $table);			  //移动图片到指定目录

	        	$data_img['img_path']         = str_replace('\\','/',$info->getSaveName());			  //获取路径,组合条件
	        	$data_img['img_surface_name'] = $table;
    			$data_img['img_surface_id']   = $id;														

	            $bool = Db::table('n_img')->where($old_data)->insert($data_img);					   //插入数据
        	}

        	if($bool){
        		return true;
        	}

		}else if($del == '2'){

		}

	}


	//删除文件
	/*
		delete_file(对应表 , 对应表id , 指定图片img_path字段);

		table  		对应的数据表
		id     		对应数据表数据的id
		img_path    指定图片的img_path字段
						存在	:删除所有图片
						不存在  :删除指定图片
	 */	
	function delete_file($table , $id , $img_path="")
	{
		//判断是否有图片;
		$old_data['img_surface_name'] = $table;
		$old_data['img_surface_id']   = $id;
		$old_img  = DB::table('n_img')->where($old_data)->select();    				 		  //找出原图片
		if(empty($old_img)){
			return false;
		}

		//判断是否删除原图片
    	if(empty($img_path)){
    		//删除所有图片,数据
    	    $bool = Db::table('n_img')->where($old_data)->delete();  								  //删除所有条件数据
    		foreach ($old_img as $v) {
	        	@unlink(ROOT_PATH . 'public' . DS . 'uploads' . DS . $table . DS . $v['img_path']);	  //删除原图片
    		}

        	if($bool){
        		return true;
        	}
		}else{
			$old_data['img_path'] = $img_path;
			$old_path = DB::table('n_img')->where($old_data)->find()['img_path'];
    	    $bool 	  = Db::table('n_img')->where($old_data)->delete();  							  //删除指定条件数据
	        @unlink(ROOT_PATH . 'public' . DS . 'uploads' . DS . $table . DS . $old_path);	 	  	  //删除原图片

        	if($bool){
        		return true;
        	}
		}

	}


//----------------------------------------------------基础操作 end-----------------------------------