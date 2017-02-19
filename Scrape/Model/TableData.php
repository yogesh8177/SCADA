<?php 
include $_SERVER['DOCUMENT_ROOT'].'/Scrape/Shared/db_config.php';

class TableData{
    
   public $status; 
   public $totalColumns = 7;
   
   public $update = false;
   public $DATE;
   public $TIMESLOT;
   public $DELHI;
   public $BRPL;
   public $BYPL;
   public $NDPL;
   public $NDMC;
   public $MES;
   
   private $conn;
   
   function __construct(){
       try{
           $this->conn = new PDO("mysql:host=".DBHOST.";dbname=".DBNAME.";charset=utf8",DBUSER,DBPASSWORD);
           $this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
           
       }catch(PDOException $e){
           $this->status = $e->getMessage();
       }
   }//constructor ends
   
   public function save_for_date(){
       try{
           $exists = $this->check_if_already_exists_date();
           //echo $exists;
           if(!$exists){
               $this->update = false;
               $query = $this->conn->prepare("INSERT INTO load_date (for_date) VALUES(STR_TO_DATE(?, '%d/%m/%YYYY'))"); //using placeholders to prevent SQL Injection attack
               $query->execute(array($this->DATE)); 
               return $this->conn->lastInsertId();
           }else{
               $this->update = true;
               return $this->check_if_already_exists_date(); //returns previous ID of already existing date. 
           }
           
       }catch(PDOException $e){
           $status = $e->getMessage();
           return -1;
       }
   }
   
   public function save_values($for_date_id){
       //echo "Starting to insert values...<br>";
       try{
           $query = null;
           
           if(!$this->update){
               $query = $this->conn->prepare("INSERT INTO load_values (for_date_id,time_slot, delhi, brpl, bypl, ndpl, ndmc, mes) VALUES(?,?,?,?,?,?,?,?)"); //using placeholders to prevent SQL Injection attack        
               $query->execute(array($for_date_id, $this->TIMESLOT, $this->DELHI, $this->BRPL, $this->BYPL, $this->NDPL, $this->NDMC, $this->MES)); 
                //echo " -> Inserted";
           }else{
               $query = $this->conn->prepare("UPDATE load_values SET time_slot = ?, delhi = ?, brpl = ?, bypl = ?, ndpl = ?, ndmc = ?, mes = ? WHERE for_date_id = ? AND time_slot = ?");   
               $query->execute(array($this->TIMESLOT, $this->DELHI, $this->BRPL, $this->BYPL, $this->NDPL, $this->NDMC, $this->MES, $for_date_id, $this->TIMESLOT)); 
               //echo " -> Updated";
           }
           //echo "Done...<br>";
       }catch(PDOException $e){
           $status = $e->getMessage();
       }
       
   }
   
   //Check if date already data exists for current date, if exists then return ID of existing date!
   function check_if_already_exists_date(){
       try{
           $check_query = $this->conn->prepare("SELECT ID from load_date WHERE for_date = STR_TO_DATE(?, '%d/%m/%YYYY')");
           $check_query->execute(array($this->DATE));
           $row = $check_query != null ? $check_query->fetch() : null;
           
           return $row != null ? $row["ID"] : null;
       }catch(PDOException $e){
           $status = $e->getMessage();
       }
   }
}

?>
