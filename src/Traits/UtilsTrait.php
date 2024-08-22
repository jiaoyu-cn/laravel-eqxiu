<?php

namespace Githen\LaravelEqxiu\Traits;

trait UtilsTrait
{

    /**
     * 生成签名信息
     * @param array $params
     * @param string $key
     * @return string
     * @author nanjishidu
     */
    public function genSignature($params, $key = '')
    {
        // 不参与签名
        unset($params['pvCount'], $params['pageNo'], $params['pageSize']);
        $params = array_values($params);
        if (empty($key)) {
            $params[] = $this->getAppKey();
        } else {
            $params[] = $key;
        }
        usort($params, 'strcoll');
        return hash('sha256', implode('', $params));
    }

    /**
     * 解析列表信息
     * @param array $resp
     * @return array|mixed
     * @author nanjishidu
     */
    public function parsePaginationData($resp)
    {
        return [
            'pageNo' => $resp['map']['pageNo'] ?? 0,
            'count' => $resp['map']['count'] ?? 0,
            'pageSize' => $resp['map']['pageSize'] ?? 0,
            'end' => $resp['map']['end'] ?? true,
            'items' => $resp['list'] ?? []
        ];
    }
}
