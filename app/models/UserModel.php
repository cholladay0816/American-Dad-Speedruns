<?php

class UserModel
{
  private $db;

  public function __construct()
  {
    $this->db = new Database;
  }
  //Clamps a value between a min and max
  public function clamp($current, $min, $max)
  {
    return max($min, min($max, $current));
  }
  //Sends an email
  public function send_email($target, $title,$content, $from='noreply')
  {
    $to = $target;
    $subject = SITENAME.' - '.$title;
    $message = "<html><head><h1>".$title."</h1></head><body>".$content."</body></html>";
    $headers = "";
    $headers  .= "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\n";
    $headers .= "From:".$from."@".EMAIL_DOMAIN;
    mail($to,$subject,$message, $headers);
  }
  //Sanitizes a string
  public function sanitize($text)
  {
    return htmlspecialchars(filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
  }
  //Gets all data but password and email from user
  public function getUserFromID($id)
  {
    $sql = "SELECT * FROM `accounts` WHERE `id` = :id LIMIT 1";
    $this->db->query($sql);
    $this->db->bind('id', $id);
    $user = $this->db->single();
    $user->password='';
    $user->email='';
    return $user;
  }
  //Gets username from an ID
  public function getUsernameFromID($id)
  {
    $sql = "SELECT `username` FROM `accounts` WHERE `id` = :id LIMIT 1";
    $this->db->query($sql);
    $this->db->bind('id', $id);
    return $this->db->single()->username;
  }
  //Gets all data from a user (including email and password)
  public function getFullUserFromID($id)
  {
    $sql = "SELECT * FROM `accounts` WHERE `id` = :id LIMIT 1";
    $this->db->query($sql);
    $this->db->bind('id', $id);
    return $this->db->single();
  }
  //Delete account using current Session
  public function deleteAccount()
  {
    $sql = "DELETE FROM `accounts` WHERE `id` = :id";
    $this->db->query($sql);
    $this->db->bind('id',$_SESSION['account']->id);
    $this->db->execute();
  }
  //Gets a user by username
  public function getUserFromName($id)
  {
    $sql = "SELECT * FROM `accounts` WHERE `username` = :name LIMIT 1";
    $this->db->query($sql);
    $this->db->bind('name', $id);
    $user = $this->db->single();
    $user->password='';
    $user->email='';
    return $user;
  }
  public function getuserFromEmail($id)
  {
    $sql = "SELECT * FROM `accounts` WHERE `email` = :name LIMIT 1";
    $this->db->query($sql);
    $this->db->bind('name', $id);
    $user = $this->db->single();
    $user->password='';
    $user->email='';
    return $user;
  }
  public function hashPass($password)
  {
		return password_hash($password, ENC_METHOD);
	}
  public function verifyPass($hashed_pass, $unhashed_pass)
  {
    return password_verify($unhashed_pass, $hashed_pass);
  }
  public function createUser($username, $password, $email)
  {
    if(!is_null($this->getUserFromName($username)->username))
    {
      return 'Name is taken';
    }
    if(!is_null($this->getuserFromEmail($email)->username))
    {
      return 'Email is taken';
    }
    $sql = "INSERT INTO `accounts` (`username`, `password`, `email`) VALUES (:username,:password,:email)";
    $this->db->query($sql);
    $this->db->bind('username',$username);
    $this->db->bind('password',$password);
    $this->db->bind('email',$email);
    $this->db->execute();
    return !is_null($this->getUserFromName($username)->username)?'success':'failed';
  }
  public function login($username, $pass)
  {
    $sql = "SELECT * FROM `accounts` WHERE `username` = :username LIMIT 1";
    $this->db->query($sql);
    $this->db->bind('username', $username);
    $user = $this->db->single();
    if(is_null($user))
    {
      return false;
    }
    if(!$this->verifyPass($user->password, $pass))
    {
      return false;
    }
    $user->password = "";
    $_SESSION['account']=$user;
    return true;
  }
}
?>
