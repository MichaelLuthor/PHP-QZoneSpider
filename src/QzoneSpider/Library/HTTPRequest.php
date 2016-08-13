<?php
namespace MichaelLuthor\QzoneSpider\Library;
class HTTPRequest {
    private $cookieFilePath = null;
    private $cookieContent = false;
    private $pageContent = '';
    private $requestInfo = array();
    public $host = null;
    public $referer = null;
    public $userAgent='Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0';
    
    /**
     * @param unknown $path
     */
    public function setCookiePath( $path ) {
        $this->cookieFilePath = $path;
    }
    
    /**
     * @return void
     */
    public function cleanCookie() {
        @unlink($this->cookieFilePath);
    }
    
    /**
     * @param unknown $name
     */
    public function getCookieValue( $name ) {
        if ( false === $this->cookieContent ) {
            $cookie = $this->cookieFilePath;
            $content = file($cookie, FILE_IGNORE_NEW_LINES);
            $values = array();
            foreach ( $content as $index => $line ) {
                $line = explode("\t", $line);
                if ( !isset($line[6]) ) {
                    continue;
                }
                $values[$line[5]] = $line[6];
            }
            $this->cookieContent = $values;
        }
        return @$this->cookieContent[$name];
    }
    
    /**
     * @return string
     */
    public function getPageContent () {
        return $this->pageContent;
    }
    
    /**
     * @return array
     */
    public function getJson() {
        $content = substr($this->pageContent, 10, strlen($this->pageContent)-12);
        return json_decode($content, true);
    }
    
    /**
     * @param unknown $url
     * @param array $params
     * @return boolean|mixed
     */
    public function get( $url, $params=array() ) {
        if ( !empty($params) ) {
            $connector = (false===strpos($url, '?')) ? '?' : '&';
            $url = $url.$connector.http_build_query($params);
        }
        return $this->sendRequest($url);
    }
    
    /**
     * @param unknown $url
     * @param array $options
     */
    private function sendRequest( $url, $options=array() ) {
        $this->requestInfo = array();
        
        $defaultOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );
        if ( null !== $this->cookieFilePath ) {
            $defaultOptions[CURLOPT_COOKIEJAR] = $this->cookieFilePath;
            $defaultOptions[CURLOPT_COOKIEFILE] = $this->cookieFilePath;
        }
        if ( null !== $this->referer ) {
            $options[CURLOPT_REFERER] = $this->referer;
        }
        foreach ( $defaultOptions as $key => $value ) {
            if ( isset($options[$key]) ) {
                continue;
            }
            $options[$key] = $value;
        }
        $this->cookieContent = false;
        $triedTimtCount = 3;
        while ( 0 < $triedTimtCount ) {
            $triedTimtCount --;
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $pageContent = curl_exec($ch);
            $this->requestInfo['error'] = curl_error($ch);
            $this->requestInfo['errno'] = curl_errno($ch);
            $this->requestInfo['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ( false !== $pageContent ) {
                $this->pageContent = $pageContent;
                return $pageContent;
            }
            sleep(1);
        }
        return false;
    }
}