<?php

class User extends Controller
{

    public function __construct()
    {
      $this->postModel = $this->model('UserModel');
    }
    //User hub, shows all user abilities
    public function index()
    {
      //Must be logged in
      if($_SESSION['account']=='')
      {
        header('location:'.URLROOT);
        exit();
      }

      $data = ['title' => 'My Account'];
      $data['account'] = $this->postModel->getUserFromID($_SESSION['account']->id);
      $this->view('user/index', $data);
    }
    //Gathers user's data and sends all info we have via email (privacy compliancy for EU)
    public function request_data()
    {
      if($_SESSION['account']=='')
      {
        array_push($_SESSION['errors'],'You are not logged in.');
        header('location:'.URLROOT.'/user/login');
        exit();
      }
      $data = ['title' => 'Request Data'];
      if($_SERVER['REQUEST_METHOD']=='POST')
      {
        $fulluser = $this->postModel->getFullUserFromID($_SESSION['account']->id);
        $user = var_export($fulluser,true);
        $this->postModel = $this->model('SpeedrunModel');
        $runs = var_export($this->postModel->getRunsByID($_SESSION['account']->id),true);
        $this->postModel = $this->model('UserModel');
        $this->postModel->send_email($fulluser->email,'Your Requested Information','You recently made a request for all of your data on AmericanDadSpeedruns.com.  Here is all of the information we have on file:<br />'.$user.'<br />'.$runs);
        array_push($_SESSION['successes'], 'Information Request Emailed');
        header('location:'.URLROOT);
        exit();
      }
      $this->view('user/request_data', $data);
    }

    //Deletes an account, removes all runs and closes the account
    public function delete_account()
    {
      if($_SESSION['account']==''){
        header('location:'.URLROOT);
        exit();
      }
      $this->postModel = $this->model('UserModel');
      if($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        $pass = $this->postModel->sanitize($_POST['password']);
        if($this->postModel->login($_SESSION['account']->username,$pass))
        {
          $this->postModel->deleteAccount();
          $this->model('SpeedrunModel')->deleteMySpeedruns();

          header('location:'.URLROOT.'/user/logout');
          exit();
        }
      }
      $data = ['title' => 'Delete Account'];

      $this->view('user/delete_acct', $data);
    }
    //Signup
    public function register()
    {
      if($_SESSION['account']!="")
      {
        array_push($_SESSION['errors'],"You are already logged in.");
        header("location:".URLROOT);
        exit();
      }
      $data = ['title' => 'Register'];
      if($_SERVER['REQUEST_METHOD'] == 'POST') //Some error checking before attempting to upload to server and more error checking
      {
        $username = $this->postModel->sanitize($_POST['username']);
        $password = $this->postModel->sanitize($_POST['password']);
        $err=0;
        if($username=='')
        {
          $err=1;
          array_push($_SESSION['errors'],'Username invalid.');
        }
        if($password=='')
        {
          $err=1;
          array_push($_SESSION['errors'],'Password invalid.');
        }
        if($password != $this->postModel->sanitize($_POST['confirm_password']))
        {
          $err=1;
          array_push($_SESSION['errors'],'Passwords do not match.');
        }
        $password = $this->postModel->hashPass($password);
        $email = $this->postModel->sanitize($_POST['email']);
        if($email=='')
        {
          $err=1;
          array_push($_SESSION['errors'],'Email invalid.');
        }
        if($err==0)
        {
          $userstatus = $this->postModel->createUser($username,$password, $email);
          if($userstatus=='success')
          {
            $_SESSION['account'] = $this->postModel->getUserFromName($username);
            header('location:'.URLROOT);
            exit();
          }
          else
          {
            array_push($_SESSION['errors'],'Login failed: '.$userstatus);
          }
        }
      }
      $this->view('user/register', $data);
    }

    //Login
    public function login()
    {
      if($_SESSION['account']!="")
      {
      array_push($_SESSION['errors'],"You are already logged in.");
      header("location:".URLROOT);
      exit();
      }
      $data = ['title' => 'Login'];
      if($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        $username = $this->postModel->sanitize($_POST['username']);
        $password = $this->postModel->sanitize($_POST['password']);
        //On success, log in user and go to home
        if($this->postModel->login($username,$password))
        {
          header('location:'.URLROOT);
          exit();
        }
        //On fail, show an error
        else
        {
          $data['error']='Error: incorrect login.';
        }
      }
      $this->view('user/login', $data);
    }
    //Logout
    public function logout()
    {
        $data = ['title' => 'Logout'];
        session_unset();
        header('location:'.URLROOT);
        exit();
    }
}

?>
