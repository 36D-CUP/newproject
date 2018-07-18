<?php
namespace app\admin\controller;

use app\admin\controller\Allow;
use think\Db;

Class Goodsmeal extends Allow
{

	//商品列表
	public function getGoodsmeal()
	{
		//分页
		$pro = input('pro')==""?1:input('pro');							//获取分页传过来的当前页
		$showNum = input('data_num')?input('data_num'):'5';				//设定每一个显示的条数
		$str = (($pro-1)*$showNum).",".$showNum;						//分页

		$s_val  = input('search_value');								//获取搜索传过来的值
		$s_ty   = input('search_type');    								//获取搜索传过来的字段名
		$s_si   = input('search_size');									//判断是否搜索大小写
		$s_type = $s_val?input('type'):'其他';							//获取搜索传过来的搜索类型

		$table = 'n_goods_meal';
		switch($s_type){
			case 1:
				$num  = Db::table($table)->where($s_ty,'like',"%".$s_val."%")->count();							//数量
				$data = Db::table($table)->where($s_ty,'like',"%".$s_val."%")->limit($str)->select();			//获取所有数据
			break;
			default:
				$num  = Db::table($table)->count();												//数量
				$data = Db::table($table)->limit($str)->select();								//获取所有数据
			break;
		}


		//分页
		$arr['page'] = ceil($num/$showNum);//获取条数

		$arr['row']        = $data;																			//列表数据
		$arr['s_ty']       = $s_ty;																			//搜索保留值
		$arr['s_val']      = $s_val;																		//搜索保留值
		$arr['data_num']   = $showNum;																		//搜索保留值
		$arr['table']      = $table;																	//模板上使用
		$arr['tables']     = 'goodsmeal';																		//模板上使用
		$arr['empty']      = "<tr><td colspan='7' style='text-align:center'>没有任何数据</td></tr>";		//显示条数
		$arr['title']	   = '商品管理';
		$arr['title_txt']  = '1级规格列表';
		return $this->fetch('Goodsmeal/goodsmeal',$arr);
	}


	//添加与修改页
	public function getGoodsadd()
	{
		$id = input('id');

		//判定是否添加
		$table = 'n_goods';
		if(empty($id)){
			$arr['title_txt']  = '管理员添加';
			$arr['row']['sort'] = 1;
		}else{
			$arr['title_txt']  = '管理员修改';
			$arr['row']        = Db::table($table)->where('id',$id)->find();		//找出用户

			$old_img['img_surface_name'] = $table;										//组合图片条件
			$old_img['img_surface_id']   = $id;
			$img = Db::table('n_img')->where($old_img)->select();  					//查找图片

			if(!empty($img)){
				for($i = 0 ; $i<count($img) ; $i++){
					$arr['row']['img'][] = $this->imgPath.$img[$i]['img_surface_name'].DS.$img[$i]['img_path'];
					$arr['row']['imgpath'][] = $img[$i]['img_path'];
				}
			}
		}
		$arr['table']      = 'n_goods';																	//模板上使用
		$arr['tables']     = 'goods';																		//模板上使用
		$arr['title']	   = '管理员管理';
		return $this->fetch('goods/goodsadd',$arr);
	}


	//添加与修改功能
	public function postGoodsdoadd()
	{
		$id 						 = input('id');				//对应id
		$data['name']  				 = input('name');			//商品名称
		$data['price'] 				 = input('price');			//单价
		$data['introduce'] 			 = input('introduce');		//介绍
		$data['details']  			 = input('content');		//详情
		$data['sort']  				 = input('sort');			//排序
		$data['aid']  				 = $aid;					//管理员id

		if(!empty(input('discountprice'))){
			$data['discountprice']  =input('discountprice');    //优惠价
		}


		$files 				 = request()->file('imgs');
		$table 				 = 'n_goods';
		
		//判断是否修改或添加
		if(!empty($id)){
	        $bool = Db::table($table)->where('id',$id)->update($data);			//更新数据

			upload_file($files , $table , $id , 1);								//操作图片

		}else{
		    $bool = Db::table($table)->insertGetId($data);						//插入数据

			upload_file($files , $table , $bool , 1);						    //操作图片
		}

		echo $id==""?'添加成功':'修改成功';
	}


	//单击修改状态
	public function postClick_updata_status()
	{
		$id  	= input('id');					//获取ID
		$val 	= input('val');					//获取新值
		$key	= input('key');					//获取字段名
		$table  = input('table');			    //获取表名;

		$bool = Click_updata_status($id,$val,$key,$table);
		echo $bool;
	}

	//单击删除
	public function postClick_delete()
	{
		$id    = input('id');					//id
		$table = input('table');				//表名

		//删除图片
		delete_file($table , $id);
		//删除数据
		$bool = Db::table($table)->where('id',$id)->delete();

		echo $bool?"1":'no';
	}

	//删除图片
	public function postThisImgdel()
	{
		$data['img_surface_name'] = input('name');
		$data['img_surface_id']   = input('id');
		$data['img_path']         = input('url');
		@unlink(ROOT_PATH.'public'.DS.'uploads'.DS.$data['img_surface_name'].DS.$data['img_path']);
		$img = Db::table('n_img')->where($data)->delete();

		if($img){
			echo "1";
		}
	}

	//双击修改名字
	public function postDouble_updata_txt()
	{
		$id    = input('id');					//获取ID
		$val   = input('new_txt');				//获取新值
		$key   = input('name');					//获取字段名
		$table = input('table');				//表

		$bool = Db::table($table)->where('id',$id)->update([$key  => $val]);
		echo $bool?'1':'2';
	}
}
