<?php
namespace MichaelLuthor\QzoneSpider;
use MichaelLuthor\QzoneSpider\Library\HTTPRequest;
use MichaelLuthor\QzoneSpider\Library\Util;
class Spider {
    private $account = null;
    private $password = null;
    private $tasks = array();
    private $httpRequest = null;
    /**
     * @var \MichaelLuthor\QzoneSpider\Library\DataStorageHandler
     */
    private $storage = null;
    public function __construct( $account, $password, $tasks, $storageConfig ) {
        $this->account = $account;
        $this->password = $password;
        $this->tasks = $tasks;
        
        $cookie = sprintf('%s/Data/Cookie/%s.cookie', dirname(__FILE__), $account);
        $this->httpRequest = new HTTPRequest();
        $this->httpRequest->setCookiePath($cookie);
        
        $storage = ucfirst($storageConfig['name']);
        $storage = "\\MichaelLuthor\\QzoneSpider\\StorageHandler\\{$storage}";
        $this->storage = new $storage($storageConfig);
    }
    
    /**
     * @return void
     */
    public function run() {
        if ( $this->isLoginRequired() ) {
            $this->login();
        }
        foreach ( $this->tasks as $task ) {
            $this->getPersionalInformation($task);
            $this->getInterestInformation($task);
        }
    }
    
    /**
     * @var unknown
     */
    private $token = null;
    
    /**
     * @param unknown $account
     */
    private function getInterestInformation( $account ) {
        $params = array(
            'flag'=>'1',
            'fupdate'=>'1',
            'g_tk'=>$this->token,
            'rd'=>'0.'.Util::randNumber(16),
            'uin'=>$account,
            'vuin'=>$this->account,
        );
        $this->httpRequest->get('http://page.qq.com/cgi-bin/profile/interest_get');
        $result = $this->httpRequest->getJson();
        $catMap = explode(',', '喜欢,明星,音乐,影视,运动,游戏,数码,美食,旅游,书籍,其它');
        foreach ( $result['data'] as $catKey => $catGroup ) {
            foreach ( $catGroup['item'] as $item ) {
                $interest = array(
                    'account'  => $account,
                    'category' => $catMap[$catKey],
                    'topic'    => $item['topic'],
                    'flag'     => $item['flag'],
                    'page'     => $item['page'],
                    'releated_account' => @$item['uin'],
                );
                $this->storage->save('interest', $interest);
            }
        }
    }
    
    /**
     * @param unknown $account
     */
    private function getPersionalInformation( $account ) {
        $params = array(
            'fupdate' => '1',
            'g_tk' => $this->token,
            'rd' => '0.'.Util::randNumber(16),
            'uin' => $account,
            'vuin' => $this->account,
        );
        $this->httpRequest->get('https://h5.qzone.qq.com/proxy/domain/base.qzone.qq.com/cgi-bin/user/cgi_userinfo_get_all', $params);
        $profile = $this->httpRequest->getJson();
        $profile['data']['qzworkexp'] = json_encode($profile['data']['qzworkexp']);
        $profile['data']['qzeduexp'] = json_encode($profile['data']['qzeduexp']);
        $profile['data']['emoji'] = json_encode($profile['data']['emoji']);
        $this->storage->save('profiles', $profile['data']);
    }
    
    /**
     * @return boolean
     */
    private function isLoginRequired() {
        $this->httpRequest->cleanCookie();
        return true;
    }
    
    /**
     * 登陆到QQ空间。
     * @throws \Exception
     */
    private function login() {
        $appid = '636014201';
        $urlSuccess = 'http://www.qq.com/qq2012/loginSuccess.htm';
        
        # get login_sig
        $params = array (
          'proxy_url' => 'http://qzs.qq.com/qzone/v6/portal/proxy.html',
          'daid' => '5',
          'hide_title_bar' => '1',
          'low_login' => '0',
          'qlogin_auto_login' => '1',
          'no_verifyimg' => '1',
          'link_target' => 'blank',
          'appid' => $appid,
          'style' => '22',
          'target' => 'self',
          's_url' => 'http://qzs.qq.com/qzone/v5/loginsucc.html?para=izone',
          'pt_qr_app' => '手机QQ空间',
          'pt_qr_link' => 'http://z.qzone.com/download.html',
          'self_regurl' => 'http://qzs.qq.com/qzone/v6/reg/index.html',
          'pt_qr_help_link' => 'http://z.qzone.com/download.html',
        );
        $this->httpRequest->get('http://xui.ptlogin2.qq.com/cgi-bin/xlogin', $params);
        $loginSig = $this->httpRequest->getCookieValue('pt_login_sig');
        
        # check login
        $params = array (
            "uin" => $this->account,
            "appid" => $appid,
            "pt_tea" => 1,
            "pt_vcode" => 1,
            "js_ver" => 10151,
            "js_type" => 1,
            "login_sig" => $loginSig,
            "u1" => $urlSuccess,
        );
        $content = $this->httpRequest->get('http://check.ptlogin2.qq.com/check', $params);
        preg_match_all('#\'(.*?)\'#', $content, $matches);
        $checkCode = $matches[1][0];
        $verifyCode = $matches[1][1];
        if ( '0' !== $checkCode ) {
            throw new \Exception('Verify code is required.');
        }
        $salt = $matches[1][2];
        $ptVerifysessionV1 = $matches[1][3];
        
        # encrypt password
        $v8 = new \V8Js();
        $jsContent = file_get_contents(dirname(__FILE__).'/Library/qq.login.encrypt.js');
        $jsContent .= sprintf('window.$pt.Encryption.getEncryption("%s", "%s", "%s", undefined)', $this->password, $salt, $verifyCode);
        $encryptedPassword = $v8->executeString($jsContent, 'basic.js');
        
        # login
        if ( empty($ptVerifysessionV1) ) {
            $ptVerifysessionV1 = $this->httpRequest->getCookieValue('ptvfsession');
        }
        $params = array (
            'u' => $this->account,
            'verifycode' => $verifyCode,
            'pt_vcode_v1' => 0,
            'pt_verifysession_v1' => $ptVerifysessionV1,
            'p' => $encryptedPassword,
            'pt_randsalt' => 0,
            'u1' => $urlSuccess,
            'ptredirect' => 0,
            'h' => 1,
            't' => 1,
            'g' => 1,
            'from_ui' => 1,
            'ptlang' => 2052,
            'action' => '2-0-1456213685600',
            'js_ver' => 10143,
            'js_type' => 1,
            'aid' => $appid,
            'daid' => 5,
            'login_sig' => $loginSig,
        );
        $content = $this->httpRequest->get('http://ptlogin2.qq.com/login', $params);
        preg_match_all("#'(.*?)'#", $content, $matches);
        if ( $matches[1][0] !== '0') {
            throw new \Exception('Login failed.');
        }
        $urlInfo = parse_url($matches[1][2]);
        parse_str($urlInfo['query'], $params);
        
        $params = array(
            'aid'=>'549000912',
            'daid'=>'5',
            'f_url'=>'',
            'j_later'=>'0',
            'low_login_hour'=>'0',
            'nodirect'=>'0',
            'pt_3rd_aid'=>'0',
            'pt_aaid'=>'0',
            'pt_aid'=>'0',
            'pt_light'=>'0',
            'pt_login_type'=>'1',
            'ptlang'=>'2052',
            'ptredirect'=>'100',
            'ptsigx'=>$params['ptsigx'],
            'pttype'=>'1',
            'regmaster'=>'0',
            's_url'=>'http://qzs.qq.com/qzone/v5/loginsucc.html?para=izone',
            'service'=>'login',
            'uin'=>$this->account,
        );
        $this->httpRequest->get('http://ptlogin4.qzone.qq.com/check_sig', $params);
        
        # get token 
        $token = $this->httpRequest->getCookieValue('p_skey');
        $hash = 5381;
        $token = str_split($token);
        foreach ( $token as $index => $tokenChar ) {
            $tokenChar = mb_convert_encoding($tokenChar, 'UTF-32BE', 'UTF-8');
            $tokenChar = hexdec(bin2hex($tokenChar));
            $hash += ($hash<<5) + $tokenChar;
        }
        $token = $hash & 2147483647;
        $this->token = $token;
    }
}

spl_autoload_register(function( $class ) {
    $class = explode('\\', $class);
    if ( 3 > count($class) || 'MichaelLuthor' !== $class[0] ) {
        return;
    }
    
    array_shift($class);
    array_shift($class);
    
    $path = implode(DIRECTORY_SEPARATOR, array_merge(array(dirname(__FILE__)), $class)).'.php';
    require $path;
});