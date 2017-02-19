<?php 
    include $_SERVER['DOCUMENT_ROOT'].'/Scrape/Model/TableData.php';
    
    $error = "";
    $status = "";
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $URL = isset($_POST['URL']) ? $_POST['URL'] : null;
        
        
        if($URL){
            $table = new TableData();    

            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $result = curl_exec($ch);
            curl_close($ch);
            
            //DOM Parsing----------------------------------------------------------------------------------
            $dom = new DOMDocument();   //DOM Document
            $dom->loadHTML($result);
            $x = new DOMXPath($dom);    //Using XPATH for querying data in table
            
            //Query table  
            $rows = $x->query("//table//tr"); 
            $error = $rows->length > 1 ? "" : "Looks like there is not data for this link!<br>";
            
            if(!$error){    // if data for that link exists!
                $DATE = $x->query("//span[@id='LblTime']");
                $table->DATE = $DATE->item(0)->nodeValue;
                $for_ID = $table->save_for_date();  //Getting last insert ID for date table, will return existing ID if current date already exists!
                //echo $table->DATE."<br>";   
            }
            
            //Database Operation ---------------------------------------------------------------------------
            foreach ($rows as $index => $row) {
                if($index != 0){

                    $table->TIMESLOT = $row->getElementsByTagName('td')->item(0)->nodeValue;
                    $table->DELHI = $row->getElementsByTagName('td')->item(1)->nodeValue;
                    $table->BRPL = $row->getElementsByTagName('td')->item(2)->nodeValue;
                    $table->BYPL = $row->getElementsByTagName('td')->item(3)->nodeValue;
                    $table->NDPL = $row->getElementsByTagName('td')->item(4)->nodeValue;
                    $table->NDMC = $row->getElementsByTagName('td')->item(5)->nodeValue;
                    $table->MES = $row->getElementsByTagName('td')->item(6)->nodeValue;
                    
                    $table->save_values($for_ID);
                }
    
            }
             
        }else{
            $error = "Please enter URL!<br>";
        }        
          
    }
    

?>

<!DOCTYPE>
<html lang="en">
    <head>
        <title>Scrape SCADA</title>
        <style>
            input[type='url']{
                width:600px;
            }
        </style>
    </head>
    <body>
        <?php 
            if($_SERVER['REQUEST_METHOD'] === 'POST'){
                echo $error; 
                if(!$error){
                    if($table->update)
                        echo "Successfully Updated!<br>"; 
                    else
                        echo "Successfully Inserted!<br>";
                }
            }
                         
         ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <ul>
                <li><input type="url" name="URL" placeholder="Enter URL here.."/></li>
                <li><input type="submit" name="Start Scraping" /></li>
            </ul>
        </form
        <p><strong>Note: </strong>Data for 14th, 15th and 16th January is stored in SQL dump file.<br>
            Data is only stored in the database, no facility to view is developed. Data can be seen through SQL dump.    
        </p>
    </body>
</html>