<div class="left">
  <form action="users.php" method="get">
    <div class="left_nav">
      <h3>Users</h3>
      <div class="inner_box">
        <ul>
          <li><a href="users.php?op=add">Add User</a></li>
          <li><a href="users.php">List Users</a></li>
          <li>Username:</li>
          <li><input name="op" type="hidden" value="show" /><input id="usersearch" name="usersearch" type="text" size="15" /></li>
          <li><input type="submit" value=" Go " /></li>
        </ul>
      </div>
      <div id="usersearch_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('usersearch','usersearch_update','_users.php');
      // ]]>
      </script>
    </div>
  </form>
  &nbsp; 
  <form action="hardware.php?op=show" method="get">
    <div class="left_nav">
      <h3>Hardware</h3>
      <div class="inner_box">
        <ul>
          <li><a href="hardware.php?op=add">Add Hardware</a></li>
          <li><a href="hardware.php">List Hardware</a></li>
	<li>Asset/Serial Number:</li>
          <li><input name="op" type="hidden" value="show" /><input id="search" name="search" type="text" size="15" /></li>
          <li><input type="submit" value=" Go " /></li>
        </ul>
      </div>
      <div id="search_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('search','search_update','_hardware.php');
      // ]]>      
     </script>
    </div>
  </form>
  &nbsp;   
  <form action="software.php" method="get">
    <div class="left_nav">
      <h3>Software</h3>   
      <div class="inner_box">
        <ul>
          <li><a href="software.php?op=add">Add Software</a></li>
          <li><a href="software.php">List Software</a></li>
          <li>Software Title:</li>
          <li><input name="op" type="hidden" value="show" /><input id="title" name="title" type="text" size="15" /></li>
          <li><input type="submit" value=" Go " /></li>
        </ul>
      </div>
      <div id="title_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('title','title_update','_software.php');
      // ]]>
      </script>
    </div>
  </form>
</div>

