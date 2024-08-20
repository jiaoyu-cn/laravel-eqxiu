<?php

namespace Githen\LaravelEqxiu\Traits;

trait UtilsTrait
{

    /**
     * 生成签名信息
     * @param array $params
     * @return string
     * @author nanjishidu
     */
    public function genSignature($params, $key = '')
    {
        unset($params['pvCount']);
        $params = array_values($params);
        if (empty($key)) {
            $params[] = $this->getAppKey();
        } else {
            $params[] = $key;
        }
        usort($params, 'strcoll');
        return hash('sha256', implode('', $params));
    }

}
