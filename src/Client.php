<?php

namespace Halink\SSO;

use Halink\SSO\Exceptions\ClientException;
use Halink\SSO\Response\Client as ClientResponse;

class Client
{
    // contructor
    private $client_url, $client_secret, $url;
    public function __construct(array $config)
    {

        $this->client_url = !empty($config['url']) ? $config['url'] : SSO_URL;
        if (empty($config['client_id'])) {
            throw new ClientException('Client ID is required');
        }
        $client_id = $config['client_id'];
        if (empty($config['client_secret'])) {
            throw new ClientException('Client Secret is required');
        }
        $this->client_secret = $config['client_secret'];
        if (empty($config['redirect_uri'])) {
            throw new ClientException('Redirect url is required');
        }
        $redirect_uri = $config['redirect_uri'];
        $query = [
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
        ];
        if (!empty($config['fields']) && is_array($config['fields'])) {
            $query['fields'] = implode(',', $config['fields']);
        }
        $this->url = $this->client_url . '?' . http_build_query($query);
    }
    public function getOauthUrl()
    {
        return $this->url;
    }
    protected function getAccessToken()
    {
        // var_dump($_GET);
        if (empty($_GET['halink-token'])) {
            throw new ClientException('Token is empty');
        }
        $token = $_GET['halink-token'];
        return $token;
    }
    public function me()
    {
        $status = (int) $_GET['status'];
        if ($status !== 1) {
            throw new ClientException('Khách hàng không đồng ý cho truy cập dữ liệu');
        }
        $token = $this->getAccessToken();
        // var_dump($token);
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->client_url . '/me');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5000);
        // https
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            'secret' => $this->client_secret,
        ]));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,

        ]);
        // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        if (($result = curl_exec($curl)) === false) {
            throw new ClientException(curl_error($curl));
        }
        $errno = curl_errno($curl);
        $httpReturnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpReturnCode != 200) {
            throw new ClientException('Token is invalid or expired');
        }
        $result = json_decode($result, true);
        if (empty($result['status'])) {
            throw new ClientException($result['message']);
        }
        return $result['user'];
        // var_dump($result);
    }
    // Exception
}
