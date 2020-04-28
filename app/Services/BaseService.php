<?php

namespace App\Services;

/**
 * 业务服务: 基类
 *
 * @package App\Services
 */
abstract class BaseService
{
    private $key = "kqX4tnCxHOqTM8O3";

    /**
     * 返回新实例
     *
     * @return static
     */
    public static function newInstance()
    {
        return new static();
    }

    /**
     * 过滤参数
     * @param string $str 接受的参数
     * @return string
     */
    public function filterWords(string $str)
    {
        $farr = array(
            "/<(\\/?)(script|i?frame|style|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU",
            "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
            "/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dump/is"
        );
        $str = preg_replace($farr, '', $str);
        return $str;
    }

    /**
     * 过滤接受的参数或者数组,如$_GET,$_POST
     * @param array|string $arr 接受的参数或者数组
     * @return array|string
     */
    public function filterArr(array $arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $this->filterArr($v);
                } else {
                    $arr[$k] = $this->filterWords($v);
                }

            }
        } else {
            $arr = $this->filterWords($arr);
        }
        return $arr;
    }

    /**
     * 加密方法
     *
     * @param string $plainText
     * @return string
     */
    public function encrypt(string $plainText)
    {
        # 为 CBC 模式创建随机的初始向量
        $ivlen = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        # 创建和 AES 兼容的密文（Rijndael 分组大小 = 128）
        # 仅适用于编码后的输入不是以 00h 结尾的
        # （因为默认是使用 0 来补齐数据）
        $cipherText = openssl_encrypt($plainText, 'AES-128-CBC', $this->key, 1, $iv);
        # 将初始向量附加在密文之后，以供解密时使用
        $cipherTextBase64 = $iv . $cipherText;
        # 对密文进行 base64 编码
        $cipherTextBase64 = base64_encode($cipherTextBase64);
        return $cipherTextBase64;
    }

    /**
     * 解密方法
     *
     * @param string $encryptStr
     * @return mixed
     */
    public function decrypt(string $encryptStr)
    {
        # --- 解密 ---
        $ivlen = openssl_cipher_iv_length('AES-128-CBC');
        $cipherText = base64_decode($encryptStr);
        # 初始向量大小，可以通过 mcrypt_get_iv_size() 来获得
        $iv = substr($cipherText, 0, $ivlen);
        # 获取除初始向量外的密文
        $cipherText = substr($cipherText, $ivlen);
        # 可能需要从明文末尾移除 0
        $plaintext_dec = openssl_decrypt($cipherText, 'AES-128-CBC', $this->key, 1, $iv);
        return json_decode($plaintext_dec, true);
    }

    /**
     * 生成uuid
     *
     * @return string
     */
    public function guid()
    {
        if (function_exists('com_create_guid')) {

            return com_create_guid();

        } else {

            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.

            $charid = strtoupper(md5(uniqid(rand(), true)));

            $hyphen = chr(45);// "-"

            $uuid = substr($charid, 0, 8) . $hyphen

                . substr($charid, 8, 4) . $hyphen

                . substr($charid, 12, 4) . $hyphen

                . substr($charid, 16, 4) . $hyphen

                . substr($charid, 20, 12);

            return $uuid;

        }
    }

    /**
     * 生成pin_code
     *
     * @return string
     */
    public function pinCode()
    {
        static $seed = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
        $str = '';
        for ($i = 0; $i < 8; $i++) {
            $rand = rand(0, count($seed) - 1);
            $temp = $seed[$rand];
            $str .= $temp;
            unset($seed[$rand]);
            $seed = array_values($seed);
        }
        return $str;
    }
}