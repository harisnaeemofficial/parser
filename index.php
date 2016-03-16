<!DOCTYPE HTML>
<html>
 <head>
  <meta charset="utf-8">
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
 </head>
 <body>
  <style type="text/css">
   table, th, td {
    border: 1px solid grey;  
	}  
  </style>
	<?php 

	$str = 'adriano celentano, sting, pavarotti caruso, celine dion, michael jackson';
	$queryArr = explode(',',$str);
	 ?>
	<div style="margin-left: 15px; margin-right: 15px;">
		<div style="width: 20%; float: left;">
		   <span style="font-size:24px;">Запросы</span>
		   <ul>
		   <?php foreach ($queryArr as $query): 
					echo '<li style="padding-bottom:24px;"><a class="query" href="#">'.$query.'</a></li>';
				 endforeach; 
		   ?>  
		   </ul>
		</div>
		<div style="width: 20%; float: left;">
		   <span style="font-size:24px;">Общая информация</span>
		   <div id="common_info"></div>  
		</div>
		<div style="width: 60%; float: left;">
		   <span style="font-size:24px;">Результаты</span>
		   <table id="results" style="display:none">
				<thead>
				  <tr>
					 <th>Название</th>
					 <th>Описание</th>
					 <th>Нравится</th>
					 <th>Не нравится</th>
				  </tr>
				 </thead>
		   </table>
		</div>
	</div> 
 </body>
</html>
<script>
	$( document ).ready(function() {
	    myKey = 'YOUR_API_KEY';  
		commonInfoDiv = $('#common_info');
		resultsTable = $('#results');
		idsArr = []; 
		$('.query').click(function(){
		    commonInfoDiv.empty();  
			resultsTable.hide(); 
		    resultsTable.find('tbody').empty();    
			queryString = $.trim($(this).text());  
			$.getJSON('https://www.googleapis.com/youtube/v3/search?q='+queryString+'&key='+myKey+'&part=snippet&type=video&maxResults=10',function(data){
			    i=0;
				data.items.forEach(function (item) { 
					idsArr[i] = item.id.videoId;
					i++; 
				});      
				
				$.getJSON('https://www.googleapis.com/youtube/v3/videos?id='+idsArr.join(',')+'&part=snippet,statistics&key='+myKey,function(data){
					$.ajax({  
						url: 'save.php',          
						type: 'POST',    
						data: { myPostData : JSON.stringify(data) , query : queryString},           
						dataType: 'json',
						async: false,
						success: function(msg) {  
						    sumCountLikes = 0;
						    sumCountDislikes = 0;
							commonInfoDiv.append('<p><strong>Запрос: '+msg.name+'</strong></p>' );    
							commonInfoDiv.append('<p><strong>Количество результатов: '+msg.count+'</strong></p>' );    
							msg.items.forEach(function (item) {   		   					
								resultsTable.append('<tr><td>'+item.title+'</td><td>'+item.description+'</td><td>'+item.likeCount+'</td><td>'+item.dislikeCount+'</td></tr>' );   
                                sumCountLikes = sumCountLikes + parseInt(item.likeCount);								
                                sumCountDislikes = sumCountDislikes + parseInt(item.dislikeCount);								
							});    
							commonInfoDiv.append('<p>Нравится: '+sumCountLikes+'</p>' ); 
							commonInfoDiv.append('<p>Не нравится: '+sumCountDislikes+'</p>' );    
						    resultsTable.show();  
						}  
					});
				});
			});
			return false;
		});  
	}); 
</script>