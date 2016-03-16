<?php

$data = $_POST['myPostData'];           
$query = $_POST['query'];           
$videos = json_decode($data);  

    

$conn  = mysql_connect('localhost', 'root', '');  
if (!$conn) {
    die('Ошибка соединения: ' . mysql_error());  
}
mysql_select_db("test1") or die("Ошибка соединения");
  
$sql = "SELECT id, name FROM query WHERE name = '".$query."'";    
$q = mysql_query ($sql);
if (mysql_num_rows($q) == 0)     
{     
	$sql = "INSERT INTO query (name) VALUES ('".$query."')";
	mysql_query($sql);    
	$queryId = mysql_insert_id();  
}  
else
{  
	while($i = mysql_fetch_array($q)) {
		$queryId = $i['id'];
		break;
	}
}   


$youtubeIdsArray = [];
$oldItemsYoutubeIdsArray = [];
foreach ($videos->items as $item){ 
	$youtubeIdsArray [] = $item->id;
}

$sql = "DELETE FROM result WHERE ((youtube_id NOT IN ( '" . implode($youtubeIdsArray, "', '") . "' )) AND ( query_id = ".$queryId."))";
$q = mysql_query($sql);

$sql = "SELECT id, youtube_id FROM result WHERE (query_id = ".$queryId.")";
$q = mysql_query($sql);    
while($i = mysql_fetch_array($q)) {
		if (in_array($i['youtube_id'], $youtubeIdsArray))
			$oldItemsYoutubeIdsArray [] = $i['youtube_id'];
	}  


    
foreach ($videos->items as $newItem){  
	if (!in_array($newItem->id, $oldItemsYoutubeIdsArray))
		{   
			$sql = "INSERT INTO result (query_id, title, description, youtube_id, likeCount, dislikeCount)
				VALUES (".$queryId.",
				'".mysql_real_escape_string($newItem->snippet->title)."', 
				'".mysql_real_escape_string($newItem->snippet->description)."', 
				'".mysql_real_escape_string($newItem->id)."',  
			    ".$newItem->statistics->likeCount.",
			    ".$newItem->statistics->dislikeCount."
				)";  
			$q = mysql_query($sql);      
		}			
}   

$items = [];
$sql = "SELECT * FROM result WHERE (query_id = ".$queryId.")";
$q = mysql_query($sql);      
while($row = mysql_fetch_array($q)) {
        $items[] = array(
			  'title' => $row['title'],  
			  'description' => $row['description'],    
			  'likeCount' => $row['likeCount'],
			  'dislikeCount' => $row['dislikeCount']
		   );    
}       

$response = array('name' => $query, 
			'count' => mysql_num_rows($q),   
			'items'=>$items 
 );
echo json_encode($response);	     

mysql_close($conn);   
?>