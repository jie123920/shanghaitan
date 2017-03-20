<?php
namespace Admin\Controller;
use Admin\Logic\GoodsLogic;
use Think\Page;
class SystemController extends BaseController{
	
	/*
	 * 配置入口
	 */
	public function index()
	{          
		/*配置列表*/
		$group_list = array('shop_info'=>'网站信息','basic'=>'基本设置','sms'=>'短信设置','shopping'=>'购物流程设置','smtp'=>'邮件设置','water'=>'水印设置');		
		$this->assign('group_list',$group_list);
		$inc_type =  I('get.inc_type','shop_info');
		//dump($inc_type);die;
		$this->assign('inc_type',$inc_type);
		$this->assign('config',tpCache($inc_type));//当前配置项
		$this->display($inc_type);
	}
	
	/*
	 * 新增修改配置
	 */
	public function handle()
	{
		$param = I('post.');
		$inc_type = $param['inc_type'];
		unset($param['__hash__']);
		unset($param['inc_type']);
		tpCache($inc_type,$param);
		$this->success("操作成功",U('System/index',array('inc_type'=>$inc_type)));
	}        
        
       /**
        * 自定义导航
        */
    public function navigationList(){
           $model = M("Navigation");
           $count = $model->count();
           $Page  = new Page($count,10);
           $show = $Page->show();
           $navigationList = $model->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
           $this->assign('navigationList',$navigationList);
           $this->assign('show',$show);
           $this->display('navigationList');          
     }
    
    /**
     * 添加修改编辑 前台导航
     */
    public  function addEditNav(){                        
            $model = D("Navigation");            
            if(IS_POST)
            {
                    $model->create();
                    // $model->url = strstr($model->url, 'http') ? $model->url : "http://".$model->url; // 前面自动加上 http://
                    if($_GET['id'])
                        $model->save();
                    else
                        $model->add();
                    
                    $this->success("操作成功!!!",U('Admin/System/navigationList'));               
                    exit;
            }                    
           // 点击过来编辑时                 
           $id = $_GET['id'] ? $_GET['id'] : 0;       
           $navigation = $model->find($id);      
           
           // 系统菜单
          /* $GoodsLogic = new GoodsLogic();
           $cat_list = $GoodsLogic->goods_cat_list();
           $select_option = array();                       
            foreach ($cat_list AS $key => $value)
            {
                    $strpad_count = $value['level']*4;
                    $select_val = U("/Home/Index/goodsList",array('id'=>$key));
                    $select_option[$select_val] = str_pad('',$strpad_count,"-",STR_PAD_LEFT).$value['name'];                                        
            }  */
           $system_nav = M('navigation')->where(array('parent_id'=>0))->select();
           $this->assign('system_nav',$system_nav);
           $this->assign('navigation',$navigation);
           $this->display('_navigation');
    }   
    
    /**
     * 删除前台 自定义 导航
     */
    public function delNav()
    {     
        // 删除导航
        M('Navigation')->where("id = {$_GET['id']}")->delete();   
        $this->success("操作成功!!!",U('Admin/System/navigationList'));
    }
	
    
    public function menu(){
    	$this->assign('tree',$this->tree());
    	$this->display();
    }
    
	public function create_menu(){
		$this->assign('tree',$this->tree());
		$action = I('get.action','add');
		$mod_id = I('get.mod_id',0);
		if($mod_id>0){
			$menu = D('system_module')->where("mod_id=$mod_id")->find();
			$this->assign('menu',$menu);
		}
		$this->assign('pid',$mod_id);		
		$this->assign('tree',$this->tree());
		$this->assign('action',$action);
		$this->display();
	}
	
	public function menuSave(){
		$data = I('post.');
		if($data['action'] == 'add'){
			if($data['mod_id']>0 || $data['parent_id']>0){
				$data['level'] = 2;
				$data['module'] = 'menu';				
			}else{
				$data['level'] = 1;
				$data['module'] = 'top';
			}
			unset($data['mod_id']);
			$r = D('system_module')->add($data);
		}
		if($data['action'] == 'edit'){
			$r = D('system_module')->where('mod_id='.$data['mod_id'])->save($data);
		}

		if($data['action'] == 'del'){
			$res = D('system_module')->where('parent_id='.$data['mod_id'])->select();
			if($res){
				$res = array('stat'=>'fail','msg'=>'要删除的菜单中含有子项目,请先移动或删除子项目');
				exit(json_encode($res));
			}else{
				$r = D('system_module')->where('mod_id='.$data['mod_id'])->delete();				
			}
		}
		
		if($r){	
			adminLog('管理系统菜单',__ACTION__);
			$res = array('stat'=>'ok');
		}else{
			$res = array('stat'=>'fail','msg'=>'操作失败');
		}
		exit(json_encode($res));
	}
	
	public function module(){
		$this->assign('tree',$this->tree());
		$this->display();
	}
	
	public function ctl_detail(){
		$mod_id = I('get.mod_id');		
		$tree = $this->tree();
		$rs = D('system_module')->order('mod_id ASC')->select();
		if($rs){
			foreach($rs as $k=>$v){
				if($v['parent_id'] == $mod_id && $v['module'] == 'module'){
					$modules[$k] = $v;
				}
			}
			$this->assign('pid',$mod_id);
			$this->assign('modules',$modules);
		}
		$this->assign('menu_tree',$this->tree());
		$this->display();
	}
	/**
     * 删除模块
     */
    public function delMod()
    {
        $re = M("System_module")->where('mod_id = '.$_GET['id'])->delete();
		if($re){
			$return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);
		}else{
			$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);
		}		
        $this->ajaxReturn(json_encode($return_arr));
    }
	public function ctlSave(){
		$data = I('post.');
		$t = false;
		if($data['module']){
			foreach ($data['module'] as $k=>$v){
				$v['visible'] = empty($v['visible']) ? 0 : 1;
				$r = D('system_module')->where("mod_id=$k")->save($v);
				if($r) $t = $r;
			}
		}
				
		if($data['data']){
			foreach ($data['data'] as  $k=>$v){
				if($v['title'] && $v['ctl'] && $v['act']){
					$v['level'] = 3;
					$v['module'] = 'module';
					$v['orderby'] = $v['orderby'] ? $v['orderby'] : 50;
					$r = D('system_module')->add($v);
				}
			}
		}
		if($r || $t){
			$res = array('stat'=>'ok');
		}else{
			$res = array('stat'=>'fail');
		}
		exit(json_encode($res));
	}
 
	
	public function tree()
	{
		$modules = array();
		$rs = D('system_module')->order('mod_id ASC')->select();
		if(is_array($rs)){
			foreach($rs as $row){
				$modules[$row['mod_id']] = $row;
			}		
		}
		if($modules){
			$tree = array();
			foreach($modules as $k=>$v){
				if($v['module'] == 'top'){
					$tree[$k] = $v;
				}
			}
			foreach($modules as $k=>$v){
				if($v['module'] == 'menu'){
					$tree[$v['parent_id']]['menu'][$k] = $v;
				}
			}
			foreach($modules as $k=>$v){
				if($v['module'] == 'module'){
					$ppk = $modules[$v['parent_id']]['parent_id'];
					$tree[$ppk]['menu'][$v['parent_id']]['menu'][$k] = $v;
				}
			}
			return $tree;
		}
		return false;
	}
	
	public function refreshMenu(){
		$pmenu = $arr = array();
		$rs = M('system_module')->where('level>1 AND visible=1')->order('mod_id ASC')->select();
		foreach($rs as $row){
			if($row['level'] == 2){
				$pmenu[$row['mod_id']] = $row['title'];//父菜单
			}
		}

		foreach ($rs as $val){
			if($row['level']==2){
				$arr[$val['mod_id']] = $val['title'];
			}
			if($row['level']==3){
				$arr[$val['mod_id']] = $pmenu[$val['parent_id']].'/'.$val['title'];
			}
		}
		return $arr;
	}
        
      /**
         * 清空系统缓存
         */
        public function cleanCache(){              
                              
            if(IS_POST)
            {                
                 in_array('cache',$_POST['clear']) && delFile('./Application/Runtime/Cache');// 模板缓存                 
                 in_array('data',$_POST['clear'])  && delFile('./Application/Runtime/Data');// 项目数据                 
                 in_array('logs',$_POST['clear'])  && delFile('./Application/Runtime/Logs');// logs日志                 
                 in_array('temp',$_POST['clear'])  && delFile('./Application/Runtime/Temp');// 临时数据
                 in_array('cacheAll',$_POST['clear'])  && delFile('./Application/Runtime');// 清除所有                 
                 //in_array('goods_thumb',$_POST['clear'])  && delFile('./Public/upload/goods/thumb'); // 删除缩略图
                 
                // 删除静态文件                
                $html_arr = glob("./Application/Runtime/Html/*.html");
                foreach ($html_arr as $key => $val)
                {
                    
                    in_array('index',$_POST['clear']) && strstr($val,'Home_Index_index.html') && unlink($val); // 首页                    
                    in_array('goodsList',$_POST['clear']) && strstr($val,'Home_Goods_goodsList') && unlink($val); // 列表页
                    in_array('channel',$_POST['clear']) && strstr($val,'Home_Channel_index') && unlink($val);  // 频道页
                    
                    in_array('articleList',$_POST['clear']) && strstr($val,'Index_Article_articleList') && unlink($val);  // 文章列表页
                    in_array('detail',$_POST['clear']) && strstr($val,'Index_Article_detail') && unlink($val);  // 文章详情
                    in_array('articleList',$_POST['clear']) && strstr($val,'Doc_Index_index_') && unlink($val);  // 文章列表页                    
                    in_array('detail',$_POST['clear']) && strstr($val,'Doc_Index_article_') && unlink($val);  // 文章详情                                        
                    
                    // 详情页
                    if(in_array('goodsInfo',$_POST['clear']))
                    {
                        if(strstr($val,'Home_Goods_goodsInfo') || strstr($val,'Home_Goods_ajaxComment') || strstr($val,'Home_Goods_ajax_consult'))
                            unlink($val);
                    }                                       
                }                               
                $this->success("操作完成!!!");
                exit;
            }            
            $this->display();                     
        }
	
}