<div class="left">

  <form action="userview.php" method="get">
    <div class="left_nav">
      <h3>Users</h3>
      <div class="inner_box">
        <ul>
          <li><a href="user.php">Add User</a></li>
          <li><a href="userview.php?op=view_all">List Users</a></li>
          <li>User Name:</li>
          <li><input id="user_name" name="user_name" type="text" size="15" /></li>
          <li><input type="submit" value=" Go " /></li>
        </ul>
      </div>
      <div id="user_nameupdate" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('user_name','user_nameupdate','_users.php');
      // ]]>
      </script>
    </div>
  </form>

  &nbsp; 

  <div class="left_nav">
    <h3>Hardware</h3>
    <div class="inner_box">
      <ul>
        <li><a href="hardware.php">Add Hardware</a></li>
        <li><a href="hardwareview.php?op=view_all">List Hardware</a></li>
      </ul>
    </div>
  </div>


  &nbsp;   

  <form action="softwareview.php" method="get">
    <div class="left_nav">
      <h3>Softare</h3>   
      <div class="inner_box">
        <ul>
          <li><a href="software.php">Add Software</a></li>
          <li><a href="softwareview.php?op=view_all">List Software</a></li>
          <li>Software Title:</li>
          <li><input id="software_title" name="software_title" type="text" size="15" /></li>
          <li><input type="submit" value=" Go " /></li>
        </ul>
      </div>
      <div id="software_titleupdate" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('software_title','software_titleupdate','_software.php');
      // ]]>
      </script>
    </div>
  </form>

</div>

