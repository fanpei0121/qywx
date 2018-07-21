<?php
namespace Ylfc;
use think\Db;

class SendNotice
{
    //发送给物业的消息
    public function send_wuye($userid, $data, $type, $content = '', $wx_list = array(), $dx_list = array(), $zhiding = 0, $is_print = 1)
    {
        if (empty($userid) || empty($type)) {
            return false;
        }
        $now_time = NOW_TIME_Y;
        $oaurl = 'https://oa.weixiaoqu.com/';

        if ($zhiding == 0) {//有些地方（意见报修要判断标签）是发消息的人是传过来，不用再获取一次
            if ($type == 2 || $type == 3) {//商家入驻不需要关联小区
                $join_wx = '';
            } else {
                $join_wx = ['wxq_user_notice_xq d', 'b.n_id=d.n_id'];
//                $join_wx = 'inner join  wxq_user_notice_xq as d on b.n_id=d.n_id';
                $wx_where['d.xiaoquid'] = (int)$data['xiaoquid'];
                $wx_where['d.buildingid'] = (int)$data['buildingid'];
                $wx_where['d.unitid'] = (int)$data['unitid'];
                $wx_where['d.type'] = $type;
            }
            //获取发微信通知的人
            $wx_where['c.is_wx'] = 1; //开启微信
            $wx_where['c.type'] = $type;
            $wx_where['a.addid'] = $userid;
            //$wx_where['openid'] = array('neq', '');
            $joins_1 = [
                ['wxq_user_notice b', 'a.user_id=b.user_id'],
                ['wxq_user_notice_fun c', 'b.n_id=c.n_id'],
            ];
            if (!empty($join_wx)) {
                $joins_1[] = $join_wx;
            }
            //join('inner join  wxq_user_notice as b on a.user_id=b.user_id inner join wxq_user_notice_fun as c on b.n_id=c.n_id '.$join_wx)
            $wx_list = Db::name('staff_user')->alias('a')->field('a.*')
                ->join($joins_1)
                ->where($wx_where)->select();

            if ($type == 2 || $type == 3) {//商家入驻不需要关联小区
                $join_dx = '';
            } else {
                $join_dx = ['wxq_user_notice_xq d', 'b.n_id=d.n_id'];
//                $join_dx = 'inner join  wxq_user_notice_xq as d on b.n_id=d.n_id';
                $dx_where['d.xiaoquid'] = (int)$data['xiaoquid'];
                $dx_where['d.buildingid'] = (int)$data['buildingid'];
                $dx_where['d.unitid'] = (int)$data['unitid'];
                $dx_where['d.type'] = $type;
            }
            //获取发短信通知的人
            $dx_where['c.is_dx'] = 1; //开启短信
            $dx_where['c.type'] = $type;
            $dx_where['a.addid'] = $userid;
            $joins_2 = [
                ['wxq_user_notice b', 'a.user_id=b.user_id'],
                ['wxq_user_notice_fun c', 'b.n_id=c.n_id']
            ];
            if (!empty($join_dx)) {
                $joins_2[] = $join_dx;
            }
            //join('inner join  wxq_user_notice as b on a.user_id=b.user_id inner join wxq_user_notice_fun as c on b.n_id=c.n_id '.$join_dx)
            $dx_list = Db::name('staff_user')->alias('a')->field('a.*')
                ->join($joins_2)
                ->where($dx_where)->select();
        }
        if ($is_print == 1) {//打印机默认开启
            //获取打印机
            if ($type == 2 || $type == 3) {
                $join_print = '';
            } else {
                $join_print = ['wxq_user_print_xq c', 'b.p_id=c.p_id'];
//            $join_print = 'inner join wxq_user_print_xq as c on b.p_id=c.p_id ';
                $print_where['c.xiaoquid'] = (int)$data['xiaoquid'];
                $print_where['c.buildingid'] = (int)$data['buildingid'];
                $print_where['c.unitid'] = (int)$data['unitid'];
                $print_where['c.type'] = $type;
            }
            $print_where['b.is_use'] = 1; //开启短信
            $print_where['b.type'] = $type;
            $print_where['a.userid'] = $userid;
            $joins_3 = [
                ['wxq_user_print_fun b', 'a.p_id=b.p_id']
            ];
            if (!empty($join_print)) {
                $joins_3[] = $join_print;
            }
            //join('inner join  wxq_user_print_fun as b on a.p_id=b.p_id '.$join_print)
            $print_list = Db::name('user_print')->alias('a')->field('a.*')
                ->join($joins_3)
                ->where($print_where)->select();
        }
        if (!empty($wx_list)) {
            import('Ylfc.Msgwx', EXTEND_PATH, '.class.php');
            $msg = new Msgwx();
            $access_token = $msg->get_access_token();
            $te = new \app\api\model\Templat();
            //企业微信通知
            $qy = new \Ylfc\QiyeWxSq($userid);
            $qyweixin = new \Ylfc\Qyweixin();
            $qy_accses_token = $qy->get_access_token();
            if (!empty($qy_accses_token)) {
                $qiye_info = Db::name('qiye_weixin')->field('corpid,agentid')->where(['userid' => $userid])->find();
                $agentid = $qiye_info['agentid'];
                $corpid = $qiye_info['corpid'];
            }
        }

        switch ($type) {
            case 1://意见报修通知
                /*
                 * 需要xiaoquid buildingid unitid houseid xiaoquname name phone wenzi （您好，您有以下事项需要处理）
                 */
                $b_name = Db::name('xq_building')->where(array('buildingid' => (int)$data['buildingid']))->value('buildingname');
                $u_name = Db::name('xq_unit')->where(array('unitid' => (int)$data['unitid']))->value('unitname');
                $h_no = Db::name('xq_house')->where(array('houseid' => (int)$data['houseid']))->value('houseno');
                if (!empty($print_list)) {//发送打印机通知
                    $print_con .= '<center>' . $data['xiaoquname'] . '报修工单</center>\r\n';
                    $print_con .= '--------------------------------\r\n';
                    $print_con .= '报 修 人：' . $data['name'] . '\r\n';
                    $print_con .= '联系电话：' . $data['phone'] . '\r\n';
                    $print_con .= '报修时间：' . $now_time . '\r\n';
                    $print_con .= '所在楼宇：' . $b_name . '\r\n';
                    $print_con .= '所在单元：' . $u_name . '\r\n';
                    $print_con .= '相关房屋：' . $h_no . '\r\n';
                    $print_con .= '报修内容：' . $content . '\r\n';
                    $print_con .= '--------------------------------\r\n';
                    $print_con .= '请尽快登录微小区管理中心处理申请\r\n';
                }
                //短信消息

                if (!empty($dx_list)) {
                    $send_data['xqname'] = $data['xiaoquname'];
                    $send_data['home'] = $h_no;
                    $send_data['name'] = $data['name'];
                    $send_data['phone'] = $data['phone'];
                    $send_data['title'] = $content;
                    $item = 'ejG1z2';
                    $dx_beishu = 3;
                }
                $url = $oaurl . 'index/fun/yijian_detail/repair_id/' . $data['id'];
                if (!empty($wx_list)) {
                    $tem = 'OPENTM401111343';
                    $template_id = $te->getTemplate(config('MSGWX_APPID'), $tem, $access_token);
                    if ($template_id) {
                        foreach ($wx_list as $wx_v) {
                            if(!empty($wx_v['wx_user_id'])){
                            $qy_list[] = $wx_v['wx_user_id'];
                            }
                            $user_id_arr[] = $wx_v['user_id'];
                            if(!empty($wx_v['openid'])){
                            $param = '{"touser":"' . $wx_v['openid'] . '","template_id":"' . $template_id . '","url":"' . $url . '",';
                            $param .= '"data":{';
                            $param .= '"first": {';
                            $param .= '"value":"' . $data['wenzi'] . '",';
                            $param .= '"color":"#173177"';
                            $param .= '},';
                            $param .= '"keyword1": {';
                            $param .= '"value":"' . $data['xiaoquname'] . $b_name . $u_name . $h_no . '",';
                            $param .= '"color":"#173177"';
                            $param .= '},';
                            $param .= '"keyword2": {';
                            $param .= '"value":"' . $data['name'] . ' 电话：' . $data['phone'] . '",';
                            $param .= '"color":"#173177"';
                            $param .= '},';
                            $param .= '"keyword3": {';
                            $param .= '"value":"意见报修",';
                            $param .= '"color":"#173177"';
                            $param .= '},';
                            $param .= '"keyword4": {';
                            $param .= '"value":"' . $data['id'] . '",';
                            $param .= '"color":"#173177"';
                            $param .= '},';
                            $param .= '"keyword5": {';
                            $param .= '"value":"' . $content . '",';
                            $param .= '"color":"#173177"';
                            $param .= '},';
                            $param .= '"remark":{';
                            $param .= '"value":"请登录微小区管理中心查看。",';
                            $param .= '"color":"#173177"';
                            $param .= '}';
                            $param .= '}';
                            $param .= '}';
                            http_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token, $param);
                            }
                        }
                    }
                    if (!empty($qy_list)) {
                        $qy_content['title'] = '工单提醒';
                        $qy_content['description'] = '<div class="gray">' . date('Y年m月d日') . '</div><div>' . $data['wenzi'] . '</div><div>相关房屋：' . $data['xiaoquname'] . $b_name . $u_name . $h_no . '</div><div>业主信息：' . $data['name'] . ' 电话：' . $data['phone'] . '</div><div>工单类型：意见报修</div><div>工单编号：' . $data['id'] . '</div><div>具体内容：' . $content . '</div>';
                        $qy_content['url'] = 'https://work.weixiaoqu.com/wuye/gd-detail/' . $data['id'].'?corpid='.$corpid;
                        $qyweixin->send_text_card($agentid, $qy_accses_token, $qy_list, $qy_content);
                    }
                }
                break;
            default:
                break;
        }

        if (!empty($send_data)) {//发送短信请求
//            $infoa = M('UserInfo')->field('smstotal,smsused')->where(array('userid' => $userid))->find(); //每次发生判断短信数量
            foreach ($dx_list as $dx_v) {
                $user_id_arr[] = $dx_v['user_id'];
            }
            $infoa = Db::name('user_info')->field('smstotal,smsused')->where(['userid' => $userid])->find();
            if ($infoa["smstotal"] - $infoa["smsused"] > count($dx_list) * $dx_beishu) {
                import('Ylfc.Send', EXTEND_PATH, '.class.php');
                $send = new Send();
                $info = $send->sendWuye($item, array_column($dx_list, 'phone'), $send_data);
                if($info['status']=='error'){

                }  else {
                foreach ($info as $sms_v) {
                    if ($sms_v['status'] == 'success') {
                        $sms_count += (int)$sms_v['fee'];
                    }
                }
                }
                if ($sms_count > 0) {
//                    M('UserInfo')->where(array('userid' => $userid))->setInc('smsused', $sms_count);
                    Db::name('user_info')->where(['userid' => $userid])->setInc('smsused', $sms_count);
                    $log = new \app\api\model\UserLog();
                    $log->addLog( '短信：' . $sms_count . '条', 6, $sms_count);
                }
            } else {
                $d['msgtype'] = 1;
                $d['msgstatus'] = 0;
                $d['userid'] = $userid;
                $d['msgtype2'] = 0;
                $d['msg'] = '<a href="/wuye/sms_combo">短信包不足，请尽快充值购买</a>';
                $d['title'] = '短信包不足，请尽快充值购买';
                $d['addtime'] = $now_time;
//                M('UserMsg')->add($d);
                Db::name('user_msg')->insert($d);
            }
        }

        if (!empty($print_con)) {//发送打印机请求
//            import('@.Ylfc.Yprint');
            import('Ylfc.Yprint', EXTEND_PATH, '.class.php');
            $print = new \Yprint();
            foreach ($print_list as $value) {
                $print->action_print($value['partner'], $value['print_zd'], $print_con, $value['apikey'], $value['print_ms']);
            }
        }
        if(!empty($user_id_arr)){
        $user_id_arr_qu = array_unique($user_id_arr);//取出重复数据
        }
        if(!empty($user_id_arr_qu)){
            $msg_s = $content;
            foreach ($user_id_arr_qu as  $msg_v) {
                $usermsg[] = array(
                    'msgtype'=>0,
                    'msgstatus'=>0,
                    'userid'=>0,
                    'xiaoquid'=>0,
                    'msgtype2'=>0,
                    'msg'=>$msg_s,
                    'addtime'=>$now_time,
                    'addid'=>0,
                    'title'=>'',
                    'msg_group'=>'0',
                    'msg_type'=>$type,
                    'user_id'=>$msg_v,
                    'url'=>$url,
                );
            }
            Db::name('user_msg')->insertAll($usermsg);
        }
    }

    /*
     * 给住户回复消息
     */
    public function send_zhuhu($userid, $type, $yhid, $data = array())
    {
        if (empty($userid) || empty($yhid) || empty($type)) {
            return false;
        }

        $now_time = date('Y-m-d H:i:s');
//        $set = M('UserNoticeReply')->where(array('userid' => $userid, 'type' => $type))->find();
        if($type==7){
            $a_type = 1;
        }  else {
            $a_type = $type;
        }

        $set = Db::name('user_notice_reply')->where(['userid' => $userid, 'type' => $a_type])->find();
        if ($set['is_wx'] == 1) {//微信
            $where['a.yhid'] = $yhid;
            $where['d.appid'] = ['exp','=a.appid'];
            $joins_yz = [
                ['wxq_yz_info b', 'a.yhid=b.id'],
                ['wxq_user_weixin_xq c', 'b.xiaoquid=c.xiaoquid'],
                ['wxq_user_weixin d', 'c.weixinid=d.weixinid']
            ];
            $info = Db::name('yz_openid')->alias('a')->field('a.*,b.xiaoquid,d.webdomain,d.weixinid')
                ->join($joins_yz)
                ->where($where)->find();
            unset($where['d.appid']);
            $info_zfb = Db::name('yz_zfb_openid')->alias('a')->where($where)->field('a.*,b.xiaoquid,b.userid')
                    ->join('wxq_yz_info b','a.yhid=b.id')->find();
            if (!empty($info)) {
                import('Ylfc.Gzfuwu', EXTEND_PATH, '.class.php');
                $fw = new Gzfuwu();
                $access_token = $fw->get_access_token($info['appid']);
                $te = new \app\api\model\Templat();
                if (!empty($access_token)) {

                    switch ($type) {
                        case 1://id（意见报修主键）zhuangtai（回复状态：已回复，已完结）con（回复内容）kefu（客服昵称）
                            $item = 'OPENTM201006704';
//                            $template_id = D('Template')->getTemplate($info['appid'], $item, $access_token);
                            $template_id = $te->getTemplate($info['appid'], $item, $access_token);
                            if (false != $template_id) {
                                $room = Db::name('yz_house')->where(array('yhid' => $yhid))->value('h_no');
                                $param = '{"touser":"' . $info['openid'] . '","template_id":"' . $template_id . '","url":"https://i.weixiaoqu.com/index/user/yijian_detail?id=' . $data['id'] . '&wid='.jiami_id($info['weixinid']).'",';
                                $param .= '"data":{';
                                $param .= '"first": {';
                                $param .= '"value":"您好，您有新的物业反馈提醒：",';
                                $param .= '"color":"#173177"';
                                $param .= '},';
                                $param .= '"keyword1":{';
                                $param .= '"value":"' .$room. '",';
                                $param .= '"color":"#173177"';
                                $param .= '},';
                                $param .= '"keyword2": {';
                                $param .= '"value":"意见报修",';
                                $param .= '"color":"#173177"';
                                $param .= '},';
                                $param .= '"keyword3": {';
                                $param .= '"value":"' . $data['zhuangtai'] . '",';
                                $param .= '"color":"#173177"';
                                $param .= '},';
                                $param .= '"keyword4": {';
                                $param .= '"value":"' . $data['con'] . '",';
                                $param .= '"color":"#173177"';
                                $param .= '},';
                                $param .= '"keyword5": {';
                                $param .= '"value":"' . $data['kefu'] . '",';
                                $param .= '"color":"#173177"';
                                $param .= '},';
                                $param .= '"remark":{';
                                $param .= '"value":"感谢您对我们提出宝贵建议，若在处理过程中有任何疑问请随时与业主中心联系。",';
                                $param .= '"color":"#173177"';
                                $param .= '}';
                                $param .= '}';
                                $param .= '}';
                            }
                            break;
                        case 6:
                            $item = 'OPENTM201006704';
                            $te = new \app\api\model\Templat();
                            $template_id = $te->getTemplate($info['appid'], $item, $access_token);
                            if (false != $template_id) {
                                $room = Db::name('yz_house')->where(array('yhid'=>$yhid))->value('h_no');
                                $param = '{"touser":"' . $info['openid'] . '","template_id":"' . $template_id . '","url":"https://i.weixiaoqu.com/index/user/kefu_notice?id=' . $data['id'] . '&wid='.jiami_id($info['weixinid']).'",';
                                $param .='"data":{';
                                $param .='"first": {';
                                $param .='"value":"尊敬的业主，'.$data['xiaoquname'].'客服向您发送了客服通知：",';
                                $param .='"color":"#173177"';
                                $param .='},';
                                $param .='"keyword1":{';
                                $param .='"value":"' . $room . '",';
                                $param .='"color":"#173177"';
                                $param .='},';
                                $param .='"keyword2": {';
                                $param .='"value":"客服通知",';
                                $param .='"color":"#173177"';
                                $param .='},';
                                $param .='"keyword3": {';
                                $param .='"value":"' . $data['zhuangtai'] . '",';
                                $param .='"color":"#173177"';
                                $param .='},';
                                $param .='"keyword4": {';
                                $param .='"value":"' . $data['con'] . '",';
                                $param .='"color":"#173177"';
                                $param .='},';
                                $param .='"keyword5": {';
                                $param .='"value":"' . $data['kefu'] . '",';
                                $param .='"color":"#173177"';
                                $param .='},';
                                $param .='"remark":{';
                                $param .='"value":"点击查看详情。",';
                                $param .='"color":"#173177"';
                                $param .='}';
                                $param .='}';
                                $param .='}';
                            }
                            break;
                            case 7:
                    $item = 'OPENTM400889655';
                    $te = new \app\api\model\Templat();
                    $template_id = $te->getTemplate($info['appid'], $item, $access_token);
                    if (false != $template_id) {
                        $param = '{"touser":"' . $info['openid'] . '","template_id":"' . $template_id . '","url":"https://i.weixiaoqu.com/index/user/index?wid='.jiami_id($info['weixinid']).'",';
                        $param .='"data":{';
                        $param .='"first": {';
                        $param .='"value":"'.$data['con'].'",';
                        $param .='"color":"#173177"';
                        $param .='},';
                        $param .='"keyword1":{';
                        $param .='"value":"迁入审核",';
                        $param .='"color":"#173177"';
                        $param .='},';
                        $param .='"keyword2": {';
                        $param .='"value":"'.$data['tg'].'",';
                        $param .='"color":"#173177"';
                        $param .='},';
                        $param .='"keyword3": {';
                        $param .='"value":"' . $data['butongguo'] . '",';
                        $param .='"color":"#173177"';
                        $param .='},';
                        $param .='"remark":{';
                        $param .='"value":"点击查看详情",';
                        $param .='"color":"#173177"';
                        $param .='}';
                        $param .='}';
                        $param .='}';
                    }
                  break;
                        default:
                            break;
                    }
                    if ($template_id != false) {
                        http_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token, $param);
                    }
                }
            }
            if(!empty($info_zfb)){
                switch ($type) {
                    case 1:
                        $item_z = 'TM000002426';

                        $room = Db::name('yz_house')->where(array('yhid'=>$yhid))->value('h_no');
                        $te = new \app\api\model\Templat();
                        $template_id_z = $te->getZfbT($info_zfb['userid'], $info_zfb['xiaoquid'],$item_z);

                        if (false != $template_id_z) {
                            $zfb_data['to_user_id'] = $info_zfb['zfb_openid'];
                            $zfb_data['template'] = [
                                'template_id'=>$template_id_z,
                                'context'=>[
                                    'head_color'=>'#173177',
                                    'url'=>'https://a.weixiaoqu.com/index/fun/yijian_detail/repair_id/'.$data['id'].'/xid/'.  jiami_id($info_zfb['xiaoquid']),
                                    'action_name'=>'查看详情',
                                    'first'=>['color'=>'#173177','value'=>'您好，您有新的物业反馈提醒：'],
                                    'keyword1'=>['color'=>'#173177','value'=>empty($room)?' ':$room],
                                    'keyword2'=>['color'=>'#173177','value'=>'意见报修'],
                                    'keyword3'=>['color'=>'#173177','value'=>$data['zhuangtai']],
                                    'keyword4'=>['color'=>'#173177','value'=>$data['con']],
                                    'keyword5'=>['color'=>'#173177','value'=>$data['kefu']],
                                    'remark'=>['color'=>'#173177','value'=>'感谢您对我们提出宝贵建议，若在处理过程中有任何疑问请随时与业主中心联系'],
                                ],
                            ];
                        }
                        break;
                    case 6:
                        $item_z = 'TM000002426';
                        $room = Db::name('yz_house')->where(array('yhid'=>$yhid))->value('h_no');
                        $te = new \app\api\model\Templat();
                        $template_id_z = $te->getZfbT($info_zfb['userid'], $info_zfb['xiaoquid'],$item_z);
                        if (false != $template_id_z) {
                            $zfb_data['to_user_id'] = $info_zfb['zfb_openid'];
                            $zfb_data['template'] = [
                                'template_id'=>$template_id_z,
                                'context'=>[
                                    'head_color'=>'#173177',
                                    'url'=>'https://a.weixiaoqu.com/index/fun/kefu_notice/id/'.$data['id'].'/xid/'.  jiami_id($info_zfb['xiaoquid']),
                                    'action_name'=>'查看详情',
                                    'first'=>['color'=>'#173177','value'=>'尊敬的业主，'.$data['xiaoquname'].'客服向您发送了客服通知：'],
                                    'keyword1'=>['color'=>'#173177','value'=>empty($room)?' ':$room],
                                    'keyword2'=>['color'=>'#173177','value'=>'客服通知'],
                                    'keyword3'=>['color'=>'#173177','value'=>$data['zhuangtai']],
                                    'keyword4'=>['color'=>'#173177','value'=>$data['con']],
                                    'keyword5'=>['color'=>'#173177','value'=>$data['kefu']],
                                    'remark'=>['color'=>'#173177','value'=>''],
                                ],
                            ];
                        }
                        break;
                    case 7:
                        $item_z = 'TM000002429';
                        $te = new \app\api\model\Templat();
                        $template_id_z = $te->getZfbT($info_zfb['userid'], $info_zfb['xiaoquid'],$item_z);
                        if (false != $template_id_z) {
                            $zfb_data['to_user_id'] = $info_zfb['zfb_openid'];
                            $zfb_data['template'] = [
                                'template_id'=>$template_id_z,
                                'context'=>[
                                    'head_color'=>'#173177',
                                    'url'=>'https://a.weixiaoqu.com/index/account/index/xid/'.  jiami_id($info_zfb['xiaoquid']),
                                    'action_name'=>'查看详情',
                                    'first'=>['color'=>'#173177','value'=>$data['con']],
                                    'keyword1'=>['color'=>'#173177','value'=>'迁入审核'],
                                    'keyword2'=>['color'=>'#173177','value'=>$data['tg']],
                                    'keyword3'=>['color'=>'#173177','value'=>$data['butongguo']],
                                    'remark'=>['color'=>'#173177','value'=>'如有疑问可通过客服中心或客服电话联系客服人员'],
                                ],
                            ];
                        }
                        break;
                    default:
                        break;
                }
                if (!empty($zfb_data)) {
                    import('Ylfc.ZfbShh', EXTEND_PATH, '.class.php');
                    $zfbshh = new \ZfbShh($info_zfb['userid'],$info_zfb['xiaoquid']);
                    $res_info = $zfbshh->sendMuban($zfb_data);
                }
            }
        }

        if ($set['is_dx'] == 1) {//短信
//            $info = M('YzInfo')->where(array('id' => $yhid))->find();
            $info = Db::name('yz_info')->where(['id' => $yhid])->find();

            if (empty($info)) {
                return false;
            }
            if (!verfiy_phone($info['phone'])) {
                return false;
            }
            if (empty($data['xiaoquname'])) {
                $data['xiaoquname'] = Db::name('xq_info')->where(array('xiaoquid' => $info['xiaoquid']))->value('xiaoquname');
            }
            $infoa = Db::name('user_info')->field('smstotal,smsused')->where(array('userid' => $userid))->find(); //每次发生判断短信数量
            if ($infoa["smstotal"] - $infoa["smsused"] > 5) {//要有5条短信才允许发送

                switch ($type) {
                    case 1://意见报修name（住户名字）info（说明文字）
                        $itm = 'SmQzU4';
                        $send_dx['name'] = $data['name'];
                        $send_dx['info'] = $data['con'];
                        break;
                    case 6://客服通知  name业主姓名  con内容
                        $itm = 'i8uLx2';
                        $send_dx['name'] = $data['name'];
                        $send_dx['con'] = $data['con'];
                        break;
                    default:
                        break;
                }
                import('Ylfc.Send',EXTEND_PATH,'.class.php');
                $send = new send();

                $info_res = $send->sendWuye($itm, array($info['phone']), $send_dx);
                if($info_res['status']=='error'){

                }  else {
                    if (!empty($info_res)) {
                        foreach ($info_res as $sms_v) {
                            if ($sms_v['status'] == 'success') {
                                $sms_count += (int) $sms_v['fee'];
                            }
                        }
                    }
                }

                if ($sms_count > 0) {
//                    M('UserInfo')->where(array('userid' => $userid))->setInc('smsused', $sms_count);
                    Db::name('user_info')->where(['userid' => $userid])->setInc('smsused', $sms_count);
                    $log = new \app\api\model\UserLog();
                    $log->addLog('意见报修短信：' . $sms_count . '条', 6, $sms_count);
                }
            } else {
                $d['msgtype'] = 1;
                $d['msgstatus'] = 0;
                $d['userid'] = $userid;
                $d['msgtype2'] = 0;
                $d['msg'] = '<a href="/wuye/sms_combo">短信包不足，请尽快充值购买</a>';
                $d['title'] = '短信包不足，请尽快充值购买';
                $d['addtime'] = $now_time;
                Db::name('user_msg')->insert($d);
            }
        }


    }


    public function send_weixiu($address, $name, $phone, $the_content, $id,$weixiu_info) {
        if(empty($weixiu_info) || empty($weixiu_info['openid'])){
            return false;
        }
        import('Ylfc.Gzfuwu',EXTEND_PATH,'.class.php'); //引入公众服务类文件
        $fw = new \Gzfuwu();
        $access_token = $fw->get_access_token('wx31a76d5c5b8a9922');
        $te = new \app\api\model\Templat();
        $template_id = $te->getTemplate('wx31a76d5c5b8a9922', 'OPENTM401111343', $access_token);
        if ($template_id != false) {
            $param = '{"touser":"' . $weixiu_info['openid'] . '","template_id":"' . $template_id . '","url":"https://shifu.louguanjia.com/details/'.$id.'",';
            $param .='"data":{';
            $param .='"first": {';
            $param .='"value":"您好，您有以下事项需要处理",';
            $param .='"color":"#173177"';
            $param .='},';
            $param .='"keyword1": {';
            $param .='"value":"' . $address . '",';
            $param .='"color":"#173177"';
            $param .='},';
            $param .='"keyword2": {';
            $param .='"value":"' . $name . ' 电话：' . $phone . '",';
            $param .='"color":"#173177"';
            $param .='},';
            $param .='"keyword3": {';
            $param .='"value":"意见报修",';
            $param .='"color":"#173177"';
            $param .='},';
            $param .='"keyword4": {';
            $param .='"value":"' . $id . '",';
            $param .='"color":"#173177"';
            $param .='},';
            $param .='"keyword5": {';
            $param .='"value":"' . $the_content . '",';
            $param .='"color":"#173177"';
            $param .='},';
            $param .='"remark":{';
            $param .='"value":"请点击查看。",';
            $param .='"color":"#173177"';
            $param .='}';
            $param .='}';
            $param .='}';
            http_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token, $param);

            return true;
        }
    }


}