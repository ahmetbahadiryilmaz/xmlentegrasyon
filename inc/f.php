<?

class f{
    static function clean($rra){
        $rra=str_replace("&nbsp;","",$rra);
        return $rra;
    }
 
    static function get_http_response_code($domain1) {
        $headers = get_headers($domain1);
        return substr($headers[0], 9, 3);
      }
}
?>