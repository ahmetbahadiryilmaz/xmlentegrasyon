<?


class xmlSerializer{
    public $xml;
    public function __construct($obj) {
        $this->xml= new DOMDocument;  
        if(is_object($obj)){
            $this->addObjNodes($obj,$this->xml);
        }else{
            $this->addArrNodes($obj,$this->xml);
        }
        
    }
    public function addObjNodes($obj,DOMNode $parent){
    
        $nodename= get_class( $obj);
        $node = $this->xml->createElement($nodename);
        
        $vars = get_object_vars( $obj);
      
        foreach ($vars as $key => $value) {
            $nodealt = $this->xml->createElement($key);
            $node->appendChild($nodealt);
            if(is_object($value)){
                $this->addObjNodes($value,$nodealt);
            }elseif(is_array($value)){
                $this->addArrNodes($value,$nodealt);
              
            }else{
                $nodealt->nodeValue=$value;
            }
        }
        $parent->appendChild( $node);
      
    }

    public function addArrNodes($arr,DOMNode $parent)
    {
       
        foreach ($arr as $key2 => $value2) {
            if(!is_numeric($key2)  ){
                $nodealt2 = $this->xml->createElement($key2);
           
                $parent->appendChild($nodealt2);
                $parent = $nodealt2;
               
            }
            if(is_object($value2)){
            
                $this->addObjNodes($value2,$parent);
              
            }elseif(is_array($value2)){
           
                $this->addArrNodes($value2,$parent);
            }else{
                $nodealt2 = $this->xml->createElement($parent->nodeName);
                $nodealt2->nodeValue=$value2;
                $parent->parentNode->appendChild($nodealt2);
            }
        }
    }
    public function addNode($key,$value,$parent){

    }
}

?>