<div class="left">

  <form action="userview.php" method="get">
    <div class="left_nav">
      <h3>Users</h3>
      <div class="inner_box">
        <ul>
          <li><a href="user.php">Add User</a></li>
          <li><a href="userview.php?op=view_all">List Users</a></li>
          <li>Find User:</li>
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

  <form action="hardware.php?op=view" method="get">
    <div class="left_nav">
      <h3>Hardware</h3>
      <div class="inner_box">
        <ul>
          <li><a href="hardware.php">Hardware Home</a></li>
          <li><a href="hardware.php?op=something">Other hardware Link</a></li>
          <li>Find Hardware:</li>
          <li><input id="search_asset" name="search_asset" type="text" size="15" /></li>
          <li><input type="submit" value=" Go " /></li>
        </ul>
      </div>
      <div id="search_assetupdate" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('search_asset','search_assetupdate','_hardware.php');
      // ]]>
      </script>
    </div>
  </form>

  &nbsp;   

  <form action="hardware.php?op=view" method="post">
    <div class="left_nav">
      <h3>Softare</h3>   
      <div class="inner_box">
        <ul>
          <li><a href="software.php">Add Software</a></li>
          <li><a href="software.php?op=something">Other software Link</a></li>
          <li>Search Software:</li>
          <li><input id="search_software" name="search_software" type="text" size="15" /></li>
          <li><input type="submit" value=" Go " /></li>
        </ul>
      </div>
      <div id="search_softwareupdate" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('search_software','search_softwareupdate','_software.php');
      // ]]>
      </script>
    </div>
  </form>

</div>

