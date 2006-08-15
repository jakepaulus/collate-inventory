<?php

$op = $_GET['op'];

switch($op){
  case "view_all";
    list_users();
    break;
  
  default:
    view_details($name);
    break;
}
	
function view_details($name){
  $name = $_GET['user_name'];
  require_once('db_connect.php');
  $row = mysql_query("SELECT * FROM users WHERE name='$name'");

  if(list($uid,$name,$phone,$altphone,$address,$city,$state,$zip,$email) = mysql_fetch_row($row)) { // row exists, display data
    require_once('header.php');
    echo "<div id=\"main\">".
         "<h1>Details for $name:</h1>".
         "<p>Address:<br />".
         "$address <br /> $city, $state $zip</p>".
         "<p>Telephone Numbers:<br />".
         "$phone<br />Alternate: $altphone</p>".
         "<p>Email Address:<br />".
         "<a href=\"mailto:$email\">$email</a>";

	 
    //Display hardware that belongs to the user:
   echo "<h1>Hardware:</h1>"; 
    
    $sql = "SELECT * FROM hardware WHERE uid=$uid";
    
    if(mysql_num_rows(mysql_query($sql)) < "1"){
      echo "This user has no hardware assigned to them.";
    }
    else {
    $row = mysql_query($sql);
      echo "<table cellspacing=\"7\">".
             "<tr><th>Name:</th><th>Asset ID:</th><th>Serial Number:</th></tr>";
      while(list($hid,$asset,$serial,$title,$desc,$value,$uid,$assigned,$returned) = mysql_fetch_row($row)){
        echo "<tr><td><a href=\"hardwareview.php?hid=$hid\">$title</a></td><td>$asset</td><td>$serial</td>";
      }
      echo "</table>";
    }
    
    
    echo "</div>";
    require_once('footer.php');
  }  
  else { // a row doesn't exist with that user's name: show error
    $result = "I'm sorry, you must supply a valid name in order for me to find what you're looking for.";
    require_once('infopage.php');
  } 

} // Ends view_details function

function list_users(){
	require_once('db_connect.php');
        require_once('header.php');
        
    echo "<div id=\"main\"><h1>All Users</h1>";
        
    $limit              = "25";               
    $query_count   = "SELECT * FROM users";    
    $result_count   = mysql_query($query_count);    
    $totalrows       = mysql_num_rows($result_count); 
    $numofpages   = round($totalrows/$limit, 0); 
    
    if(empty($_GET['page'])){
      $page = "1";
    }
    else {
      $page = $_GET['page'];
    }

    $limitvalue = $page * $limit - ($limit); 
    $query  = "SELECT * FROM users ORDER BY name ASC LIMIT $limitvalue, $limit";        
    $result = mysql_query($query) or die("Error: " . mysql_error()); 

    if(mysql_num_rows($result) == 0){
        echo("Nothing to Display!");
    }

    $bgcolor = "#E0E0E0"; // light gray

    echo "<table>".
           "<tr><th>Name</th><th>City</th><th>Email Address</th></tr>";
    
    while(list($uid,$name,$phone,$altphone,$address,$city,$state,$zip,$email) = mysql_fetch_row($result)){
        if ($bgcolor == "#E0E0E0"){
            $bgcolor = "#FFFFFF";
        }else{
            $bgcolor = "#E0E0E0";
        }
        echo "<tr bgcolor=\"$bgcolor\"><td><a href=\"userview.php?user_name=$name\">$name</a></td><td>$city</td><td><a href=\"$email\">$email</a></td></tr>";
    }

    echo("</table>");
    
    if($page != "1"){ 
        $pageprev = $page - "1";
        
        echo("<a href=\"userview.php?op=view_all&amp;page=$pageprev\"> Prev</a> "); 
    }
    
    $i = "1";

    if($page > $i){
      while($i < $page){
        echo " <a href=\"userview.php?op=view_all&amp;page=$i\">$i</a> ";
        $i++;
      }
    }
    if($numofpages > "1"){
      echo $page;
    }
    $i = $page + "1";
    if($numofpages-$page > "0"){
        while($i < $numofpages + "1"){
          echo " <a href=\"userview.php?op=view_all&amp;page=$i\">$i</a> ";
          $i++;
        }
    }
    
    if($page <= $numofpages){
      $nextpage = $page + "1";
      echo " <a href=\"userview.php?op=view_all&amp;page=$nextpage\">Next</a>";
    }
    if($limitvalue + $limit < $totalrows){
      $upperlimit = $limitvalue + $limit;
    }
    else {
      $upperlimit = $totalrows;
    }
    
    if($limitvalue == "0"){
      $lowerlimit = "1";
    }
    else {
      $lowerlimit = $limitvalue + "1";
    }
    echo "<br /><br />Showing $lowerlimit - $upperlimit out of $totalrows";
    echo "</div>";
    mysql_free_result($result);
    require_once('footer.php');
} // Ends list_users function
?>
