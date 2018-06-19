<?php
namespace app\admin\controller;

use app\admin\controller\Allow;
use think\Db;

Class Goods extends Allow
{

	//商品列表
	public function getGoods()
	{
		//分页
		$pro = input('pro')==""?1:input('pro');							//获取分页传过来的当前页
		$showNum = input('data_num')?input('data_num'):'5';				//设定每一个显示的条数
		$str = (($pro-1)*$showNum).",".$showNum;						//分页

		$s_val  = input('search_value');								//获取搜索传过来的值
		$s_ty   = input('search_type');    								//获取搜索传过来的字段名
		$s_si   = input('search_size');									//判断是否搜索大小写
		$s_type = $s_val?input('type'):'其他';							//获取搜索传过来的搜索类型

		$table = 'n_goods';
		switch($s_type){
			case 1:
				$num  = Db::table($table)->where($s_ty,'like',"%".$s_val."%")->where('aid',$this->aid)->count();							//数量
				$data = Db::table($table)->where($s_ty,'like',"%".$s_val."%")->where('aid',$this->aid)->limit($str)->select();			//获取所有数据
			break;
			default:
				$num  = Db::table($table)->where('aid',$this->aid)->count();												//数量
				$data = Db::table($table)->where('aid',$this->aid)->limit($str)->select();								//获取所有数据
			break;
		}

		//组合数据
		$img  = Db::table('n_img')->where('img_surface_name',$table)->select();					//获取所有数据图片
		for($i = 0 ; $i<count($data) ; $i++){
			//组合图片
			for($k = 0 ; $k<count($img) ; $k++){
				if($data[$i]['id'] == $img[$k]['img_surface_id']){
					$data[$i]['img'] = $this->imgPath.$img[$k]['img_surface_name'].DS.$img[$k]['img_path'];
				}
			}
			//组合类别
			$data[$i]['mid'] = Db::table('n_goods_meal')->where('aid',$this->aid)->where('id',$data[$i]['mid'])->find()['name'];
		}

		//分页
		$arr['page'] = ceil($num/$showNum);//获取条数

		$arr['row']        = $data;																			//列表数据
		$arr['s_ty']       = $s_ty;																			//搜索保留值
		$arr['s_val']      = $s_val;																		//搜索保留值
		$arr['data_num']   = $showNum;																		//搜索保留值
		$arr['table']      = $table;																	//模板上使用
		$arr['tables']     = 'goods';																		//模板上使用
		$arr['empty']      = "<tr><td colspan='7' style='text-align:center'>没有任何数据</td></tr>";		//显示条数
		$arr['title']	   = '商品管理';
		$arr['title_txt']  = '商品列表';
		return $this->fetch('Goods/goods',$arr);
	}


	//添加与修改页
	public function getGoodsadd()
	{
		$id = input('id');

		$meal = Db::table('n_goods_meal')->select();

		//判定是否添加
		$table = 'n_goods';
		if(empty($id)){
			$arr['title_txt']  = '管理员添加';
			$arr['row']['sort'] = 1;
		}else{
			$arr['title_txt']  = '管理员修改';
			$arr['row']        = Db::table($table)->where('id',$id)->find();		//找出用户
			$arr['row']['meal']= Db::table('n_goods_meal')->where('aid',$this->aid)->where('id',$arr['row']['mid'])->find()['id'];

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
		$arr['meal']	   = $meal;								//类别

		$arr['table']      = 'n_goods';																	//模板上使用
		$arr['tables']     = 'goods';																		//模板上使用
		$arr['title']	   = '管理员管理';
		return $this->fetch('Goods/goodsadd',$arr);
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
		$data['mid']  				 = input('meal');			//排序
		$data['aid']  				 = $this->aid;					//管理员id
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

	//规格添加页
	public function getProductinfoadd()
	{
        $id = input('id');
        $infos = Db::table('n_goods_info')->where('gid='.$id)->select();

        if(!empty($infos)){

            $infoc = Db::table('n_goods_info_c')->where('iid='.$infos[0]['id'])->select();
        }


		$arr['ids']	   = $id;
		$arr['sel']	   = $infos[0]['id'];
		$arr['row']	   = $infos;
		$arr['rowc']	   = $infoc;
		$arr['title']	   = '商品管理';
		$arr['title_txt']  = '商品类别添加';
		return $this->fetch('Goods/productinfoadd',$arr);
	}
	//删除系列
	public function postInfododel()
	{        
		$id = input('id');

        $bool = Db::table('n_goods_info')->where('id='.$id)->delete();
        if ($bool) {
            echo "1";
        }
    }

	//添加2级规格
	public function postInfosadd()
	{        
        $val  = input('val');
        $name 		= input('name');
        $sort 		= input('sort');
        $append 	= input('append');
        $bqs 	= input()['bqs'];


        $product_info = Db::table('n_goods_info_c')->where('iid='.$val)->select();

        foreach($product_info as $v){
            if(!in_array($v['id'],$bqs)){
                $bool = Db::table('n_goods_info_c')->where('id='.$v['id'])->delete();
                if(!$bool){
                    $no_off = 2;
                }
            }
        }

        if(!empty($name)){
            $info_c['sort'] = $sort;
            $info_c['append'] = $append;
            $info_c['name'] = $name;
            $info_c['iid'] = $val;
            $bool =Db::table('n_goods_info_c')->insert($info_c);
            if(!$bool){
                $no_off = 2;
            }

        }
        if($no_off==1){
           echo "1";
        }
    }

	//添加系列
	public function postInfodoadd()
	{        
        $data['gid'] = input('id');
        $data['name'] = input('name');
        $data['aid'] = $this->aid;
        $bool = Db::table('n_goods_info')->insert($data);
        if ($bool) {
            echo "1";
        }
    }


	//追加2级系列
	public function postInfo_name(){
		 $id = input('id');
        $row = Db::table('n_goods_info_c')->where('iid='.$id)->select();
        echo json_encode($row);
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
