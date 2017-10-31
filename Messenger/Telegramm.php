<?php
/**
 * Created by PhpStorm.
 * User: Savatneev Anton Alex
 * Date: 06.10.2017
 * Time: 13:52
 */

namespace messenger\telegram;


use Couchbase\Exception;

class Telegramm
{

    private $bot_token = "";
    private $api_url_tel = "";

    /**
     * @return string
     */
    public function getApiUrlTel()
    {
        return $this->api_url_tel;
    }

    /**
     * @return string
     */
    public function getBotToken()
    {
        return $this->bot_token;
    }

    /**
     * @param string $api_url_tel
     */
    private function setApiUrlTel()
    {
        $this->api_url_tel = "https://api.telegram.org/bot".$this->getBotToken()."/";
    }

    /**
     * @param string $bot_token
     */
    public function setBotToken($bot_token)
    {
        $this->bot_token = $bot_token;
        $this->setApiUrlTel();
    }


    /**
     * Делает запрос к серверу
     *
     * @param resource $handle
     *
     * @return boolean
     */
    protected function _exec_curl_request($handle)
    {
        $response = curl_exec($handle);
        if ($response === false)
        {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);
        if ($http_code >= 500)
        {
            // do not wat to DDOS server if something goes wrong
            sleep(10);
            return false;
        }
        else if ($http_code != 200)
        {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401)
            {
                throw new Exception('Invalid access token provided');
            }
            return false;
        }
        else
        {
            $response = json_decode($response, true);
            if (isset($response['description']))
            {
                error_log("Request was successfull: {$response['description']}\n");
            }
            $response = $response['result'];
        }

        return $response;
    }

    /**
     * Подготовка запроса
     *
     * @param string $method
     * @param array $parameters
     *
     * @return boolean
     */
    protected function _apiRequest($method, $parameters)
    {
        if (!is_string($method))
        {
            error_log("Method name must be a string\n");
            return false;
        }

        if (!$parameters)
        {
            $parameters = array();
        }
        else if (!is_array($parameters))
        {
            error_log("Parameters must be an array\n");
            return false;
        }

        foreach($parameters as $key => & $val)
        {
            // encoding to JSON array parameters, for example reply_markup
            if (!is_numeric($val) && !is_string($val))
            {
                $val = json_encode($val);
            }
        }

        $url = $this->getApiUrlTel() . $method . '?' . http_build_query($parameters);

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        return $this->_exec_curl_request($handle);
    }

    /**
     * Отправка сообщения
     *
     * @param int $id_chat
     * @param string $sMessage
     *
     * @return void
     */
    public function sendMessage($id_chat, $sMessage)
    {
        //https://api.telegram.org/botID:HASH/sendMessage?chat_id=111&text=Nice+to+meet+you

        $this->_apiRequest('sendMessage', array(
            'chat_id' => $id_chat,
            'text' => $sMessage,
        ));
    }
}