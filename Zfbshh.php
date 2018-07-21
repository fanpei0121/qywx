<?php

import('Ylfc.Zfb.AopSdk', EXTEND_PATH);

class ZfbShh {
    private $gatewayUrl = 'xxxxxxxxxxxxxx';
    private $appId = 'xxxxxxxxxxxx';
    private $rsaPrivateKey = 'xxxxxxxxxxxxxKxxxxxxxxxxxxxxwX+1ScEhKKe4e6sDl0SRSA/JowVkbY8xUz/5NyI3E0Y28QKBgQDm0+m5RaQW8ispET26TFS/tWsuP+iqdRlKvP3MV6sTmybp+5LhYGj0u4Duc4g+u4gDl7KVV/cFKA2XUFlDcg0L8B7Orlhj/Zp7nutjqayMICLtP6aWS8kDurH/Jr/kel7kt4uTqGtgU3qDxLZGP85pfMiCQuht/6VEcyTyc2jHDQKBgQCdUVyJu2oSiYMAafMM1ec8t8zbWNX/BxHaqYQcU1I0N+epBM550KN3O744epzmlxPuk7d/PFccMOfAuCOUvE1TS4T/u/9pyX9blknw1iVZ2rD3ED6uXV2bhQMHXn65XoRDOtIqn3ZigUJWGlMPNVqwv0HqQMc5Wwm8akPLStn97wKBgQCKJ4G3QyhQF4efn9MbQv5ic7n/x36BL28A3ZbmC/630F/9IXaq8CJBgExN69Y6/dENnWjkm+6cJnnj+9JBXOzUHVbDC3SZ/DrPDLIER5SflchxyWvyfs+ELOTGOzIVFOzg5b5jlSUXVT95yG30I9JuLqJv3I3y8FTBYE1X0519rQKBgQCQCUJUyMdhmY3b/yWNhvtk2FQ0MK4eBfcO4U4YMSF+tgDg+4mgdqp0LLsJMxoc9g5FzfGgCTsokI5gIThSoeWacfafvx0nWlnFHWEtpKQmFNkaHEGHm+xsy+fuA1K7nhIzC9QJOl9F6Vs3Qnrx3cPfN/294PmHRhcv74uKwkm8+QKBgQDlp8a4550Ji92RRynF/kwzKV84OVDMGivrmQ2pNkBNXVon8llqFgOCEgUxo6AmPL2s5y7ilH4YBkBw3PTqMYXpm8vOTCcgSiLDuZTPXPGeklZCW+9SsaXimh3SYuTcYmxenEgnysKNOujqzgSAg0pEJ5MnierHyGnjSkJByNTzFA==';//私钥
    private $alipayrsaPublicKey = 'xxxxxxxxxxxxxxxxxxxxxxuUAmfzukPKw+rxBqQ3fvaT6ZY/kxlWC4f7JmtItC1XX+UlQPX1OHscuWfDJzYSpufknzfGUUrcCjj04BTd6A0S0dHgw7Uj5mORyIFsV+ym+/0PaBIuSg6RrLTP5RV2DCPo/fVXmPuV2Qs38VylYEsNHKzIFmlRF303ZDxVoSdIefbuFivVF4qyYFa7eB6WEsFyPo3hx1t1dm+xhvMyqb0fYOSpj4pl2fhwpnlz9S08mB5FWI3UWX/9AGDTIdAfzNXLIc42IfUePKJUk4Yru0KKpDQY+QIDAQAB';//公钥
    private $merchant_private_key = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx2EZE2x/mywjOMw3QAY9Oel9iLFgzPq4YlAMOEjWJpB4vTuyux7pddnCqtIRuXHvfPtJKZ0gslCIU6aZ1KJGto8n7SSKnHtjiYs0HpRJtZcb0tWxs8qeNgV7IphtNjT6nULY/xkm+RA+y03D6k7TaSlihUPx/daVbtCcuZouRN2mplbuw1Bw3uOfRVoo8fB6dDMJrM3L/dpakYQL/yhZm5DykxsF/tUnOgmgoVqoZZf4ZQsMNb4/oPqy8aqsaLQBc3YRx46uIwIDAQAB';
    private $apiVersion = '1.0';
    private $signType = 'RSA2';
    public  $postCharset = 'utf-8';
    private $format = 'json';
    public  $app_auth_token;
    public $user_id;//支付宝的商户id   2088开头的
    private $userid;
    private $xid;
    public $aop;

    public function __construct($userid,$xiaoquid) {
        if(empty($userid) || empty($xiaoquid)){
            return ['status'=>0,'sub_msg'=>'数据未传入','msg'=>'数据未传入'];
        }
        $this->userid = $userid;

        if(config('zfbsx')==1){//沙箱环境
            $this->gatewayUrl = 'xxxxxxxxxxxxx';
            $this->appId = 'xxxxxxxxxxxxxxxx';
            $this->rsaPrivateKey = 'xxxxxxxxxxxxxxxxxx3daP8dBsANAirVrHYRkTbH+bLCM4zDdABj056X2IsWDM+rhiUAw4SNYmkHi9O7K7Hul12cKq0hG5ce98+0kpnSCyUIhTppnUoka2jyftJIqce2OJizQelEm1lxvS1bGzyp42BXsimG02NPqdQtj/GSb5ED7LTcPqTtNpKWKFQ/H91pVu0Jy5mi5E3aamVu7DUHDe459FWijx8Hp0Mwmszcv92lqRhAv/KFmbkPKTGwX+1Sc6CaChWqhll/hlCww1vj+g+rLxqqxotAFzdhHHjq4jAgMBAAECggEAVDwICbzi5gG+6oH7JwanqpDH6PTlCkcDue7wH1rVNPvKCgm6qiO26Z5bNsFDAU6RSe2pfVmGP/F+BQROJdC6fmVZjTMIZEbQOQHFAFWFTHRe5lLuzsXCFiiY6pb27lPPA6MLDPGugNd+BlQbAFFEx183daVrmG+9Wk/sv9CBV6av2BVwsb2iiYEh2JYgjcuLm3kUwW3F40ZPjTJKBUh/3UwEvn5hOhKK54TXWkh59fzeUvoiQzPEQJs1hFusKU1u7+jHmCbwCKJpUtDKtCnqT0WZGCK38QUZq88PgLOwTUhF9kPGKKe4e6sDl0SRSA/JowVkbY8xUz/5NyI3E0Y28QKBgQDm0+m5RaQW8ispET26TFS/tWsuP+iqdRlKvP3MV6sTmybp+5LhYGj0u4Duc4g+u4gDl7KVV/cFKA2XUFlDcg0L8B7Orlhj/Zp7nutjqayMICLtP6aWS8kDurH/Jr/kel7kt4uTqGtgU3qDxLZGP85pfMiCQuht/6VEcyTyc2jHDQKBgQCdUVyJu2oSiYMAafMM1ec8t8zbWNX/BxHaqYQcU1I0N+epBM550KN3O744epzmlxPuk7d/PFccMOfAuCOUvE1TS4T/u/9pyX9blknw1iVZ2rD3ED6uXV2bhQMHXn65XoRDOtIqn3ZigUJWGlMPNVqwv0HqQMc5Wwm8akPLStn97wKBgQCKJ4G3QyhQF4efn9MbQv5ic7n/x36BL28A3ZbmC/630F/9IXaq8CJBgExN69Y6/dENnWjkm+6cJnnj+9JBXOzUHVbDC3SZ/DrPDLIER5SflchxyWvyfs+ELOTGOzIVFOzg5b5jlSUXVT95yG30I9JuLqJv3I3y8FTBYE1X0519rQKBgQCQCUJUyMdhmY3b/yWNhvtk2FQ0MK4eBfcO4U4YMSF+tgDg+4mgdqp0LLsJMxoc9g5FzfGgCTsokI5gIThSoeWacfafvx0nWlnFHWEtpKQmFNkaHEGHm+xsy+fuA1K7nhIzC9QJOl9F6Vs3Qnrx3cPfN/294PmHRhcv74uKwkm8+QKBgQDlp8a4550Ji92RRynF/kwzKV84OVDMGivrmQ2pNkBNXVon8llqFgOCEgUxo6AmPL2s5y7ilH4YBkBw3PTqMYXpm8vOTCcgSiLDuZTPXPGeklZCW+9SsaXimh3SYuTcYmxenEgnysKNOujqzgSAg0pEJ5MnierHyGnjSkJByNTzFA==';
            $this->alipayrsaPublicKey = 'xxxxxxxxxxxxxxxxxxxC4f7JmtItC1XX+UlQPX1OHscuWfDJzYSpufknzfGUUrcCjj04BTd6A0S0dHgw7Uj5mORyIFsV+ym+/0PaBIuSg6RrLTP5RV2DCPo/fVXmPuV2Qs38VylYEsNHKzIFmlRF303ZDxVoSdIefbuFivVF4qyYFa7eB6WEsFyPo3hx1t1dm+xhvMyqb0fYOSpj4pl2fhwpnlz9S08mB5FWI3UWX/9AGDTIdAfzNXLIc42IfUePKJUk4Yru0KKpDQY+QIDAQAB';
            $this->merchant_private_key = 'xxxxxxxxxxxxxxxxxxxxxywjOMw3QAY9Oel9iLFgzPq4YlAMOEjWJpB4vTuyux7pddnCqtIRuXHvfPtJKZ0gslCIU6aZ1KJGto8n7SSKnHtjiYs0HpRJtZcb0tWxs8qeNgV7IphtNjT6nULY/xkm+RA+y03D6k7TaSlihUPx/daVbtCcuZouRN2mplbuw1Bw3uOfRVoo8fB6dDMJrM3L/dpakYQL/yhZm5DykxsF/tUnOgmgoVqoZZf4ZQsMNb4/oPqy8aqsaLQBc3YRx46uIwIDAQAB';

        }

        $this->aop = new \AopClient();
        $this->aop->gatewayUrl = $this->gatewayUrl;
        $this->aop->appId = $this->appId;
        $this->aop->rsaPrivateKey = $this->rsaPrivateKey;
        $this->aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        $this->aop->apiVersion = $this->apiVersion;
        $this->aop->signType = $this->signType;
        $this->aop->postCharset = $this->postCharset;
        $this->aop->format = $this->format;
        if($xiaoquid>0){

            $this->xid = $xiaoquid;
            $info = \think\Db::table(config('zfb_db').'.wxq_zfb_shh_auth')->field('a.*')->alias('a')
                    ->join(config('zfb_db').'.wxq_zfb_xq_shh b','a.auth_app_id=b.auth_app_id')->where(['b.shh_xiaoquid'=>$xiaoquid])->find();
            if(!empty($info)){
                $this->app_auth_token = $info['app_auth_token'];
                $this->user_id = $info['user_id'];
            }
//            if(config('bendi')==1){
//            $this->app_auth_token = '201709BBc25483f41b9c4ef6abc3c061e97b4X17';//测试用
//            }
        }
    }
    //获取当前生效的配置,要获取配置信息new的时候userid 和zi_id 可以传递-1
    public function getConfig() {
        return[
            'appId'=>  $this->appId,
            'rsaPrivateKey'=>  $this->rsaPrivateKey,
            'alipayrsaPublicKey'=>  $this->alipayrsaPublicKey,
            'apiVersion'=>  $this->apiVersion,
            'signType'=>  $this->signType,
            'postCharset'=>  $this->postCharset,
            'format'=>  $this->format,
            'gatewayUrl'=>  $this->gatewayUrl,
            'merchant_private_key'=>  $this->merchant_private_key,
        ];
    }

 //创建生活号
    public function addShh($zfb_data) {
//        if(empty($zfb_data)){
//            return ['status'=>0,'sub_msg'=>'数据未传入','msg'=>'数据未传入'];
//        }

//        $zfb_data['out_biz_no'] = date('YmdHis').$this->userid.rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
//        Log::write($zfb_data['out_biz_no']);
//        $zfb_data['mcc_code'] = 'S_S02_6513';
//        $zfb_data['special_license_pic'] = "@".dirname(__file__)."/logo.png";
//        $zfb_data['business_license_no'] = '1532501100006302';
//        $zfb_data['business_license_pic'] = "@".dirname(__file__)."/logo.png";
//        $zfb_data['contact_name'] = '3EDMPR';
//        $zfb_data['contact_mobile'] = '15186869653';
//        $zfb_data['contact_email'] = '519782970@qq.com';
//        $zfb_data['public_name'] = '微小区生活号5';
//        $zfb_data['public_desc'] = '微小区生活号简介5';
//        $zfb_data['logo_pic'] = "@".dirname(__file__)."/logo.png";
//        $zfb_data['background_pic'] = "@".dirname(__file__)."/logo.png";
//        $zfb_data['account'] = '15186869653';

        $request = new AlipayOpenPublicLifeAgentCreateRequest();
        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
        $request->setOutBizNo($zfb_data['out_biz_no']);
        $request->setMccCode($zfb_data['mcc_code']);

        $request->setSpecialLicensePic($zfb_data['special_license_pic']);
        $request->setBusinessLicenseNo($zfb_data['business_license_no']);
        $request->setBusinessLicensePic($zfb_data['business_license_pic']);
        $request->setPreviewVersion('true');
//        $request->setBusinessLicenseAuthPic("@"."本地文件路径");
//        $request->setShopSignBoardPic("@"."本地文件路径");
//        $request->setShopScenePic("@"."本地文件路径");
        $request->setContactName($zfb_data['contact_name']);
        $request->setContactMobile($zfb_data['contact_mobile']);
        $request->setContactEmail($zfb_data['contact_email']);
        $request->setPublicName($zfb_data['public_name']);
        $request->setPublicDesc($zfb_data['public_desc']);
        $request->setLogoPic($zfb_data['logo_pic']);//log
        $request->setBackgroundPic($zfb_data['background_pic']);//背景图
//        $request->setOwnIntellectualPic();
        $request->setAccount($zfb_data['account']);

        $result = $this->aop->execute($request,null,  null);//这儿不需要第三个参数
//        Log::write(json_encode($result));
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        $res = $result->$responseNode; //返回的结果集
        $this->addLog($zfb_data_str,$res,16);
        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'out_biz_no'=>$result->$responseNode->out_biz_no
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }

 //查询生活号
    public function serShh($zfb_data) {
        if(empty($zfb_data)){
            return ['status'=>0,'sub_msg'=>'数据未传入','msg'=>'数据未传入'];
        }
//
        $request = new AlipayOpenPublicLifeAgentcreateQueryRequest();

//        $zfb_data['out_biz_no'] = '201709071704194805105130';


        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($zfb_data_str);
        $result = $this->aop->execute($request,null, null);//这儿不需要第三个参数

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;


        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'out_biz_no'=>$result->$responseNode->out_biz_no,
                'order_status_biz_desc'=>$result->$responseNode->order_status_biz_desc,
                'life_app_id'=>$result->$responseNode->life_app_id,
                'refused_reason'=>$result->$responseNode->refused_reason,
                'merchant_pid'=>$result->$responseNode->merchant_pid,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }
    //添加菜单
    public function addMenu($zfb_data,$button = array()) {
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }

        $request = new AlipayOpenPublicMenuCreateRequest(); //创建菜单

        $jiami = jiami_id($zfb_data['xiaoquid']);
        if(empty($button)){
        $button = [
            [
                'name' => '小区首页',
                'sub_button' => [
                    [
                        'name' => '小区公告',
                        'action_type' => 'link',
                        'action_param' => 'https://i.weixiaoqu.com/index/fun/notice_list/xid/'.$jiami,
                        'icon' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=yYX5unnKSK6ZgZNzg30iqgAAACMAAQED&amp;zoom=original',
                    ],
                    [
                        'name' => '物业缴费',
                        'action_type' => 'link',
                        'action_param' => 'https://i.weixiaoqu.com/index/user/cost/xid/'.$jiami,
                        'icon' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=IQx5XVdXRLC68Gnps6-aZQAAACMAAQED&amp;zoom=original',
                    ],
                    [
                        'name' => '客服中心',
                        'action_type' => 'link',
                        'action_param' => 'https://i.weixiaoqu.com/index/user/my_yijian/type/1/xid/'.$jiami,
                        'icon' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=bHQKO-heRfqiuhEU6izSBQAAACMAAQED&amp;zoom=original',
                    ],
                    [
                        'name' => '个人中心',
                        'action_type' => 'link',
                        'action_param' => 'https://i.weixiaoqu.com/index/user/index/xid/'.$jiami,
                        'icon' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=GOQE-bKiQeOP2fndF-yW2gAAACMAAQED&amp;zoom=original',
                    ],

                ]
            ],
            [
                'name' => '小区首页',
                'sub_button' => [
                    [
                        'name' => '小区商家',
                        'action_type' => 'link',
                        'action_param' => 'https://i.weixiaoqu.com/index/shop/index/xid/'.$jiami,
                        'icon' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=mMojNSuqTKuTZ8lNIAn6lgAAACMAAQED&zoom=original',
                    ],
                    [
                        'name' => '客服电话',
                        'action_type' => 'tel',
                        'action_param' => $zfb_data['phone'],
                        'icon' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=lG_LAoH5T8qtTrHmXE6i2AAAACMAAQED&amp;zoom=original',
                    ],
                    [
                        'name' => '积分商城',
                        'action_type' => 'link',
                        'action_param' => 'https://i.weixiaoqu.com/index/fun/jifenshop/xid/'.$jiami,
                        'icon' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=vbdU6ggCRRq-gPfFpvX-HQAAACMAAQED&amp;zoom=original',
                    ],
                    [
                        'name' => '投票表决',
                        'action_type' => 'link',
                        'action_param' => 'https://i.weixiaoqu.com/index/vote/vote_list/xid/'.$jiami,
                        'icon' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=RQ1pUcQ-Rwq8Qy-vZOYCdQAAACMAAQED&amp;zoom=original',
                    ],
                ]
            ],
        ];
        }

        $zfb_data['button'] = $button;
        $zfb_data['type'] = 'icon';
        unset($zfb_data['xiaoquid']);
        unset($zfb_data['phone']);
        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($zfb_data_str);
        $result = $this->aop->execute($request, null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        $res = $result->$responseNode; //返回的结果集
        $this->addLog($zfb_data_str, $res, 18);
        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status' => 1,
                'menu_key' => $result->$responseNode->menu_key,
            ];
        } else {//创建菜单失败就更新菜单
            $request2 = new AlipayOpenPublicMenuModifyRequest(); //更新菜单
            $request2->setBizContent($zfb_data_str);
            $result2 = $this->aop->execute($request2, null, $this->app_auth_token);
            $responseNode = str_replace(".", "_", $request2->getApiMethodName()) . "_response";
            $resultCode2 = $result2->$responseNode->code;
            if (!empty($resultCode2) && $resultCode2 == 10000) {
            return [
                'status' => 1,
                'menu_key' => $result2->$responseNode->menu_key,
            ];
        }
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }


//
//    public function delMenu() {
//        $request = new AlipayOpenPublicPersonalizedMenuDeleteRequest();
//        $zfb_data['menu_key'] = 'default';
//
//        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
//        $request->setBizContent($zfb_data_str);
//        $result = $this->aop->execute($request,null,  $this->app_auth_token);
//
//        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
//        $resultCode = $result->$responseNode->code;
//
//        if (!empty($resultCode) && $resultCode == 10000) {
//            return [
//                'status'=>1,
//                ];
//        } else {
//            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
//        }
//    }
//设置生活号模板消息行业
    public function setHy() {
        $request = new AlipayOpenPublicTemplateMessageIndustryModifyRequest();
        $zfb_data['primary_industry_name'] = '房地产/物业';
        $zfb_data['primary_industry_code'] = '10010/21002';
        $zfb_data['secondary_industry_code'] = '10001/20101';
        $zfb_data['secondary_industry_name'] = 'IT科技/互联网|电子商务';

        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($zfb_data_str);
        $result = $this->aop->execute($request,null,  $this->app_auth_token);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }

 //领取模板消息id
    public function getMuban($zfb_data) {
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOpenPublicTemplateMessageGetRequest();

//        $zfb_data['template_id'] = 'TM000000040';

        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($zfb_data_str);
        $result = $this->aop->execute($request,null,  $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'msg_template_id' => $result->$responseNode->msg_template_id,
                'template' => $result->$responseNode->template,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }
 //发送模板消息
    public function sendMuban($zfb_data) {
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOpenPublicMessageSingleSendRequest();

//        $zfb_data['to_user_id'] = '2088612211806441';
//        $zfb_data['template'] = [
//            'template_id' => 'd905fa60b99e47d7a95041c905a08582',
//            'context' => [
//                'head_color' => '#173177',
//                'url' => 'https://a.weixiaoqu.com/index/fun/yijian_detail/repair_id/34215/xid/82541176fe807b629e90c3e1206008a2',
//                'action_name' => '查看详情',
//                'first' => ['color' => '#173177', 'value' => '您好，您有新的物业反馈提醒：'],
//                'keyword1' => ['color' => '#173177', 'value' => '测试的一栋楼-1-12-'],
//                'keyword2' => ['color' => '#173177', 'value' => '意见报修'],
//                'keyword3' => ['color' => '#173177', 'value' => '已回复'],
//                'keyword4' => ['color' => '#173177', 'value' => '666'],
//                'keyword5' => ['color' => '#173177', 'value' => '管理员'],
//                'remark' => ['color' => '#173177', 'value' => '感谢您对我们提出宝贵建议，若在处理过程中有任何疑问请随时与业主中心联系。'],
//            ],
//        ];

        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($zfb_data_str);
        $result = $this->aop->execute($request,null,  $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'msg_template_id' => $result->$responseNode->msg_template_id,
                'template' => $result->$responseNode->template,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }
    //查询生活号信息
    public function serShhInfo() {
        $request = new AlipayOpenPublicInfoQueryRequest();
        $result = $this->aop->execute($request,null,  $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'app_name' => $result->$responseNode->app_name,
                'logo_url' => $result->$responseNode->logo_url,
                'public_greeting' => $result->$responseNode->public_greeting,
                'audit_status' => $result->$responseNode->audit_status,
                'audit_desc' => $result->$responseNode->audit_desc,
                'is_online' => $result->$responseNode->is_online,
                'is_release' => $result->$responseNode->is_release,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }
    //修改生活号信息
    public function editShhInfo($zfb_data) {
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $zfb_data['public_greeting'] = '欢迎访问微小区生活号';


        $request = new AlipayOpenPublicInfoModifyRequest ();
        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($zfb_data_str);
        $result = $this->aop->execute($request,null,  $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'result' => $result->$responseNode->result,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }
    //上架生活号
    public function shangJia() {
        $request = new AlipayOpenPublicLifeAboardApplyRequest();
        $result = $this->aop->execute($request,null,  $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'result' => $result->$responseNode->result,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }
    //下架生活号
    public function xiaJia() {
        $request = new AlipayOpenPublicLifeDebarkApplyRequest();
        $result = $this->aop->execute($request,null,  $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'result' => $result->$responseNode->result,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }
    //获取生活号二维码
    public function getErweima() {
        $request = new AlipayOpenPublicQrcodeCreateRequest();
        $zfb_data['show_logo'] = 'Y';
        $zfb_data['code_type'] = 'PERM';
        $zfb_data_str = json_encode($zfb_data, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($zfb_data_str);
        $result = $this->aop->execute($request,null,  $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'code_img' => $result->$responseNode->code_img,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->$responseNode->sub_msg,'msg'=>$result->$responseNode->msg,'code'=>$resultCode,'sub_code'=>$result->$responseNode->sub_code];
        }
    }

     //获取支付宝用户的user_id
    public function getUserId($zfb_data) {
        $request = new AlipaySystemOauthTokenRequest();
        $request->setGrantType("authorization_code");
        $request->setCode($zfb_data['auth_code']);
        $result = $this->aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

        $resultCode = $result->$responseNode->code;
        if(empty($resultCode)){
            $result->error_response->code;
        }
        if (!empty($resultCode) && $resultCode != 10000) {

            return ['status' => 0, 'sub_msg' => $result->error_response->sub_msg, 'msg' => $result->error_response->msg, 'code' => $resultCode, 'sub_code' => $result->error_response->sub_code];
        } else {
            return [
                'status' => 1,
                'user_id' => $result->$responseNode->user_id,
                'access_token' => $result->$responseNode->access_token,
                'expires_in' => $result->$responseNode->expires_in,
                'refresh_token' => $result->$responseNode->refresh_token,
                're_expires_in' => $result->$responseNode->re_expires_in,
            ];
        }
    }

    //获取支付宝用户的user_id
    public function getYhInfo($zfb_data) {
        $request = new AlipayUserInfoShareRequest();

        $result = $this->aop->execute($request,$zfb_data['access_token']);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status'=>1,
                'user_id' => $result->$responseNode->user_id,
                'avatar' => $result->$responseNode->avatar,
                'nick_name' => $result->$responseNode->nick_name,
                'gender' => $result->$responseNode->gender,
                ];
        } else {
            return ['status'=>0,'sub_msg'=>$result->error_response->sub_msg,'msg'=>$result->error_response->msg,'code'=>$resultCode,'sub_code'=>$result->error_response->sub_code];
        }
    }
        //下单
    public function addOrder($zfb_data) {
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        if (!empty($this->user_id)) {
            $zfb_data['sys_service_provider_id'] = $this->user_id;
        } else {
            return ['status' => 0, 'sub_msg' => '商户信息错误', 'msg' => '商户信息错误'];
        }
        $request = new AlipayTradeWapPayRequest();

        $request->setBizContent(json_encode($zfb_data,JSON_UNESCAPED_UNICODE));

        $result = $this->aop->pageExecute($request);
        return $result;
    }


    /**
     * @param $img_info ['img_name'=>'名字','local_path'=>'本地路径','img_ext'=>'后缀格式']
     * @return array
     * @throws Exception
     * 上传图片到支付宝服务器
     */
    public function uploadImgToAlipay($img_info)
    {
        if (empty($img_info)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOfflineMaterialImageUploadRequest ();
        $request->setImageType($img_info['img_ext']);
        $request->setImageName($img_info['img_name']);
        $request->setImageContent('@' . $img_info['local_path']);
        $result = $this->aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            //删除本地图片
            unlink($img_info['local_path']);
            return [
                'status' => 1,
                'image_id' => $result->$responseNode->image_id,
                'image_url' => $result->$responseNode->image_url
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }

    /**
     * @param $img_path 图片路径
     * @return array
     * @throws Exception
     * 上传图片到支付宝服务器
     */
    public function uploadImgToAlipay2($img_path)
    {
        if (empty($img_path)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $img_arr = explode('.', basename($img_path));
        $img_info['img_ext'] = $img_arr[1];
        $img_info['local_path'] = $img_path;
        $img_info['img_name'] = $img_arr[0];
        $request = new AlipayOfflineMaterialImageUploadRequest();
        $request->setImageType($img_info['img_ext']);
        $request->setImageName($img_info['img_name']);
        $request->setImageContent('@' . $img_info['local_path']);
        $result = $this->aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            //删除本地图片
            unlink($img_info['local_path']);
            return [
                'status' => 1,
                'image_id' => $result->$responseNode->image_id,
                'image_url' => $result->$responseNode->image_url
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }

    /**
     * @param $arr
     *  /* $arr=[
     *   'msg_type'=>'类型:text|image-text',
     *   'articles' => [ //图文消息
     *      0 => ['title' => '标题', 'desc' => '内容', 'image_url' => '多图文第一张图片地址', 'url' => '跳转链接'],
     *      1 => [],
     *      2 => [],
     *      ],
     *   'text'=>['title'=>'标题','content'=>'内容'] //文字类型
     *   ];
     * @return array
     * @throws Exception
     * 群发消息
     */
    public function sendMsgByGroup($arr)
    {
        if (empty($arr)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOpenPublicMessageTotalSendRequest ();
        $request->setBizContent(json_encode($arr, JSON_UNESCAPED_UNICODE));

        $result = $this->aop->execute($request,null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status' => 1,
                'msg_id' => $result->$responseNode->message_id,
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }
    /*
     * 撤回消息消息
     */
    public function chehuiMsg($arr)
    {
        if (empty($arr)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOpenPublicLifeMsgRecallRequest();
        $request->setBizContent(json_encode($arr, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute($request,null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return [
                'status' => 1,
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }


    /**
     * 添加api操作日志
     * @param $zfb_data 请求数据
     * @param $res 响应数据
     * @param $log_type 类型
     */
    private function addLog($zfb_data, $res, $log_type)
    {
        $log['request'] = $zfb_data;
        $log['response'] = json_encode($res,JSON_UNESCAPED_UNICODE);

        $getdata = json_decode($log['response'],true);
        if($getdata['code']==10000){
            $log['log_code'] = 1;
        }  else {
            $log['log_code'] = 0;
        }
        $log['log_type'] = $log_type;
        $log['userid'] = (int)($this->userid);
        $log['addtime'] = NOW_TIME_Y;
        \think\Db::table(config('zfb_db').'.wxq_zfb_api_log')->insert($log);
    }

    /**添加广告
     * @param $zfb_data
     * @return array
     */
    public function addGuangGao($zfb_data){
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOpenPublicAdvertCreateRequest ();

        /*
        $zfb_data['advert_items'] = [
            [
                'img_url' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=RQ1pUcQ-Rwq8Qy-vZOYCdQAAACMAAQED&amp;zoom=original', //图片大小996*240
                'link_url' => 'https://www.alipay.com'
            ],
            [
                'img_url' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=RQ1pUcQ-Rwq8Qy-vZOYCdQAAACMAAQED&amp;zoom=original',
                'link_url' => 'http://www.baidu.com'
            ]
        ];
        */

        $request->setBizContent(json_encode($zfb_data, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute ( $request,null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return [
                'status' => 1,
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }

    /**修改广告
     * @param $zfb_data
     * @return array
     */
    public function editGuangGao($zfb_data){
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOpenPublicAdvertModifyRequest ();

        /*
        $zfb_data['advert_id'] = '123';   //广告位id
        $zfb_data['advert_items'] = [
            [
                'img_url' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=RQ1pUcQ-Rwq8Qy-vZOYCdQAAACMAAQED&amp;zoom=original',
                'link_url' => 'https://www.alipay.com'
            ]
        ];
        */

        $request->setBizContent(json_encode($zfb_data, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute ( $request, null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return [
                'status' => 1,
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }


    /**查询广告位
     * @return array
     */
    public function queryGuangGao(){
        $request = new AlipayOpenPublicAdvertBatchqueryRequest ();
        $result = $this->aop->execute ( $request, null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            $info = $result->$responseNode->advert_list;
            return [
                'status' => 1,
                'advert_id' =>$info[0]->advert_id,
                'advert_items' => $info[0]->advert_items
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }

    /**删除广告位
     * @param $zfb_data
     * @return array
     */
    public function delGuangGao($zfb_data){
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOpenPublicAdvertDeleteRequest ();

        //$zfb_data = ['advert_id' => '123'];  //广告位id

        $request->setBizContent(json_encode($zfb_data, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute ( $request, null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return [
                'status' => 1,
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }

    /**
     * 创建支付宝卡券
     * @param $arr
     * @return array
     */
    public function createAliCard($arr)
    {
        if (empty($arr)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        if ($arr['card_type'] == 1) {
            $len = 11;
        } else {
            $len = 6;
        }
        $card_arr = [
            'request_id' => creat_auth_code(),
            'card_type' => 'OUT_MEMBER_CARD',
            'biz_no_suffix_len' => $len, //后缀长度
            'write_off_type' => 'qrcode',
            'template_style_info' => [//模板样式信息
                'card_show_name' => $arr['sh_name'],
                'logo_id' => $arr['logo_img'],
                'background_id' => $arr['bg_img'],
                'bg_color' => 'rgb(55,112,179)',
            ],
            'column_info_list' => [
                [
                    'code' => 'BENEFIT_INFO',
                    'operate_type' => 'openWeb',
                    'title' => '小区公告',
                    'value'=>'查看小区公告',
                    'more_info' => [
                        'title' => '查看小区公告',
                        'url' => 'https://i.weixiaoqu.com/index/fun/notice_list/xid/' . $arr['xiaoquid']
                    ],
                ],
                [
                    'code' => 'BENEFIT_INFO',
                    'operate_type' => 'openWeb',
                    'title' => '物业缴费',
                    'value'=>'缴纳物业缴费',
                    'more_info' => [
                        'title' => '缴纳物业缴费',
                        'url' => 'https://i.weixiaoqu.com/index/cost/unpay/xid/' . $arr['xiaoquid']
                    ],
                ],
                [
                    'code' => 'BENEFIT_INFO',
                    'operate_type' => 'openWeb',
                    'title' => '客服中心',
                    'value'=>'进入客服中心',
                    'more_info' => [
                        'title' => '进入客服中心',
                        'url' => 'https://i.weixiaoqu.com/index/fun/yijian_list/xid/' . $arr['xiaoquid']
                    ],
                ],
                [
                    'code' => 'BENEFIT_INFO',
                    'operate_type' => 'openWeb',
                    'title' => '个人中心',
                    'value'=>'进入个人中心',
                    'more_info' => [
                        'title' => '进入个人中心',
                        'url' => 'https://i.weixiaoqu.com/index/account/index/xid/' . $arr['xiaoquid']
                    ],
                ],

            ],
            'field_rule_list'=>[
                [
                    'field_name'=>'Balance',
                    'rule_name'=>'ASSIGN_FROM_REQUEST',
                    'rule_value'=>'Balance'
                ]
            ]
        ];
//        echo json_encode($card_arr, JSON_UNESCAPED_UNICODE);die;
        $request = new AlipayMarketingCardTemplateCreateRequest ();
        $request->setBizContent(json_encode($card_arr, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute($request,null,$this->app_auth_token);
        //20171211000000000671202000300229 dump($result);die;
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return ['status' => 1, 'card_id' => $result->$responseNode->template_id];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }


    /**
     * 修改支付宝卡券
     * @param $arr
     * @return array
     */
    public function editAliCard($arr)
    {
        if (empty($arr)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
//        $arr['logo_img'] = 'WO6qrM-rRiuy5_UAmmbMtAAAACMAAQED';
//        $arr['bg_img'] = 'CKL0PsdmTqCeh-TC-DxgewAAACMAAQED';
//        $arr['card_id'] = '20171211000000000673275000300226';
//        $arr['sh_name'] = '微小区';
        $card_arr = array(
            'request_id' => creat_auth_code(),
            'template_id' => $arr['card_id'],
//            'write_off_type' => 'mdqrcode',
            'write_off_type' => 'qrcode',
            'template_style_info' => [//模板样式信息
                'card_show_name' => $arr['sh_name'],
                'logo_id' => $arr['logo_img'],
                'background_id' => $arr['bg_img'],
                'bg_color' => 'rgb(55,112,179)',
            ],
            'column_info_list' => [
                [
                    'code' => 'BENEFIT_INFO',
                    'operate_type' => 'openWeb',
                    'title' => '小区公告',
                    'value'=>'查看小区公告',
                    'more_info' => [
                        'title' => '查看小区公告',
                        'url' => 'https://i.weixiaoqu.com/index/fun/notice_list/xid/' . $arr['xiaoquid']
                    ],
                ],
                [
                    'code' => 'BENEFIT_INFO',
                    'operate_type' => 'openWeb',
                    'title' => '物业缴费',
                    'value'=>'缴纳物业缴费',
                    'more_info' => [
                        'title' => '缴纳物业缴费',
                        'url' => 'https://i.weixiaoqu.com/index/cost/unpay/xid/' . $arr['xiaoquid']
                    ],
                ],
                [
                    'code' => 'BENEFIT_INFO',
                    'operate_type' => 'openWeb',
                    'title' => '客服中心',
                    'value'=>'进入客服中心',
                    'more_info' => [
                        'title' => '进入客服中心',
                        'url' => 'https://i.weixiaoqu.com/index/fun/yijian_list/xid/' . $arr['xiaoquid']
                    ],
                ],
                [
                    'code' => 'BENEFIT_INFO',
                    'operate_type' => 'openWeb',
                    'title' => '个人中心',
                    'value'=>'进入个人中心',
                    'more_info' => [
                        'title' => '进入个人中心',
                        'url' => 'https://i.weixiaoqu.com/index/account/index/xid/' . $arr['xiaoquid']
                    ],
                ],

            ],
            'field_rule_list' => [
                [
                    'field_name' => 'Balance',
                    'rule_name' => 'ASSIGN_FROM_REQUEST',
                    'rule_value' => 'Balance'
                ]
            ],
//            'mdcode_notify_conf'=>[
//                'url'=>'https://a.weixiaoqu.com/index/account/index/xid/'.$arr['xiaoquid'],
//                'ext_params'=>[
//                    'openid'=>1,
//                    'yhid'=>2
//                ],
//            ]
        );
        echo json_encode($card_arr, JSON_UNESCAPED_UNICODE);die;
        $request = new AlipayMarketingCardTemplateModifyRequest ();
        $request->setBizContent(json_encode($card_arr, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute($request,null,$this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return ['status' => 1, 'card_id' => $result->$responseNode->template_id];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }


    /**
     * 创建卡券表单模板
     * @return array
     */
    public function createFormTemplate($template_id)
    {
        $card_arr = [
            'template_id'=>$template_id,
            'fields'=>[
                'optional'=>['required'=>['OPEN_FORM_FIELD_MOBILE','OPEN_FORM_FIELD_NAME']]
            ]
        ];
        $request = new AlipayMarketingCardFormtemplateSetRequest();
        $request->setBizContent(json_encode($card_arr, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute($request,null,$this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return ['status' => 1];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }

    /**
     * 获取会员卡投放url
     * @param $template_id
     * @return array
     */
    public function getAliCardUrl($template_id)
    {
        $card_arr = [
            'template_id' => $template_id,
            'callback' => 'https://i.weixiaoqu.com'
        ];
        $request = new AlipayMarketingCardActivateurlApplyRequest();
        $request->setBizContent(json_encode($card_arr, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute($request,null,$this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return ['status' => 1, 'url' => $result->$responseNode->apply_card_url];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }

    }

    /*public function showAliCard($template_id)
    {
        $card_arr = ['template_id'=>$template_id];
        $request = new AlipayMarketingCardTemplateQueryRequest ();
        $request->setBizContent(json_encode($card_arr, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute ( $request);
        $r = json_decode(json_encode($result),true);
        dump($r);
    }*/


    /**添加营销位
     * @return array
     */
    public function addTopic($zfb_data){
//        Log::write(json_encode($zfb_data));
        if (empty($zfb_data)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $request = new AlipayOpenPublicTopicCreateRequest();
        /*
        $zfb_data = [
            'title'=>'营销位名称',
            'topic_items' => [    营销位内容，数量限制：大于4个，小于8个
                [
                    'title' => '内容标题',
                    'sub_title' => '内容说明',
                    'img_url' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=RQ1pUcQ-Rwq8Qy-vZOYCdQAAACMAAQED&amp;zoom=original',
                    'link_url' => 'https://www.weixiaoqu.com'
                ],
                [
                    'title' => '内容标题',
                    'sub_title' => '内容说明',
                    'img_url' => 'https://oalipay-dl-django.alicdn.com/rest/1.0/image?fileIds=RQ1pUcQ-Rwq8Qy-vZOYCdQAAACMAAQED&amp;zoom=original',
                    'link_url' => 'https://www.weixiaoqu.com'
                ]
            ]
        ];
        */
        $request->setBizContent(json_encode($zfb_data, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute($request,null, $this->app_auth_token);
//        Log::write(json_encode($result));
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return [
                'status' => 1
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }


    /**查询营销位
     * @return array
     */
    public function queryTopic(){
        $request = new AlipayOpenPublicTopicBatchqueryRequest();
        $result = $this->aop->execute($request, null, $this->app_auth_token);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){

            return [
                'status' => 1,

                'topic_list' => $result->$responseNode->topic_list

            ];
        } else {
            return ['status' => 0,
                'sub_msg' => $result->$responseNode->sub_msg,
                'msg' => $result->$responseNode->msg, 'code' => $resultCode,
                'sub_code' => $result->$responseNode->sub_code];
        }
    }

    /**删除营销位
     * @return array
     */
    public function delTopic($topic_id){
        if (empty($topic_id)) {
            return ['status' => 0, 'sub_msg' => '数据未传入', 'msg' => '数据未传入'];
        }
        $zfb_data['topic_id'] = $topic_id;
        $request = new AlipayOpenPublicTopicDeleteRequest ();
        $request->setBizContent(json_encode($zfb_data, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute ( $request, null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return [
                'status' => 1
            ];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }
    /**生活号粉丝
     * @return array
     */
    public function fensi(){
        $zfb_data['begin_date'] = date('Ymd', time()-86400*16);
        $zfb_data['end_date'] = date('Ymd', time()-86400);
        $request = new AlipayOpenPublicUserDataBatchqueryRequest();
        $request->setBizContent(json_encode($zfb_data, JSON_UNESCAPED_UNICODE));
        $result = $this->aop->execute ( $request, null, $this->app_auth_token);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return ['data'=>json_decode(json_encode($result->$responseNode->data_list),true),'status' => 1];
        } else {
            return ['status' => 0, 'sub_msg' => $result->$responseNode->sub_msg, 'msg' => $result->$responseNode->msg, 'code' => $resultCode, 'sub_code' => $result->$responseNode->sub_code];
        }
    }

}
 