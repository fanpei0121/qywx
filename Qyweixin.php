<?php
namespace Ylfc;

class Qyweixin
{
    /**
     * 创建部门
     * @param $data 部门信息还有主键id
     * @param $access_token
     * @return bool
     */
    public function creat_department($data, $access_token)
    {
        $qy_pid = $data['wx_parent_id'];
        $dep_id = $data['wx_dep_id'];
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=' . $access_token . '';
        $post = [
            'name' => $data['dep_name'],
            'parentid' => $qy_pid,
            'order' => $dep_id,
            'id' => $dep_id
        ];
        $str = json_encode($post, JSON_UNESCAPED_UNICODE);
        $res = json_decode(http_post($url, $str), true);
        return $res;
    }

    /**
     * 批量创建部门
     * @param $dep_list 二维数组
     * @param $access_token
     * @return bool
     */
    public function create_batch_department($dep_list, $access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token=' . $access_token . '';
        foreach ($dep_list as $item) {
            $post = [
                'name' => $item['dep_name'],
                'parentid' => $item['wx_parent_id'],
                'order' => $item['wx_dep_id'],
                'id' => $item['wx_dep_id']
            ];
            $res = json_decode(http_post($url, json_encode($post, JSON_UNESCAPED_UNICODE)), true);
            if ($res['errcode'] != 0) {
                return false;
            }
        }
        return true;
    }


    /**
     * 更新部门
     * @param $data 部门名称和id
     * @param $access_token
     * @return bool
     */
    public function update_department($data, $access_token)
    {
        $dep_id = $data['wx_dep_id'];
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token=' . $access_token . '';
        $post = [
            'name' => $data['dep_name'],
            'id' => $dep_id
        ];
        $res = json_decode(http_post($url, json_encode($post, JSON_UNESCAPED_UNICODE)), true);
        if ($res['errcode'] != 0) {
            return false;
        }
        return true;
    }

    /**
     * 删除部门
     * @param $wx_dep_id 部门id
     * @param $access_token
     * @return bool
     */
    public function del_department($wx_dep_id, $access_token)
    {
        $dep_id = $wx_dep_id;
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=' . $access_token . '&id=' . $dep_id . '';
        $res = json_decode(http_get($url), true);
        if ($res['errcode'] != 0) {
            return false;
        }
        return true;
    }

    /**
     * 批量删除部门
     * @param $dep_ids 部门id一维数组
     * @param $access_token
     * @return bool
     */
    public function del_batch_department($dep_ids, $access_token)
    {
        foreach ($dep_ids as $dep_id) {
            $url = 'https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token=' . $access_token . '&id=' . $dep_id . '';
            $res = json_decode(http_get($url), true);
            if ($res['errcode'] != 0) {
                return false;
            }
        }
        return true;
    }
    /**
     * 获取部门
     * @param $data 部门名称和id
     * @param $access_token
     * @return bool
     */
    public function get_department($access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token='.$access_token;

        $res = json_decode(http_get($url), true);
        return $res;
    }

    /**
     * 创建员工
     * @param $data 员工信息包括主键id
     * @param $dep_ids 部门id数组
     * @param $access_token
     * @return bool
     */
    public function creat_staff($data, $dep_ids, $access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=' . $access_token . '';
        $post = [
            'userid' => $data['wx_user_id'],
            'name' => $data['user_name'],
            'department' => $dep_ids,
            'position' => $data['position'],
            'mobile' => $data['phone'],
            'email' => $data['email'],
        ];
        $str = json_encode($post, JSON_UNESCAPED_UNICODE);
        $res = json_decode(http_post($url, $str), true);
        return $res;
    }

    /**
     * 批量创建员工
     * @param $staff_list 二维数组,包括的了部门id一维数组
     * @param $access_token
     * @return bool
     */
    public function creat_batch_staff($staff_list, $access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token=' . $access_token . '';
        foreach ($staff_list as $item) {
            $post = [
                'userid' => $item['wx_user_id'],
                'name' => $item['user_name'],
                'department' => $item['dep_id'],
                'position' => $item['position'],
                'mobile' => $item['phone'],
                'email' => $item['email'],
            ];
            $res = json_decode(http_post($url, json_encode($post, JSON_UNESCAPED_UNICODE)), true);
            if ($res['errcode'] != 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * 更新员工信息
     * @param $data
     * @param $dep_ids
     * @param $access_token
     * @return bool
     */
    public function update_staff($data, $dep_ids, $access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=' . $access_token . '';
        $post = [
            'userid' => $data['wx_user_id'],
            'name' => $data['user_name'],
            'department' => $dep_ids,
            'position' => $data['position'],
            'mobile' => $data['phone'],
            'email' => $data['email'],
        ];
        $res = json_decode(http_post($url, json_encode($post, JSON_UNESCAPED_UNICODE)), true);
        if ($res['errcode'] != 0) {
            return false;
        }
        return true;
    }

    /**
     * 删除员工
     * @param $user_id
     * @param $access_token
     * @return bool
     */
    public function del_staff($wx_user_id, $access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token=' . $access_token . '&userid=' . $wx_user_id . '';
        $res = json_decode(http_get($url), true);
        if ($res['errcode'] != 0) {
            return false;
        }
        return true;
    }

    /**
     * 批量删除员工
     * @param $staff_ids 员工id一维数组
     * @param $access_token
     * @return bool
     */
    public function del_batch_staff($staff_ids, $access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token=' . $access_token . '';
        $post = [
            'useridlist' => $staff_ids
        ];
        $res = json_decode(http_post($url, json_encode($post, JSON_UNESCAPED_UNICODE)), true);
        if ($res['errcode'] != 0) {
            return false;
        }
        return true;
    }
    /**
     * 批量获取员工
     * @param department_id 部门id
     * @param $access_token
     * @return bool
     */
    public function get_batch_staff($department_id, $access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token='.$access_token.'&department_id='.$department_id.'&fetch_child=1';
        $res = json_decode(http_get($url), true);
        return $res;
    }

    /**
     * 获取指定员工的信息
     * @param $user_id
     * @param $access_token
     * @return bool|mixed
     */
    public function get_staff_info($user_id, $access_token)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token . '&userid=' . $user_id . '';
        $res = json_decode(http_get($url), true);
        if ($res['errcode'] != 0) {
            return false;
        }
        return $res;
    }

    /**
     * 发送文本消息
     * @param $agentid 应用id
     * @param $access_token
     * @param $ids 部门或者员工id 一维数组
     * @param $content 消息内容
     * @param int $type 1员工2部门3所有
     * @return bool
     */
    public function send_text_msg($agentid, $access_token, $ids, $content, $type = 1)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=' . $access_token . '';
        $post = [
            'msgtype' => 'text',
            'agentid' => $agentid,
            'text' => [
                'content' => $content
            ]
        ];
        if ($type == 1) {
            $post['touser'] = implode('|', $ids);
        }
        if ($type == 2) {
            $post['toparty'] = implode('|', $ids);
        }
        if ($type == 3) {
            $post['touser'] = '@all';
        }
        $res = json_decode(http_post($url, json_encode($post, JSON_UNESCAPED_UNICODE)), true);
        if ($res['errcode'] != 0) {
            return false;
        }
        return true;
    }

    /**
     * 发送文本卡片消息
     * @param $agentid 应用id
     * @param $access_token
     * @param $ids 部门或者员工id 一维数组
     * @param $content 消息内容数组 title  description  url
     * @param int $type 1员工2部门3所有
     * @return bool
     */
    public function send_text_card($agentid, $access_token, $ids, $content, $type = 1)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=' . $access_token . '';
        $post = [
            'msgtype' => 'textcard',
            'agentid' => $agentid,
            'textcard' => [
                'title' => $content['title'],
                'description' => $content['description'],
                'url' => $content['url']
            ]
        ];
        if ($type == 1) {
            $post['touser'] = implode('|', $ids);
        }
        if ($type == 2) {
            $post['toparty'] = implode('|', $ids);
        }
        if ($type == 3) {
            $post['touser'] = '@all';
        }
        $res = json_decode(http_post($url, json_encode($post, JSON_UNESCAPED_UNICODE)), true);
        return $res;
    }


}