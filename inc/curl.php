<?
class curl{
            

            public  $cookieFile = __DIR__."/../cookies.txt";
            private $header;
            private $httpCode;
            private $cacheFolder = __DIR__."/../cache/";
            public $cache=true;
            private $httpHeader=[ 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'];

            function __construct()
            {
             $this->cookieFile();   
            }
            public function getHeader(){
                return $this->header;
            }
            public function getHttpcode(){
                return $this->httpCode;
            }
            public static function getHttpCodeOnly($url){
             
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
                curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_TIMEOUT,10);
                $output = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                return $httpcode;
            }
            public function removeFilename($url)
            {
                $file_info = pathinfo($url);
                return isset($file_info['extension'])
                    ? str_replace($file_info['filename'] . "." . $file_info['extension'], "", $url)
                    : $url;
            }

            public function createPath($path) {
                if (is_dir($path)) 
                    return true;
                $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
                $return = $this->createPath($prev_path);
                return ($return && is_writable($prev_path)) ? mkdir($path) : false;
            }
            public function cookiefile(){
                 if(!file_exists($this->cookieFile)) {
                     $this->createPath ($this->removeFilename($this->cookieFile));
                     $fh = fopen($this->cookieFile, "w");
                     fwrite($fh, "");
                     fclose($fh);
                   
                 }
             }

             public function setCacheFolder($folder){
                return $this->cacheFolder=$folder;
             }

             private function cacheFileName($url){
                return $this->cacheFolder.md5($url).".cache";
             }
             private function getCache($url)
             {
                $cache_file = $this->cacheFileName($url);
                if(file_exists($cache_file)) {
                  if(time() - filemtime($cache_file) > 86400*100) {
                     // too old , re-fetch
                     return false;
                  } else {
                    return file_get_contents($this->cacheFileName($url));
                  }
                } else {
                  // no cache, create one
                  return false;
                }
             }
           
             private function doCache($url,$content)
             {
                file_put_contents($this->cacheFileName($url),$content);

             }
           
             public function addReqHeader($reqHead){
                $this->httpHeader[]=$reqHead;
             }

             public function emptyReqHeader(){
                $this->httpHeader=[];
             }

             public  function getPage($url){
                
                if($this->cache){
                    $cache = $this->getCache($url);
                    if($cache){
                        $cache=json_decode($cache);
                        $this->header=$cache->header;
                        $this->httpCode=$cache->httpCode;
                        return   $cache->body;
                    }
                }
                 $ch = curl_init();
             
                 curl_setopt( $ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           
                 curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile); // Cookie aware
                 curl_setopt($ch, CURLOPT_COOKIEJAR,  $this->cookieFile); // Cookie aware
                 curl_setopt($ch, CURLOPT_HEADER, true);
                 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                 if(count($this->httpHeader)>0){
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->httpHeader);
                 }
          
                $r = curl_exec($ch);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $header = substr($r, 0, $header_size);
                $body = substr($r, $header_size);                
                $this->header=$header;
                $this->httpCode=$httpCode;
                curl_close($ch);
                if($this->cache){
                    $cacheArr=[
                        "body"=>$body,
                        "header"=>$header,
                        "httpCode"=>$httpCode
                    ];
                    $this->doCache($url,json_encode($cacheArr));
                
                }

                 return $body;
             }
             
             
             
             
             public  function postPage($url,$data){   
                 $ch = curl_init();    
                 curl_setopt( $ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                 curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                 curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile); // Cookie aware
                 curl_setopt($ch, CURLOPT_COOKIEJAR,  $this->cookieFile); // Cookie aware
                 curl_setopt($ch, CURLOPT_HEADER, 1);
                 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
             /*
                 curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    $cookie
                 ]);
                 */
                 $r = curl_exec($ch);
                 // Then, after your curl_exec call:
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $header = substr($r, 0, $header_size);
                $body = substr($r, $header_size);
                $this->header=$header;
                $this->httpCode=$httpCode;
                curl_close($ch);
                return $body;
             }
             function convertISOUTF($r){
                return iconv("ISO-8859-9", "UTF-8",$r);
            }
         
         
         }
         
         
       
?>