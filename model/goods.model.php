<?
 
 
 use Symfony\Component\Serializer\Encoder\JsonEncoder;
 use Symfony\Component\Serializer\Encoder\XmlEncoder;
 use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
 use Symfony\Component\Serializer\Serializer;
 



class goods{
    public static $goods=[];
    public function __construct() {
         
    }
    public static function addGood(Good $Good){
      self::$goods[]=$Good;      
    }

    public static  function toArray()
    {
      $arr= ["Good"=>[]];
      
      foreach (self::$goods as $good ) {
        /** @var good $good */



        $variantsarr= [];
        foreach ($good->Variants as $variant) {
          if(!$variant->VaryantReference)unset($variant->VaryantReference);
        
          $variantsarr["Variant"][]= $variant ;
        }
        $good->Variants=$variantsarr;
   
        if(count($good->Images["Src"])<1){
          if(isset($good->Variants["Variant"])){
            $good->Images=$good->Variants["Variant"][0]->Images;
          }
        } 
    
        if($good->REFERENCE=="")unset($good->REFERENCE);
        if($good->CategoryTree=="")unset($good->CategoryTree);
        $arr["Good"][]=$good;
      }
      return $arr;
    }

    public static function printXml(){

        
        //$c = new xmlSerializer(["goods"=>self::$goods]);
        //echo $c->xml->saveXML();

                
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize(   goods::toArray()  , 'xml',[
            'xml_format_output' => true,
            'xml_encoding' => 'utf-8',
            'xml_root_node_name' => 'Goods'
          ]);

         
        header("Content-type: text/xml");
        echo $jsonContent; // or return it in a Response



   }
}


?>