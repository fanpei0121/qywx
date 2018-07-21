<?php
namespace Ylfc;

class QiyeWxSq {
    private $suite_id = 'XXXXXXXXXXXXXXXX';
    private $redirect_uri = 'XXXXXXXXXXXXXXXXX';
    private $suite_ticket;
    private $suite_secret = 'XXXXXXXXXXXXXXXXXXXXXXXXXX';
    public $userid;

    public function __construct($userid)
    {
        if(empty($userid)){
            return ['errcode'=>1, 'msg'=>'参数错误'];
        }
        $this->userid = $userid;
        $this->suite_ticket = \think\Db::name('only')->where('id', 6)->value('value');
    }


    /**获取第三方应用凭证suite_access_token
     *
     */
    private function get_suite_access_token(){
        $info = \think\Db::name('only')->where('id', 7)->field('lasttime,value')->find();
        if(time() - $info['lasttime'] < 7200){
            return $info['value'];
        }
        $data['suite_id'] = $this->suite_id;
        $data['suite_secret'] = $this->suite_secret;
        $data['suite_ticket'] = $this->suite_ticket;
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = http_post('https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token', $data);
        $res = json_decode($res, true);
        if($res['errcode'] == 0){
            \think\Db::name('only')->where('id', 7)->update([
                'lasttime'=>time(),
                'value'=>$res['suite_access_token']
            ]);
            return $res['suite_access_token'];
        }else{
            return '';
        }
    }
            /**获取企业微信服务商provider_access_token
     *
     */
    public function get_provider_access_token(){
        $info = \think\Db::name('only')->where('id', 8)->field('lasttime,value')->find();
        if(time() - $info['lasttime'] < 7000){
            return $info['value'];
        }
        $data['corpid'] = 'wx00762921e3ee9cd4';
        $data['provider_secret'] = 'SZCOUz3d0BgNByVSGpnbui72bjivHN4y8lfonM4kSv8Mh6kWOlQ7twrm_-8mK1cq';
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = http_post('https://qyapi.weixin.qq.com/cgi-bin/service/get_provider_token', $data);
        $res = json_decode($res, true);
        if($res['errcode'] == 0){
            \think\Db::name('only')->where('id', 8)->update([
                'lasttime'=>time(),
                'value'=>$res['suite_access_token']
            ]);
            return $res['suite_access_token'];
        }else{
            return '';
        }
    }

    /**获取预授权码
     * @param $suite_access_token
     * @return array
     */
    private function get_pre_auth_code($suite_access_token){
        $res = http_get('https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token='.$suite_access_token);
        $res = json_decode($res, true);
        if($res['errcode'] == 0){
            return $res['pre_auth_code'];
        }else{
            return '';
        }
    }

    /**获取授权url
     * @return array
     */
    public function get_shouquan_url(){
        $suite_access_token = $this->get_suite_access_token();
        $pre_auth_code = $this->get_pre_auth_code($suite_access_token);
        $this->set_session_info($pre_auth_code, $suite_access_token);
        $state['userid'] = jiami_id($this->userid);
        $state['suite_access_token'] = $suite_access_token;
        $state = json_encode($state, JSON_UNESCAPED_UNICODE);
        $redirect_uri = urlencode($this->redirect_uri);
        $url = "https://open.work.weixin.qq.com/3rdapp/install?suite_id={$this->suite_id}&pre_auth_code={$pre_auth_code}&redirect_uri={$redirect_uri}&state={$state}";
        return ['errcode'=>0, 'url'=>$url];
    }

    /**设置授权配置
     * @param $pre_auth_code
     * @param $suite_access_token
     * @return array
     */
    private function set_session_info($pre_auth_code, $suite_access_token){
        $data['pre_auth_code'] = $pre_auth_code;
        $data['session_info'] = [
            'auth_type'=>1  //0 正式授权， 1 测试授权。 默认值为0
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = http_post('https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info?suite_access_token='.$suite_access_token, $data);
        $res = json_decode($res);
        return $res;
    }


    /**获取企业access_token
     *
     *
     */
    public function get_access_token(){
        $info = \think\Db::name('qiye_weixin')->where('userid', $this->userid)->field('access_token_addtime,access_token,corpid,permanent_code')->find();
        if(empty($info)){
            return '';
        }
        if(time() - $info['access_token_addtime'] > 5400){
            $data['auth_corpid'] = $info['corpid'];
            $data['permanent_code'] = $info['permanent_code'];
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            $suite_access_token = $this->get_suite_access_token();
            $res = http_post("https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token?suite_access_token={$suite_access_token}", $data);
            $res = json_decode($res,true);

            if (!empty($res['access_token'])) {
                \think\Db::name('qiye_weixin')->where('userid', $this->userid)->update(['access_token' => $res['access_token'], 'access_token_addtime' => time()]);
                return $res['access_token'];
            } else {
                return '';
            }
        }else{
            return $info['access_token'];
        }

    }


//--------------------------------------------------以下为pay域名下操作

    /**获取企业永久授权码及企业信息入库
     * @param $auth_code
     * @param $suite_access_token
     * @return array
     */
    public function get_permanent_code($auth_code, $suite_access_token){
        $data['auth_code'] = $auth_code;
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = http_post('https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token='.$suite_access_token, $data);
        $res = json_decode($res, true);
        if($res['errcode'] == 0){
            $r = $this->add_qywx($res);
            return $r;
        }else{
            return $res;
        }
    }

    /**企业微信数据入库
     *
     *
     */
    private function add_qywx($arr){
        $check = \think\Db::name('qiye_weixin')->where('corpid', $arr['auth_corp_info']['corpid'])->find();
        $add['userid'] = $this->userid;
        $add['access_token'] = $arr['access_token'];
        $add['access_token_addtime'] = time();
        $add['permanent_code'] = $arr['permanent_code'];
        $add['corpid'] = $arr['auth_corp_info']['corpid'];
        $add['corp_name'] = $arr['auth_corp_info']['corp_name'];
        $add['corp_type'] = $arr['auth_corp_info']['corp_type'];
        $add['corp_square_logo_url'] = $arr['auth_corp_info']['corp_square_logo_url'];
        $add['corp_wxqrcode'] = $arr['auth_corp_info']['corp_wxqrcode'];
        $add['agentid'] = $arr['auth_info']['agent'][0]['agentid'];
        $add['addtime'] = date("Y-m-d H:i:s", time());
        $add['other_info'] = json_encode($arr,JSON_UNESCAPED_UNICODE);
        if(!empty($check)){
            if($check['userid'] != $this->userid){
                return ['errcode'=>1, 'msg'=>'此企业微信已被其他账号使用'];
            }else{
                $r = \think\Db::name('qiye_weixin')->where('corpid', $arr['auth_corp_info']['corpid'])->update($add);
            }
        }else{
            $r = \think\Db::name('qiye_weixin')->insert($add);
        }
        if($r !== false){
            return ['errcode'=>0, 'msg'=>'企业微信信息入库成功', 'access_token'=>$arr['access_token']];
        }else{
            return ['errcode'=>1, 'msg'=>'企业微信信息入库失败'];
        }
    }
