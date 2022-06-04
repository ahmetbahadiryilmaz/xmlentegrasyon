<?php // veri tabanı bağlantısı
	//Select örneği  toplu
/*
 	$f = db::$conn->prepare("select * from dizikelimeler");
	$f->execute();
	$fx= $f->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($fx);


	Tekli select
	$f = db::$conn->prepare("select * from dizikelimeler");
	$f->execute();
	$fx= $f->fetch(PDO::FETCH_ASSOC);
	echo $fx["sütünismi"];
 */
      class db{
		/** @var PDO $conn */	
		static $conn;

		static function  insert($tablo,$data){
		 
			 
			$engine = self::$conn;// 2 tane cıkmasını kapatıyımı nasıl yani he kapatsana P varsa yazım kolaylığı sağlayan şeyler kuralım onları da :D
			$rowsSQL = "";
			$toBind = array();
			if(isset($data["id"]) && $data["id"]=="" )unset($data["id"]) ;
			$columnNames = array_keys($data);
		
			foreach($data as $columnName => $columnValue){
				
				$param = ":" . $columnName  ;
				$params[] = $param;
				$toBind[$param] = $columnValue;
			}
			$rowsSQL  = "(" . implode(", ", $params) . ")";
			$sql = "INSERT INTO `".$tablo."` (" . implode(", ", $columnNames) . ") VALUES " . $rowsSQL ;
		
			$pdoStatement = $engine->prepare($sql);
			foreach($toBind as $param => $val){
				$pdoStatement->bindValue($param, $val);
			}
			if($pdoStatement->execute()){
				//return $engine->lastInsertId();
				return true;
			}else{
				return false;
			};
			 
		  
		  }


		  static function fetch( $sql ,$exarr=array()){
			  
				$dbh = self::$conn; 
				$sth = $dbh->prepare($sql);
				if (!$sth) {
					echo "\nPDO::errorInfo():\n";
					print_r($dbh->errorInfo());
				}
				$sth->execute( $exarr);
				
				$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			
				return $result;
		  }

		  

		  static function rowCount( $sql ,$exarr=array()){
			  
			$dbh = self::$conn; 
			$sth = $dbh->prepare($sql);
			if (!$sth) {
				echo "\nPDO::errorInfo():\n";
				print_r($dbh->errorInfo());
			}
			$sth->execute( $exarr);
			
			$result = $sth->rowCount();

			return $result;
	  }



		  
		  static function update($tablo,$data,$where=array()){
			try {
				$engine =self::$conn;
				$data = array_filter($data, function ($value) {
					return null !== $value;
				});
				$query = 'UPDATE '.$tablo.' SET ';
				$values = array();
				$queryParams=array();
				$queryParamsFilter=array();
			
				foreach ($data as $name => $value) {
					if($name!="id"){
					$queryParams[] = $name.' = :'.$name;
					}
					$values[':'.$name] = $value;
					
				}
			
				if(count($where)>0){
					foreach ($where as $name => $value) {
			
						$queryParamsFilter[] = $name.' =:'.$name;
						$values[':'.$name] = $value;
			
					}
				}/*elseif (isset($data["id"])) {
					
				}*/
				$query = $query.implode(" , " , $queryParams).((count($where)>0)?" where ".implode(" and " ,$queryParamsFilter):" where id=:id"); //
			
				$sth = $engine->prepare($query);
				foreach($values as $param => $val){
					$sth->bindValue($param, $val);
				}
				//print_r($data);
			
				//print_r($query);
				//return $sth->execute();
				if($sth->execute()){
					return true;
				 }else{
					return false;
				 };
			} catch (Exception $er) {
				//exit(json_encode($er)  );
				return false;
			}
		  }
	}

	$ip = "localhost"; //host
	$user = "ekullanici_db";  // host id
	$password = "kqrELE683**";  // password local olduğu için varsayılan şifre boş
	$dbase = "test_vt"; // db adı
 
	try{ 
	    db::$conn = new PDO("mysql:host=$ip;dbname=$dbase;charset=utf8mb4",$user,$password);
		db::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		db::$conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
	 
		// türkçe karakter için utf8
		db::$conn->exec("set names utf8mb4");
	 
		//eğer hata olursa pdo nun exception komutu ile ekrana yazdırıyoruz
	}catch(PDOException $e){
		die ("Veritabanı bağlantı hatası:".$e."");
	} 

?>