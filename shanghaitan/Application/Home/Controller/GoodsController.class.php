<?php
namespace Home\Controller;
use Think\AjaxPage;
use Think\Page;
use Think\Verify;
class GoodsController extends BaseController {
    /**
     * 家居装修首页
     */
    public function index(){
        $lunbo = M('lunbo')->where(array('is_show'=>1,'type'=>2))->select();
        $xiaoguo = M('lunbo')->where(array('is_show'=>1,'type'=>3))->select();
        $jiancai = M('lunbo')->where(array('is_show'=>1,'type'=>4))->select();
        $x_cat = M('activity_cat')->where('parent_id=21')->select();
        $brand = M('brand')->where('is_hot=1')->select();
        $total =M('minge')->where('id=1')->find();
  /*      if(!empty($total['sum'])){
            $sum_arr = array();
            (int)$number = $_POST['number'];
            for($i=strlen($number);$i>0;$i--){
                $sum_arr[] = substr($number,$i-1,1);
            }
            print_r($sum_arr);
        }*/
        $hot =M('article_cat')->where('cat_id = 54')->find();
        $this->assign('user_id',$this->user_id);
        $this->assign('total',$total);
        $this->assign('x_cat',$x_cat);
        $this->assign('lunbo',$lunbo);
        $this->assign('hot',$hot['thumb']);
        $this->assign('xiaoguo',$xiaoguo);
        $this->assign('jiancai',$jiancai);
        $this->assign('brand',$brand);
        $this->display();
    }
    /**
     * 免费设计数据存表
     */
    public function ajaxApply(){
        if($_POST){
            $arr = M('apply')->where(array('sid'=>0,'mobile'=>$_POST['mobile'],'name'=>$_POST['name']))->find();
            if($arr){
                $_POST['time'] = time();
                $re = M('apply')->where(array('id' => $arr['id']))->save($_POST);
                exit(json_encode($re));
            }else{
                $_POST['time'] = time();
                $re = M('apply')->add($_POST);
                if($re){
                    M('minge')->where('id=1')->setDec('sum');
                }
                exit(json_encode($re));
            }
        }
    }
    /**
     * 免费报价数据存表
     */
    public function ajaxApp(){
        if($_POST){
            $square = trim($_POST['square']);
            if($square>=30&&$square<=50){
                $baojia = 11275 + 235.5*$square;
            }else if($square>=51&&$square<=75){
                $baojia = 14217.5 + 230.5*$square;
            }else if($square>=76&&$square<=100){
                $baojia = 13375 + 266*$square;
            }else if($square>=100&&$square<=130){
                $baojia = 18180 + 166*$square;
            }else{
                exit(json_encode(0));
            }
            $arr = M('apply')->where(array('sid'=>2,'mobile'=>$_POST['mobile']))->find();
            if( $arr) {
                $_POST['time'] = time();
                $re = M('apply')->where(array('id' => $arr['id']))->save($_POST);
                if ($re) {
                    exit(json_encode($baojia));
                }
            }else{
                $_POST['sid'] =2;
                $_POST['time'] = time();
                $re = M('apply')->add($_POST);
                if($re){
                    exit(json_encode($baojia));
                }
            }
        }
    }
    /**
     * 装修公司列表
     */
    public function companyList(){
        $count = M('shanghu')->where(array('is_check'=>1))->count();
        $page = new Page($count,5);
        $show = $page->show();
        if($_GET['pid']== 1){
            $gongsi = M('shanghu')->where(array('is_check'=>1))->order('sum desc')->limit($page->firstRow.','.$page->listRows)->select();
        }elseif($_GET['pid']== 2){
            $gongsi = M('shanghu')->where(array('is_check'=>1))->order('koubei desc')->limit($page->firstRow.','.$page->listRows)->select();
        }else{
            $gongsi = M('shanghu')->where(array('is_check'=>1))->order('reg_time desc')->limit($page->firstRow.','.$page->listRows)->select();
        }
        $this->assign('gongsi',$gongsi);
        $this->assign('total',$page->totalPages);
        $now = $page->nowPage;
        $this->assign('now',$now );
        $this->assign('next',$page->url($now+1) );
        $this->assign('prev',$page->url($now-1) );
        $this->assign('page',$show);
        $this->display();
    }
    /**
     * 免费设计（装修公司）
     */
    public function ajaxDesign(){
        if($_POST){
            $arr = M('apply')->where(array('sid'=>0,'mobile'=>$_POST['mobile'],'name'=>$_POST['name'],'c_id'=>$_POST['c_id']))->find();
            if($arr){
                $_POST['time'] = time();
                $re = M('apply')->where(array('id' => $arr['id']))->save($_POST);
                exit(json_encode($re));
            }else{
                $_POST['time'] = time();
                $re = M('apply')->add($_POST);
                if($re){
                    M('shanghu')->where(array('goods_id' => $_POST['c_id']))->setInc('taoshu');
                }
                exit(json_encode($re));
            }
        }
    }
    /**
     * 帮我选公司
     */
    public function ajaxSelect(){
        if($_POST){
            $arr = M('apply')->where(array('sid'=>3,'mobile'=>$_POST['mobile'],'name'=>$_POST['name']))->find();
            if($arr){
                $_POST['time'] = time();
                $re = M('apply')->where(array('id' => $arr['id']))->save($_POST);
                $this->ajaxReturn(json_encode($re));
            }else{
                $_POST['time'] = time();
                $_POST['sid'] = 3;
                $re = M('apply')->add($_POST);
                $this->ajaxReturn(json_encode($re));
            }
        }
    }
    /**
     * 公司详情
     */
    public function shanghu(){
        $id = $_GET['id'];
        $gongsi = M('shanghu')->where(array('goods_id'=>$id))->find();
        $ad = M('ad')->where(array('ad_name'=>$gongsi['goods_name']))->find();
        $this->assign('gongsi',$gongsi);
        $this->assign('ad',$ad);
        $this->display();
    }
    /**
     * 加载商品
     */
    public function ajaxPage(){
        $id = $_GET['id'];
        $gongsi = M('shanghu')->where(array('goods_id'=>$id))->find();
        $count = M('goods')->where(array('s_id'=>$gongsi['goods_id']))->count();
        $page  = new AjaxPage($count,8);
        $show = $page->show();
        $goods = M('goods')->where(array('s_id'=>$gongsi['goods_id']))->order('on_time desc')->limit($page->firstRow.','.$page->listRows)->select();
        $now = $page->nowPage;
        $this->assign('prev',$now-1 );
        $this->assign('next',$now+1 );
        $this->assign('arr',$goods);
        $this->display();
    }
    /**
     * 计算访问量
     */
    public function ajaxTotal(){
        //因不知数据存哪  就在名额表加了个字段total 暂用名额表
         M('minge')->where('id=1')->setInc('total',1);
        $arr = M('minge')->where('id=1')->find();
        $total = $arr['total'];
        exit(json_encode($total));
    }
    /**
     * 攻略
     */
    public function gonglue(){
        $this->display();
    }
    /**
     * 效果图
     */
    public function effect(){
        $effect = M('xiaoguo')->where(array('x_parent_id'=>0,'is_new'=>1))->order('x_time desc')->select();
        foreach($effect as $k=>$v){
            $count = M('xiaoguo')->where(array('x_parent_id'=>$v['x_id']))->count();
            if($count){
                $effect[$k]['total'] = $count;
            }else{
                $effect[$k]['total'] = 0;
            }
        }
        $this->assign('effect',$effect);
        $this->display();
    }
    /**
     * 加载效果图
     */
    public function ajaxEffect(){
        $x_id = $_POST['x_id'];
        $effect = M('xiaoguo')->where(array('x_parent_id'=>$x_id))->select();
        $this->assign('effect',$effect);
        $this->display();
    }
    /**
     * 展会专题
     */
    public function zhuanti(){
        $lunbo = M('lunbo')->where(array('type'=>5))->order('id desc')->select();
        $this->assign('lunbo',$lunbo);
        $this->display();
    }
    /**
     * 装修论坛
     */
    public function luntan(){
        $gongsi = M('shanghu')->where(array('is_check'=>1))->order('reg_time desc')->select();
        $this->assign('gongsi',$gongsi);
        $this->display();
    }
   /**
    * 商品详情页(积分兑换）
    */ 
    public function goodsInfo(){
        //  form表单提交
        C('TOKEN_ON',true);        
        $goodsLogic = new \Home\Logic\GoodsLogic();
        $goods_id = I("get.id");
        $goods = M('Goods')->where("goods_id = $goods_id")->find();
        if(empty($goods)){
        	$this->tp404('此商品不存在或者已下架');
        }
        if($goods['brand_id']){
            $brnad = M('brand')->where("id =".$goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }
        $goods_images_list = M('GoodsImages')->where("goods_id = $goods_id")->limit(6)->select(); // 商品 图册
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表                        
		$filter_spec = $goodsLogic->get_spec($goods_id);  
        //商品是否正在促销中
        $goods['promoting'] = 0;
        if($goods['is_promote'] == 1){
        	if(time() > $goods['promote_start_time'] && $goods['promote_end_time']>time()){
        		$goods['promoting'] = 1;
        	}else{
        		$goods['promoting'] = 0;
        	}
        } 
        $spec_goods_price  = M('spec_goods_price')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        M('Goods')->where("goods_id=$goods_id")->save(array('click_count'=>$goods['click_count']+1 )); //统计点击数
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        $count1 = M('Comment')->where(array('goods_id'=>$goods_id,'is_show'=>1,'parent_id'=>0))->count();
        $page1 = new Page($count1,6);
        $show1 = $page1->show();
        $c_arr = M('Comment')->where(array('goods_id'=>$goods_id,'is_show'=>1,'parent_id'=>0))->order('comment_id desc')->limit($page1->firstRow,$page1->listRows)->select();
        $num = '';
        foreach($c_arr as $k => $v){
            $c_arr[$k]['img'] = unserialize($v['img']); // 晒单图片
            $num += $v['goods_rank'];
        }
        $ave = $num/$count1;
        $ave = sprintf("%.1f",$ave);
        $this->assign('ave',$ave);
        $this->assign('c_arr',$c_arr);
        $this->assign('page1',$show1);
        //有追加评论
        $condition = "goods_id=$goods_id and recontent != ''and is_show=1 and parent_id=0";
        $count2 = M('Comment')->where($condition)->count();
        $page2 = new Page($count2,6);
        $show2 = $page2->show();
        $b_arr = M('Comment')->where($condition)->order('comment_id desc')->limit($page2->firstRow,$page2->listRows)->select();
        $this->assign('b_arr',$b_arr);
        $this->assign('page2',$show2);
        //有图片的评论
        $cond = "goods_id=$goods_id and img != ''and is_show=1 and parent_id=0";
        $count3 = M('Comment')->where($cond)->count();
        $page3 = new Page($count3,6);
        $show3 = $page3->show();
        $a_arr = M('Comment')->where($cond)->order('comment_id desc')->limit($page3->firstRow,$page3->listRows)->select();
        foreach($a_arr as $k => $v){
            $a_arr[$k]['img'] = unserialize($v['img']); // 晒单图片
        }
        //var_dump($a_arr);exit;
        $this->assign('a_arr',$a_arr);
        $this->assign('page3',$show3);
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        $this->assign('navigate_goods',navigate_goods($goods_id,1));// 面包屑导航 
        $this->assign('commentStatistics',$commentStatistics);//评论概览
        $this->assign('goods_attribute',$goods_attribute);//属性值     
        $this->assign('goods_attr_list',$goods_attr_list);//属性列表
        $this->assign('filter_spec',$filter_spec);//规格参数
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('siblings_cate',$goodsLogic->get_siblings_cate($goods['cat_id']));//相关分类
        $this->assign('look_see',$goodsLogic->get_look_see($goods));//看了又看      
        $this->assign('goods',$goods);
        $this->display();
    }
    public function goodsDetail(){
        //  form表单提交
        C('TOKEN_ON',true);
        $goodsLogic = new \Home\Logic\GoodsLogic();
        $goods_id = I("get.id");
        $goods = M('Goods')->where("goods_id = $goods_id")->find();
        if(empty($goods)){
            $this->tp404('此商品不存在或者已下架');
        }
        if($goods['brand_id']){
            $brnad = M('brand')->where("id =".$goods['brand_id'])->find();
            $goods['brand_name'] = $brnad['name'];
        }
        $goods_images_list = M('GoodsImages')->where("goods_id = $goods_id")->limit(6)->select(); // 商品 图册
        $goods_attribute = M('GoodsAttribute')->getField('attr_id,attr_name'); // 查询属性
        $goods_attr_list = M('GoodsAttr')->where("goods_id = $goods_id")->select(); // 查询商品属性表
        $filter_spec = $goodsLogic->get_spec($goods_id);
        //商品是否正在促销中
        $goods['promoting'] = 0;
        if($goods['is_promote'] == 1){
            if(time() > $goods['promote_start_time'] && $goods['promote_end_time']>time()){
                $goods['promoting'] = 1;
            }else{
                $goods['promoting'] = 0;
            }
        }
        $spec_goods_price  = M('spec_goods_price')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        M('Goods')->where("goods_id=$goods_id")->save(array('click_count'=>$goods['click_count']+1 )); //统计点击数
        $commentStatistics = $goodsLogic->commentStatistics($goods_id);// 获取某个商品的评论统计
        //获取全部评论
        $count1 = M('Comment')->where(array('goods_id'=>$goods_id,'is_show'=>1,'parent_id'=>0))->count();
        $page1 = new Page($count1,6);
        $show1 = $page1->show();
        $c_arr = M('Comment')->where(array('goods_id'=>$goods_id,'is_show'=>1,'parent_id'=>0))->order('comment_id desc')->limit($page1->firstRow,$page1->listRows)->select();
        $num = '';
        foreach($c_arr as $k => $v){
            $c_arr[$k]['img'] = unserialize($v['img']); // 晒单图片
            $num += $v['goods_rank'];
        }
        $ave = $num/$count1;
        $ave = sprintf("%.1f",$ave);
        $this->assign('ave',$ave);
        $this->assign('c_arr',$c_arr);
        $this->assign('page1',$show1);
        //有追加评论
        $condition = "goods_id=$goods_id and recontent != ''and is_show=1 and parent_id=0";
        $count2 = M('Comment')->where($condition)->count();
        $page2 = new Page($count2,6);
        $show2 = $page2->show();
        $b_arr = M('Comment')->where($condition)->order('comment_id desc')->limit($page2->firstRow,$page2->listRows)->select();
        $this->assign('b_arr',$b_arr);
        $this->assign('page2',$show2);
        //有图片的评论
        $cond = "goods_id=$goods_id and img != ''and is_show=1 and parent_id=0";
        $count3 = M('Comment')->where($cond)->count();
        $page3 = new Page($count3,6);
        $show3 = $page3->show();
        $a_arr = M('Comment')->where($cond)->order('comment_id desc')->limit($page3->firstRow,$page3->listRows)->select();
        foreach($a_arr as $k => $v){
            $a_arr[$k]['img'] = unserialize($v['img']); // 晒单图片
        }
        $this->assign('a_arr',$a_arr);
        $this->assign('page3',$show3);
        $this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        $this->assign('navigate_goods',navigate_goods($goods_id,1));// 面包屑导航
        $this->assign('commentStatistics',$commentStatistics);//评论概览
        $this->assign('goods_attribute',$goods_attribute);//属性值
        $this->assign('goods_attr_list',$goods_attr_list);//属性列表
        $this->assign('filter_spec',$filter_spec);//规格参数
        $this->assign('goods_images_list',$goods_images_list);//商品缩略图
        $this->assign('siblings_cate',$goodsLogic->get_siblings_cate($goods['cat_id']));//相关分类
        $this->assign('look_see',$goodsLogic->get_lookto_see($goods));//看了又看
        $this->assign('goods',$goods);
        $this->display();
    }
    /*
     * 养老平台
     */
    public function yangLao(){
        C('TOKEN_ON',true);
        $id = I('get.id',0); // 当前分类id
        $sort = I('sort','goods_id'); // 排序
        $sort_asc = I('sort_asc','asc'); // 排序
        $price = I('price',''); // 价钱
        $start_price = trim(I('start_price','0')); // 输入框价钱
        $end_price = trim(I('end_price','0')); // 输入框价钱
        if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱


        $goodsLogic = new \Home\Logic\GoodsLogic(); // 前台商品操作逻辑类

        // 分类菜单显示
        $goodsCate = M('GoodsCategory')->where("id = $id")->find();// 当前分类
        $cateArr = $goodsLogic->get_goods_cate($goodsCate);

        // 帅选 品牌 规格 属性 价格
        $cat_id_arr = getCatGrandson ($id);
        $filter_goods_id = M('goods')->where("is_on_sale=1 and cat_id in(".  implode(',', $cat_id_arr).")")->cache(true)->getField("goods_id",true);
        //$condition="is_on_sale=1 and is_recommend=1 and cat_id in(".  implode(',', $cat_id_arr).")";
        //$filter = M('goods')->where($condition)->cache(true)->getField("goods_id",true);
        $list = M('lunbo')->where(array('name'=>1))->limit(3)->select();


        $count = count($filter_goods_id);
        $page = new Page($count,16);
        //echo $page;exit;
        if($count > 0)
        {
            $goods_list = M('goods')->where("goods_id in (".  implode(',', $filter_goods_id).")")->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
            $filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
            if($filter_goods_id2)
                $goods_images = M('goods_images')->where("goods_id in (".  implode(',', $filter_goods_id2).")")->cache(true)->select();
        }
        // print_r($filter_menu);


        $goods_category = M('goods_category')->where(array('parent_id' => $id))->cache(true)->select(); // 键值分类数组
        if($goods_category){
            $goods_category = M('goods_category')->where(array('parent_id' => $id))->cache(true)->select();
        }else{
            $goods_category = M('goods_category')->where(array('id' => $id))->cache(true)->find();
            $p_id= $goods_category['parent_id'];
            $goods_category = M('goods_category')->where(array('parent_id' => $p_id))->cache(true)->select();
        }
        $navigate_cat = navigate_goods($id); // 面包屑导航
        $this->assign('list',$list);
        $this->assign('goods_list',$goods_list);
        $this->assign('navigate_cat',$navigate_cat);
        $this->assign('goods_category',$goods_category);
        $this->assign('goods_images',$goods_images);  // 相册图片
        $this->assign('goodsCate',$goodsCate);
        $this->assign('cateArr',$cateArr);
        $this->assign('cat_id',$id);
        $this->assign('page',$page->show());// 赋值分页输出
        C('TOKEN_ON',false);
        $this->display();
    }
    /*
     * 营养膳食详情也
     */
    public function detail(){
        $id = $_GET['id'];
        //var_dump($id);exit;
        $article = M('article')->where(array('article_id'=>$id))->cache(true)->find();
        $id_arr = M('article')->field('article_id')->where(array('cat_id'=>41))->select();
        $arr = array();
        foreach( $id_arr as $k=>$v){
           $arr[] =  $v['article_id'];
        }
        //var_dump($arr);exit;
        $article_id = $article['article_id'];
        $pre = $article_id-1;
        for($i=0;$i<count($arr);$i++) {
            if (!in_array($pre, $arr)) {
                $pre--;
                if($pre < min($arr)){
                    $pre = min($arr);
                }
            } else {
                $pre_article = M('article')->where(array('article_id'=>$pre))->cache(true)->find();
                $this->assign('pre_article', $pre_article);
                $this->assign('pre', $pre);
                break;
            }
        }
        $next = $article_id+1;
        for($i=0;$i<count($arr);$i++) {
            if (!in_array($next, $arr)) {
                $next++;
                if($next > max($arr)) {
                    $next = max($arr);
                }
            } else {
                $next_article = M('article')->where(array('article_id'=>$next))->cache(true)->find();
                $this->assign('next_article', $next_article);
                $this->assign('next', $next);
                break;
            }
        }
        $this->assign('article',$article);
        $this->assign('article_id',$article_id);
        $this->display();
    }
    /**
     * 商品列表页
     */
    public function goodsList(){
        //var_dump($_GET['cid']);
        C('TOKEN_ON',true);
        $id = I('get.id',0); // 当前分类id
        if($id){
            $id = I('get.id',0);
        }else{
            $id = 849;
        }
        $brand_id = I('brand_id',0);
        $spec = I('spec',0); // 规格
        $attr = I('attr',''); // 属性
        $sort = I('sort','goods_id'); // 排序
        $sort_asc = I('sort_asc','asc'); // 排序
        $price = I('price',''); // 价钱
        $start_price = trim(I('start_price','0')); // 输入框价钱
        $end_price = trim(I('end_price','0')); // 输入框价钱
        if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱


        $goodsLogic = new \Home\Logic\GoodsLogic(); // 前台商品操作逻辑类
        
        // 分类菜单显示
        $goodsCate = M('GoodsCategory')->where("id = $id")->find();// 当前分类
        //($goodsCate['level'] == 1) && header('Location:'.U('Home/Channel/index',array('cat_id'=>$id))); //一级分类跳转至大分类馆        
        $cateArr = $goodsLogic->get_goods_cate($goodsCate); 
         
        // 帅选 品牌 规格 属性 价格
        $cat_id_arr = getCatGrandson ($id);
        //var_dump($cat_id_arr);exit;
        $filter_goods_id = M('goods')->where("is_on_sale=1 and cat_id in(".  implode(',', $cat_id_arr).")")->cache(true)->getField("goods_id",true);
        //var_dump($filter_goods_id);exit;
        //$condition="is_on_sale=1 and is_recommend=1 and cat_id in(".  implode(',', $cat_id_arr).")";
        //$filter = M('goods')->where($condition)->cache(true)->getField("goods_id",true);
        $list = M('lunbo')->where(array('name'=>2))->limit(3)->select();
        $count = count($filter_goods_id);
        $page = new Page($count,16);
        //echo $page;exit;
        if($count > 0)
        {
            $goods_list = M('goods')->where("goods_id in (".  implode(',', $filter_goods_id).")")->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
            $filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
            if($filter_goods_id2)
            $goods_images = M('goods_images')->where("goods_id in (".  implode(',', $filter_goods_id2).")")->cache(true)->select();       
        }
        // print_r($filter_menu);


        $goods_category = M('goods_category')->where(array('parent_id' => $id))->cache(true)->select(); // 键值分类数组
       if($goods_category){
           $goods_category = M('goods_category')->where(array('parent_id' => $id))->cache(true)->select();
       }else{
           $goods_category = M('goods_category')->where(array('id' => $id))->cache(true)->find();
           $p_id= $goods_category['parent_id'];
           $goods_category = M('goods_category')->where(array('parent_id' => $p_id))->cache(true)->select();
       }
        $gid = I('get.gid',0);
        if($gid){
            $gid = I('get.gid',0);
        }else{
            $gid = 860;
        }
        $cat_gid_arr = getCatGrandson ($gid);
        //var_dump($cat_id_arr);exit;
        $filt_goods_id = M('goods')->where("is_on_sale=1 and cat_id in(".  implode(',', $cat_gid_arr).")")->cache(true)->getField("goods_id",true);
        //var_dump($filter_goods_id);exit;
        //$condition="is_on_sale=1 and is_recommend=1 and cat_id in(".  implode(',', $cat_id_arr).")";
        //$filter = M('goods')->where($condition)->cache(true)->getField("goods_id",true);
        $gcount = count($filt_goods_id);
        $gpage = new Page($gcount,16);
        //echo $page;exit;
        if($gcount > 0)
        {
            $g_list = M('goods')->where("goods_id in (".  implode(',', $filt_goods_id).")")->order("$sort $sort_asc")->limit($gpage->firstRow.','.$gpage->listRows)->select();
            $filt_goods_id2 = get_arr_column($g_list, 'goods_id');
            if($filt_goods_id2)
                $g_images = M('goods_images')->where("goods_id in (".  implode(',', $filt_goods_id2).")")->cache(true)->select();
        }
        $g_category = M('goods_category')->where(array('parent_id' => $gid))->cache(true)->select(); // 键值分类数组
        if($g_category){
            $g_category = M('goods_category')->where(array('parent_id' => $gid))->cache(true)->select();
        }else{
            $g_category = M('goods_category')->where(array('id' => $gid))->cache(true)->find();
            $g_id= $g_category['parent_id'];
            $g_category = M('goods_category')->where(array('parent_id' => $g_id))->cache(true)->select();
        }
        $this->assign('list',$list);
        $this->assign('g_category',$g_category);
        $navigate_cat = navigate_goods($id); // 面包屑导航         
        $this->assign('goods_list',$goods_list);
        $this->assign('g_list',$g_list);
        $this->assign('navigate_cat',$navigate_cat);
        $this->assign('goods_category',$goods_category);                
        $this->assign('goods_images',$goods_images);  // 相册图片
        $this->assign('g_images',$g_images);
        $this->assign('goodsCate',$goodsCate);
        $this->assign('cateArr',$cateArr);
        $this->assign('cat_id',$id);
        $this->assign('page',$page->show());// 赋值分页输出
        C('TOKEN_ON',false);
        $this->display();         
    }    
   
    /**
     * 商品搜索列表页
     */
    public function search()
    {
       //C('URL_MODEL',0);
        $filter_param = array(); // 帅选数组                        
        $id = I('get.id',0); // 当前分类id 
        $brand_id = I('brand_id',0);
        $spec = I('spec',0); // 规格 
        $attr = I('attr',''); // 属性        
        $sort = I('sort','goods_id'); // 排序
        $sort_asc = I('sort_asc','asc'); // 排序
        $price = I('price',''); // 价钱
        $start_price = trim(I('start_price','0')); // 输入框价钱
        $end_price = trim(I('end_price','0')); // 输入框价钱
        if($start_price && $end_price) $price = $start_price.'-'.$end_price; // 如果输入框有价钱 则使用输入框的价钱
        $q = urldecode(trim(I('q',''))); // 关键字搜索 
        empty($q) && $this->error('请输入搜索词');
                
        $id && ($filter_param['id'] = $id); //加入帅选条件中                       
        $brand_id  && ($filter_param['brand_id'] = $brand_id); //加入帅选条件中
        $spec  && ($filter_param['spec'] = $spec); //加入帅选条件中
        $attr  && ($filter_param['attr'] = $attr); //加入帅选条件中
        $price  && ($filter_param['price'] = $price); //加入帅选条件中
        $q  && ($_GET['q'] = $filter_param['q'] = $q); //加入帅选条件中
        
        $goodsLogic = new \Home\Logic\GoodsLogic(); // 前台商品操作逻辑类
               
        $where = "goods_name like '%{$q}%' and is_on_sale=1 ";
        if($id)
        {
            $cat_id_arr = getCatGrandson ($id);
            $where .= " and cat_id in(".  implode(',', $cat_id_arr).")";
        }
        
        $search_goods = M('goods')->where($where)->getField('goods_id,cat_id');
        $filter_goods_id = array_keys($search_goods);                
        $filter_cat_id = array_unique($search_goods); // 分类需要去重
        if($filter_cat_id)        
        {
            $cateArr = M('goods_category')->where("id in(".implode(',', $filter_cat_id).")")->select();            
            $tmp = $filter_param;
            foreach($cateArr as $k => $v)            
            {
                $tmp['id'] = $v['id'];
                $cateArr[$k]['href'] = U("/Home/Goods/search",$tmp);                            
            }                
        }                        
        // 过滤帅选的结果集里面找商品        
        if($brand_id || $price)// 品牌或者价格
        {
            $goods_id_1 = $goodsLogic->getGoodsIdByBrandPrice($brand_id,$price); // 根据 品牌 或者 价格范围 查找所有商品id    
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_1); // 获取多个帅选条件的结果 的交集
        }
        if($spec)// 规格
        {
            $goods_id_2 = $goodsLogic->getGoodsIdBySpec($spec); // 根据 规格 查找当所有商品id
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_2); // 获取多个帅选条件的结果 的交集
        }
        if($attr)// 属性
        {
            $goods_id_3 = $goodsLogic->getGoodsIdByAttr($attr); // 根据 规格 查找当所有商品id
            $filter_goods_id = array_intersect($filter_goods_id,$goods_id_3); // 获取多个帅选条件的结果 的交集
        }        
             
        $filter_menu  = $goodsLogic->get_filter_menu($filter_param,'search'); // 获取显示的帅选菜单
        $filter_price = $goodsLogic->get_filter_price($filter_goods_id,$filter_param,'search'); // 帅选的价格期间         
        $filter_brand = $goodsLogic->get_filter_brand($filter_goods_id,$filter_param,'search',1); // 获取指定分类下的帅选品牌        
        $filter_spec  = $goodsLogic->get_filter_spec($filter_goods_id,$filter_param,'search',1); // 获取指定分类下的帅选规格        
        $filter_attr  = $goodsLogic->get_filter_attr($filter_goods_id,$filter_param,'search',1); // 获取指定分类下的帅选属性        
                                
        $count = count($filter_goods_id);
        $page = new Page($count,20);
        if($count > 0)
        {
            $goods_list = M('goods')->where("is_on_sale=1 and goods_id in (".  implode(',', $filter_goods_id).")")->order("$sort $sort_asc")->limit($page->firstRow.','.$page->listRows)->select();
            $filter_goods_id2 = get_arr_column($goods_list, 'goods_id');
            if($filter_goods_id2)
            $goods_images = M('goods_images')->where("goods_id in (".  implode(',', $filter_goods_id2).")")->select();       
        }    
                
        $this->assign('goods_list',$goods_list);  
        $this->assign('goods_images',$goods_images);  // 相册图片
        $this->assign('filter_menu',$filter_menu);  // 帅选菜单
        $this->assign('filter_spec',$filter_spec);  // 帅选规格
        $this->assign('filter_attr',$filter_attr);  // 帅选属性
        $this->assign('filter_brand',$filter_brand);  // 列表页帅选属性 - 商品品牌
        $this->assign('filter_price',$filter_price);// 帅选的价格期间
        $this->assign('cateArr',$cateArr);
        $this->assign('filter_param',$filter_param); // 帅选条件
        $this->assign('cat_id',$id);
        $this->assign('page',$page);// 赋值分页输出
        C('TOKEN_ON',false);
        $this->display();
    }
    
    /**
     * 商品咨询ajax分页
     */
    public function ajaxConsult(){        
        $goods_id = I("goods_id",'0');        
        $consult_type = I('consult_type','0'); // 0全部咨询  1 商品咨询 2 支付咨询 3 配送 4 售后
                 
        $where = " parent_id = 0 and goods_id = $goods_id";
        if($consult_type > 0)
            $where .= " and consult_type = $consult_type ";
        
        $count = M('GoodsConsult')->where($where)->count();        
        $page = new AjaxPage($count,5);
        $show = $page->show();        
        $list = M('GoodsConsult')->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        $replyList = M('GoodsConsult')->where("parent_id > 0")->order("id desc")->select();
        $this->assign('consultCount',$count);// 商品咨询数量
        $this->assign('consultList',$list);// 商品咨询
        $this->assign('replyList',$replyList); // 管理员回复
        $this->assign('page',$show);// 赋值分页输出        
        $this->display();        
    }
    
    /**
     * 商品评论ajax分页
     */
    public function ajaxComment(){        
        $goods_id = I("goods_id",'0');        
        $commentType = I('commentType','1'); // 1 全部 2好评 3 中评 4差评
        if($commentType==5){
        	$where = "goods_id = $goods_id and parent_id = 0 and img !='' ";
        }else{
        	$typeArr = array('1'=>'0,1,2,3,4,5','2'=>'4,5','3'=>'3','4'=>'0,1,2');
        	$where = "goods_id = $goods_id and parent_id = 0 and ceil((deliver_rank + goods_rank + service_rank) / 3) in($typeArr[$commentType])";
        }
        $count = M('Comment')->where($where)->count();                
        
        $page = new AjaxPage($count,5);
        $show = $page->show();        
        $list = M('Comment')->where($where)->order("add_time desc")->limit($page->firstRow.','.$page->listRows)->select();
        $replyList = M('Comment')->where("goods_id = $goods_id and parent_id > 0")->order("add_time desc")->select();
        
        foreach($list as $k => $v){
            $list[$k]['img'] = unserialize($v['img']); // 晒单图片            
        }        
        $this->assign('commentlist',$list);// 商品评论
        $this->assign('replyList',$replyList); // 管理员回复
        $this->assign('page',$show);// 赋值分页输出        
        $this->display();        
    }    
    
    /**
     *  商品咨询
     */
    public function goodsConsult(){
        //  form表单提交
        C('TOKEN_ON',true);        
        $goods_id = I("goods_id",'0'); // 商品id
        $consult_type = I("consult_type",'1'); // 商品咨询类型
        $username = I("username",'TPshop用户'); // 网友咨询
        $content = I("content"); // 咨询内容
        
        $verify = new Verify();
        if (!$verify->check(I('post.verify_code'),'consult')) {
            $this->error("验证码错误");
        }
        
        $goodsConsult = M('goodsConsult');        
        if (!$goodsConsult->autoCheckToken($_POST))
        {            
                $this->error('你已经提交过了!', U('/Home/Goods/goodsInfo',array('id'=>$goods_id))); 
                exit;
        }
        
        $data = array(
            'goods_id'=>$goods_id,
            'consult_type'=>$consult_type,
            'username'=>$username,
            'content'=>$content,
            'add_time'=>time(),
        );        
        $goodsConsult->add($data);        
        $this->success('咨询已提交!', U('/Home/Goods/goodsInfo',array('id'=>$goods_id))); 
    }
    
    /**
     * 用户收藏某一件商品
     * @param type $goods_id
     */
    public function collect_goods($goods_id)
    {
        $goods_id = I('goods_id');
        $goodsLogic = new \Home\Logic\GoodsLogic();        
        $result = $goodsLogic->collect_goods($this->user_id,$goods_id);
        exit(json_encode($result));
    }
    
    /**
     * 加入购物车弹出
     */
    public function open_add_cart()
    {
         $this->display();
    }
    public function integralMall()
    {
        $cat_id = I('get.id');
        /*   $minValue = I('get.minValue');
           $maxValue = I('get.maxValue');
           $brandType = I('get.brandType');*/
        $point_rate = tpCache('shopping.point_rate');
        $is_new = I('get.is_new',0);
        $exchange = I('get.exchange',0);
        $goods_where = array(
            'is_on_sale' => 0,
        );
        $where = array(
            'is_on_sale' => 0,
            'is_hot'=> 1
        );
        //积分兑换筛选
        $exchange_integral_where_array = array(array('gt',0));
        // 分类id
        if (!empty($cat_id)) {
            $goods_where['cat_id'] = array('in', getCatGrandson($cat_id));
        }
        /*   //积分截止范围
        if (!empty($maxValue)) {
            array_push($exchange_integral_where_array, array('lt', $maxValue));
        }
        //积分起始范围
        if (!empty($minValue)) {
            array_push($exchange_integral_where_array, array('gt', $minValue));
        }
        //积分+金额
        if ($brandType == 1) {
            array_push($exchange_integral_where_array, array('exp', ' < shop_price* ' . $point_rate));
        }
        //全部积分
        if ($brandType == 2) {
            array_push($exchange_integral_where_array, array('exp', ' = shop_price* ' . $point_rate));
        }
        //新品
        if($is_new == 1){
            $goods_where['is_new'] = $is_new;
        }
        //我能兑换
        $user_id = cookie('user_id');
        if ($exchange == 1 && !empty($user_id)) {
            $user_pay_points = intval(M('users')->where(array('user_id' => $user_id))->getField('pay_points'));
            if ($user_pay_points !== false) {
                array_push($exchange_integral_where_array, array('lt', $user_pay_points));
            }
        }

        $goods_where['exchange_integral'] =  $exchange_integral_where_array;*/
        $goods_list_count = M('goods')->where($goods_where)->count();
        $page = new Page($goods_list_count, 8);
        $goods_list = M('goods')->where($goods_where)->limit($page->firstRow . ',' . $page->listRows)->select();
        //var_dump($goods_list);exit;
        $goods_hot = M('goods')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
        $goods_category = M('goods_category')->where(array('level' => 2, ))->select();
        //var_dump($goods_category);exit;
        $list = M('lunbo')->where(array('name'=>7))->limit(3)->select();
        $this->assign('list', $list);
        $this->assign('goods_list', $goods_list);
        $this->assign('goods_hot', $goods_hot);
        $this->assign('page', $page->show());
        $this->assign('goods_list_count',$goods_list_count);
        $this->assign('goods_category', $goods_category);//商品1级分类
        $this->assign('point_rate', $point_rate);//兑换率
        $this->assign('nowPage',$page->nowPage);// 当前页
        $this->assign('totalPages',$page->totalPages);//总页数
        $this->display();
    }
}