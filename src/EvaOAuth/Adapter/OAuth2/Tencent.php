<?php
    
namespace EvaOAuth\Adapter\OAuth2;

use EvaOAuth\Adapter\OAuth2\AbstractAdapter;
use EvaOAuth\Service\Token\Access as AccessToken;
use ZendOAuth\OAuth;

class Tencent extends AbstractAdapter
{
    protected $accessTokenFormat = 'pair';
    protected $authorizeUrl = "https://graph.qq.com/oauth2.0/authorize";
    protected $accessTokenUrl = "https://graph.qq.com/oauth2.0/token";

    protected $defaultOptions = array(
        'requestScheme' => OAuth::REQUEST_SCHEME_POSTBODY,
        //'scope' => 'get_user_info,add_share,add_weibo',
        'scope' => 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr',
    );

    public function accessTokenToArray(AccessToken $accessToken)
    {
        $token = parent::accessTokenToArray($accessToken);
        if(!isset($token['remoteUserId']) || !$token['remoteUserId']){
            $token['remoteUserId'] = $this->getRemoteUserId();
            $token['remoteUserName'] = $this->getRemoteUserName();
            $token['remoteExtra'] = $this->getRawProfileString();
        }
        return $token;
    }

    public function getRemoteUserId()
    {
        $client = $this->getHttpClient();
        $client->setUri('https://graph.qq.com/oauth2.0/me');
        $response = $client->send();

        $data = $this->parseJsonpResponse($response);
        return isset($data['client_id']) ? $data['client_id'] : null;
    }

    public function getRemoteUserName()
    {
        $data = $this->getRawProfile();
        return isset($data['nickname']) ? $data['nickname'] : null;
    }

    public function getRawProfile()
    {
        if($this->rawProfile || false === $this->rawProfile) {
            return $this->rawProfile;
        }

        $client = $this->getHttpClient();
        $client->setUri('https://graph.qq.com/user/get_user_info');
        $response = $client->send();
        if($response->getStatusCode() >= 300) {
            return false;
        }
        return $this->rawProfile = $this->parseJsonResponse($response);
    }
}
