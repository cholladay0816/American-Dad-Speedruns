<?php

class AdminModel
{
  private $db;

  public function __construct()
  {
    $this->db = new Database;
  }
  //Makes sure user is logged in
  public function mustBeLoggedIn()
  {
    if($_SESSION['admin']=='')
    {
      header('location:'.URLROOT_ADMIN.'/login'.substr($_SERVER['REQUEST_URI'],6));
      exit();
    }
  }
  //Gets an admin by name
  public function getAdminByName($name)
  {
    $sql = "SELECT * FROM `admins` WHERE `name` = :name";
    $this->db->query($sql);
    $this->db->bind('name',$name);
    return $this->db->single();
  }
  public function hashPass($password)
  {
    return password_hash($password, ENC_METHOD);
  }
  public function verifyPass($hashed_pass, $unhashed_pass)
  {
    return password_verify($unhashed_pass, $hashed_pass);
  }
}
?>
