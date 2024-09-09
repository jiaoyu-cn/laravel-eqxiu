<?php

namespace Githen\LaravelEqxiu\Traits;


trait BaseTrait
{
    /** 获取令牌(Token)接口
     * @param boolean $refresh 是否刷新缓存
     * @return array|mixed
     * @author nanjishidu
     */
    public function oauthToken($refresh = false)
    {
        if (!$refresh && $token = cache($this->getSecretId())) {
            return ['code' => '0000', 'message' => 'success', 'data' => $token];
        }
        $resp = $this->httpRequest('POST', 'base/oauth/token', [], 'tokenSignature');
        if ($resp['code'] != 200) {
            return ['code' => '2000', 'message' => '令牌获取失败'];
        }
        $ttl = $resp['map']['expires'] ?? 0;
        $token = $resp['map']['token'] ?? '';
        if (empty($token) || $ttl < 10) {
            return ['code' => '2000', 'message' => '令牌已超时'];
        }
        cache([$this->getSecretId() => $token], $ttl - 10);
        return ['code' => '0000', 'message' => 'success', 'data' => $token];;
    }

    /** 登录授权码接口
     * @param string $openId 三方用户唯一标识
     * @return array|mixed
     * @author nanjishidu
     */
    public function oauthCode($openId)
    {
        $resp = $this->httpRequest('POST', 'base/oauth/code', ['openId' => $openId], 'token');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', $resp['obj'] ?? '');
    }

    /** 静默登录接口
     * @param string $code 三方客户端访问内容平台临时票据
     * @param string $redirectURL 页面跳转URL
     * @return array|mixed
     * @author nanjishidu
     */
    public function oauthLogin($code, $redirectURL = '')
    {
        $params = [
            'code' => $code
        ];
        !empty($redirectURL) && $params['redirectUrl'] = $redirectURL;
        $resp = $this->httpRequest('POST', 'base/oauth/login', $params);
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', $resp['obj'] ?? '');
    }

    /** 登录退出接口
     * @param string $openId 三方用户唯一标识
     * @return array|mixed
     * @author nanjishidu
     */
    public function oauthLogout($openId)
    {
        $resp = $this->httpRequest('POST', 'base/auth/logout', ['openId' => $openId], 'token');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', $resp['obj'] ?? '');
    }

    /** 将企业员工注册到内容中台
     * @param string $name 员工姓名
     * @param string $openId 三方用户唯一标识
     * @param int $roleId 角色id，默认为3(员工)
     * @param string $phone 手机号
     * @param int $sex 性别1男，2女
     * @return array|mixed
     * @author nanjishidu
     */
    public function corpStaffRegister($name, $openId, $roleId = 3, $phone = '', $sex = 0)
    {
        $params = [
            'name' => $name,
            'openId' => $openId,
            'roleId' => $roleId,
        ];
        !empty($phone) && $params['phone'] = $phone;
        !empty($sex) && $params['sex'] = $sex;
        $resp = $this->httpRequest('POST', 'base/oauth/staff/register', $params, 'token');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', $resp['obj']['staffId'] ?? '');
    }

    /** 分页查询员工列表
     * @param string $pageNo 页码
     * @param string $pageSize 每页数量
     * @param Long $deptId 部门ID
     * @param string $keyword 关键词
     * @return array|mixed
     * @author nanjishidu
     */
    public function corpStaffList($pageNo = 1, $pageSize = 10, $deptId = 0, $keyword = '')
    {
        $params = [
            'pageNo' => $pageNo,
            'pageSize' => $pageSize,
        ];
        !empty($deptId) && $params['deptId'] = $deptId;
        !empty($keyword) && $params['keyword'] = $keyword;
        $resp = $this->httpRequest('GET', 'base/corp/staff/list', $params);
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', $this->parsePaginationData($resp));
    }

    /** 员工删除
     * @param string $openIds 员工姓名
     * @return array|mixed
     * @author nanjishidu
     */
    public function corpStaffDelete($openIds)
    {
        $resp = $this->httpRequest('POST', 'base/corp/staff/delete?openIds=' . $openIds, []);
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', []);
    }


    /** 创建企业
     * @param string $name 企业名称
     * @param string $openId 企业在平台侧的唯一ID
     * @param Long $accountType 1-正式账号 2-试用账号
     * @param int $clearingType 1先结算，2后结算
     * @return array|mixed
     * @author nanjishidu
     */
    public function corpCreate($name, $openId, $accountType = 0, $clearingType = 0)
    {
        $params = [
            'name' => $name,
            'openId' => $openId,
        ];
        !empty($accountType) && $params['accountType'] = $accountType;
        !empty($clearingType) && $params['clearingType'] = $clearingType;
        $resp = $this->httpRequest('POST', 'base/corp/create', $params, 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', $resp['map'] ?? '');
    }

    /** 企业密钥（授权凭证）查询
     * @param string $openId 企业在平台侧的唯一ID
     * @return array|mixed
     * @author nanjishidu
     */
    public function secretDetail($openId)
    {
        $resp = $this->httpRequest('GET', 'base/secret/detail', ['openId' => $openId], 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', $resp['map'] ?? []);
    }

    /** 分页查询员工列表
     * @param string $pageNo 页码
     * @param string $pageSize 每页数量
     * @param string $accountType 账号类型,0:全部，1：正式，2：试用
     * @param string $status 企业状态，-6：全部，0停用，1正常，2关闭，3已过期
     * @return array|mixed
     * @author nanjishidu
     */
    public function productCorpList($pageNo = 1, $pageSize = 10, $accountType = 0, $status = 0)
    {
        $resp = $this->httpRequest('GET', 'base/product/corpList', [], 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success', $this->parsePaginationData($resp));
    }

    /** 停用企业
     * @param string $openId 企业在平台侧的唯一ID
     * @return array|mixed
     * @author nanjishidu
     */
    public function productCorpDisable($openId)
    {
        $resp = $this->httpRequest('POST', 'base/product/corp/disable', ['openId' => $openId], 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success');
    }

    /** 启用企业
     * @param string $openId 企业在平台侧的唯一ID
     * @return array|mixed
     * @author nanjishidu
     */
    public function productCorpEnable($openId)
    {
        $resp = $this->httpRequest('POST', 'base/product/corp/enable', ['openId' => $openId], 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success');
    }

    /** 更改企业有效期
     * @param string $openId 企业在平台侧的唯一ID
     * @param string $expires 过期时间
     * @return array|mixed
     * @author nanjishidu
     */
    public function productCorpExpire($openId, $expires)
    {
        $resp = $this->httpRequest('POST', 'base/product/corp/expire', ['openId' => $openId, 'expires' => $expires], 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success');
    }

    /** 更改子企业员工数
     * @param string $openId 企业在平台侧的唯一ID
     * @param int $count 可用员工数
     * @return array|mixed
     * @author nanjishidu
     */
    public function productCorpStaffCount($openId, $count)
    {
        $resp = $this->httpRequest('POST', 'base/product/corp/staffCount', ['openId' => $openId, 'count' => $count], 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['message'] ?? 'success');
    }
}
