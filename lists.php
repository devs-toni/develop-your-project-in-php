
<?php

session_start();
require_once('config.php');
require_once(DIR_MODELS . 'User.php');
require_once(DIR_MODELS . 'Movie.php');
require_once(DIR_TEMPLATES . 'Templates.php');
require_once(DIR_REPO . 'Repository.php');
require_once(DIR_SESSION . 'Session.php');

if (isset($_SESSION['user']))
  Session::checkSessionExpiration();

$db = new Repository();
$isLogged = false;
$isAdmin = false;

if (isset($_SESSION['user'])) {
  $isLogged = true;
  $isAdmin = $db->isAdmin($_SESSION['user']);
}

if (isset($_REQUEST['page'])) 
  $_SESSION['lastPage'] = $_REQUEST['page'];


Templates::addHeader('Lists', ['alerts'], ['manageLists']);
include_once(DIR_TEMPLATES . 'aside.php');
include_once(DIR_TEMPLATES . 'manageLists.php');

Templates::addFooter([]);
?>
<script>
  const listsReturn = document.querySelector('#listsReturn');
  listsReturn.addEventListener('click', () =>   window.location.href = "index.php");
</script>