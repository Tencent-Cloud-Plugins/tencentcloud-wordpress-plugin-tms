<?php
/*
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace TencentWordpressTMS;
class TencentWordpressTMSOptions
{
    //使用全局密钥
    const GLOBAL_KEY = 0;
    //使用自定义密钥
    const CUSTOM_KEY = 1;

    //系统审核通过还需要人工审核
    const ALLOW_TO_REVIEW = 1;
    //系统审核通过直接批准
    const ALLOW_TO_PASS = 0;

    //系统审核不通过移到回收站
    const FAIL_TO_RECYCLE_BIN = 'recycle_bin';
    //系统审核不通过转为垃圾评论
    const FAIL_TO_TRASH = 'trash';
    //系统审核不通过不允许提交
    const FAIL_TO_FORBID_SUBMISSION = 'forbid_submission';

    private $secretID;
    private $secretKey;
    private $allowOption;
    private $failOption;
    private $customKey;
    private $whitelist = [];

    public function __construct($customKey = self::GLOBAL_KEY, $secretID = '', $secretKey = '',
                                $allowOption = self::ALLOW_TO_PASS, $failOption = self::FAIL_TO_FORBID_SUBMISSION, $whitelist = '')
    {
        $this->customKey = $customKey;
        $this->secretID = $secretID;
        $this->secretKey = $secretKey;
        $this->allowOption = $allowOption;
        $this->failOption = $failOption;
        $this->whitelist = $whitelist;
    }

    /**
     * 获取全局的配置项
     */
    public function getCommonOptions()
    {
        return get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
    }

    public function setSecretID($secretID)
    {
        if (empty($secretID)) {
            throw new \Exception('secretID不能为空');
        }
        $this->secretID = $secretID;
    }

    public function setSecretKey($secretKey)
    {
        if (empty($secretKey)) {
            throw new \Exception('secretKey不能为空');
        }
        $this->secretKey = $secretKey;
    }

    public function setCustomKey($customKey)
    {
        if (!in_array($customKey, array(self::GLOBAL_KEY, self::CUSTOM_KEY))) {
            throw new \Exception('自定义密钥传参错误');
        }
        $this->customKey = intval($customKey);
    }


    public function setAllowOption($allowOption)
    {
        if (!in_array($allowOption, [self::ALLOW_TO_PASS, self::ALLOW_TO_REVIEW])) {
            throw new \Exception('系统审核通过参数传值错误');
        }
        $this->allowOption = intval($allowOption);
    }

    public function setFailOption($failOption)
    {
        if (!in_array($failOption, [self::FAIL_TO_TRASH, self::FAIL_TO_RECYCLE_BIN, self::FAIL_TO_FORBID_SUBMISSION])
        ) {
            throw new \Exception('系统不审核通过参数传值错误');
        }
        $this->failOption = $failOption;
    }

    public function setWhitelist($whitelist)
    {
        $this->whitelist = $whitelist;
    }

    public function getSecretID()
    {
        $commonOptions = $this->getCommonOptions();
        if ($this->customKey === self::GLOBAL_KEY && isset($commonOptions['secret_id'])) {
            $this->secretID = $commonOptions['secret_id'] ?: '';
        }
        return $this->secretID;
    }

    public function getSecretKey()
    {
        $commonOptions = $this->getCommonOptions();
        if ($this->customKey === self::GLOBAL_KEY && isset($commonOptions['secret_key'])) {
            $this->secretKey = $commonOptions['secret_key'] ?: '';
        }
        return $this->secretKey;
    }

    public function getAllowOption()
    {
        return $this->allowOption;
    }

    public function getFailOption()
    {
        return $this->failOption;
    }

    public function getCustomKey()
    {
        return $this->customKey;
    }

    public function getWhitelist()
    {
        return $this->whitelist;
    }

}