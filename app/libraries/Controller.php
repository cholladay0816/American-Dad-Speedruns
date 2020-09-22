<?php
class Controller
{
  public function model($model)
  {
      require_once('../app/models/' . $model . '.php');
      return new $model();
  }
  public function redirect($uri)
  {
    $url = URLROOT_ADMIN;
    $exp = explode('/',$uri);
    for ($i=3; $i < count($exp); $i++)
    {
      $url.='/'.$exp[$i];
    }
    header('location:'.$url);
    exit();
  }
  public function view($view, $data = [], $admin=0)
  {
    if(file_exists('../app/views/' . $view . '.php'))
    { //Load the headers and footers (admin if on admin page)
      require_once('../app/views/inc/header'.($admin==0?'':'_admin').'.php');
      require_once('../app/views/' . $view . '.php');
      require_once('../app/views/inc/footer.php');
    }
    else
    {
      die('Page does not exist.');
    }
  }
}
