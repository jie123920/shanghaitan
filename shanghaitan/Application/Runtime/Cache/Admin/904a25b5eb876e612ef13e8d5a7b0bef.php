<?php if (!defined('THINK_PATH')) exit();?><form method="post" enctype="multipart/form-data" target="_blank" id="form-order">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);"></td>
                </td>
                <td class="text-right">
                    <a href="javascript:sort('goods_id');">ID</a>
                </td>
                <td class="text-left">
                    <a href="javascript:sort('goods_name');">商户名称</a>
                </td>
                <td class="text-left">
                    <a href="javascript:sort('city');">所在地</a>
                </td>                                
                <td class="text-left">
                    <a href="javascript:sort('sum');">咨询数</a>
                </td>                
                <td class="text-left">
                    <a href="javascript:sort('koubei');">口碑值</a>
                </td>
                <td class="text-left">
                    <a href="javascript:sort('reg_time');">入驻时间</a>
                </td>
                <td class="text-center">
                    <a href="javascript:sort('is_recommend');">推荐</a>
                </td>
                <td class="text-center">
                    <a href="javascript:sort('sort');">排序</a>
                </td>                   
                <td class="text-right">操作</td>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($goodsList)): $i = 0; $__LIST__ = $goodsList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><tr>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="6">
                        <input type="hidden" name="shipping_code[]" value="flat.flat">
                    </td>
                    <td class="text-right"><?php echo ($list["goods_id"]); ?></td>
                    <td class="text-left"><?php echo (getSubstr($list["goods_name"],0,33)); ?></td>
                    <td class="text-left"><?php echo ($list["city"]); ?></td>
                    <td class="text-left"><?php echo ($list["sum"]); ?></td>
                    <td class="text-left"><?php echo ($list["koubei"]); ?></td>
                    <td class="text-left"><?php echo (date("Y-m-d H:i",$list["reg_time"])); ?></td>
                    <td class="text-center">
                        <img width="20" height="20" src="/Public/images/<?php if($list[is_recommend] == 1): ?>yes.png<?php else: ?>cancel.png<?php endif; ?>" onclick="changeTableVal('goods','goods_id','<?php echo ($list["goods_id"]); ?>','is_recommend',this)"/>
                    </td>
                    <td class="text-center">                         
                        <input type="text" onkeyup="this.value=this.value.replace(/[^\d]/g,'')" onpaste="this.value=this.value.replace(/[^\d]/g,'')" onchange="updateSort('goods','goods_id','<?php echo ($list["goods_id"]); ?>','sort',this)" size="4" value="<?php echo ($list["sort"]); ?>" />
                    </td>                    
                    <td class="text-right">
                        <a  target="_blank" href="<?php echo U('Home/Goods/goodsInfo',array('id'=>$list['goods_id']));?>" data-toggle="tooltip" title="" class="btn btn-info" data-original-title="查看详情"><i class="fa fa-eye"></i></a>
                      <!-- <a href="<?php echo U('Admin/goods/addEditGoods',array('id'=>$list['goods_id']));?>" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="编辑"><i class="fa fa-pencil"></i></a>-->
                        <a href="javascript:void(0);" onclick="del('<?php echo ($list[goods_id]); ?>')" id="button-delete6" data-toggle="tooltip" title="" class="btn btn-danger" data-original-title="删除"><i class="fa fa-trash-o"></i></a></td>
                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
        </table>
    </div>
</form>
<div class="row">
    <div class="col-sm-3 text-left"></div>
    <div class="col-sm-9 text-right"><?php echo ($page); ?></div>
</div>
<script>
    // 点击分页触发的事件
    $(".pagination  a").click(function(){
        cur_page = $(this).data('p');
        ajax_get_table('search-form2',cur_page);
    });
</script>