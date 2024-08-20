<?php

namespace Githen\LaravelEqxiu\Traits;

use Illuminate\Support\Arr;

trait BaseTrait
{
    public function oauthToken($refresh = false)
    {
        if (!$refresh && $token = cache($this->getSecretId())) {
            return $token;
        }
        $resp = $this->httpRequest('POST', 'base/oauth/token', [], 'tokenSignature');
        if ($resp['code'] != 200) {
            return '';
        }
        $ttl = $resp['map']['expires'] ?? 0;
        $token = $resp['map']['token'] ?? '';
        if (empty($token) || $ttl < 10) {
            return '';
        }
        cache([$this->getSecretId() => $token], $ttl - 5);
        return $token;
    }

    public function oauthCode($openId)
    {
        $resp = $this->httpRequest('POST', 'base/oauth/code', ['openId' => $openId]);
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['msg'] ?? 'success', $resp['obj'] ?? '');
    }

    public function corpStaffList($params = [])
    {
        $resp = $this->httpRequest('GET', 'base/corp/staff/list', $params);
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['msg'] ?? 'success', [
            'pageNo' => $resp['map']['pageNo'] ?? 0,
            'count' => $resp['map']['count'] ?? 0,
            'pageSize' => $resp['map']['pageSize'] ?? 0,
            'end' => $resp['map']['end'] ?? true,
            'items' => $resp['list'] ?? []
        ]);
    }

    public function corpDeptList($params = [])
    {
        $resp = $this->httpRequest('GET', 'base/corp/dept/list', [], 'token');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['msg'] ?? 'success', [
            'pageNo' => $resp['map']['pageNo'] ?? 0,
            'count' => $resp['map']['count'] ?? 0,
            'pageSize' => $resp['map']['pageSize'] ?? 0,
            'end' => $resp['map']['end'] ?? true,
            'items' => $resp['list'] ?? []
        ]);
    }

    public function secretDetail($openId)
    {
        $resp = $this->httpRequest('GET', 'base/secret/detail', ['openId' => $openId], 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['msg'] ?? 'success', $resp['map'] ?? []);
    }

    public function poductCorpList($params = [])
    {
        $resp = $this->httpRequest('GET', 'base/product/corpList', [], 'signature');
        return $this->message($resp['code'] != 200 ? '2000' : '0000', $resp['msg'] ?? 'success', [
            'pageNo' => $resp['map']['pageNo'] ?? 0,
            'count' => $resp['map']['count'] ?? 0,
            'pageSize' => $resp['map']['pageSize'] ?? 0,
            'end' => $resp['map']['end'] ?? true,
            'items' => $resp['list'] ?? []
        ]);
    }

}
