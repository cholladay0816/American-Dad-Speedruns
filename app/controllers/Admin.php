<?php

class Admin extends Controller
{
    public function __construct()
    {

    }

    public function index($optional_parameters = [])
    {
      $this->postModel = $this->model('AdminModel');
      $this->postModel->mustBeLoggedIn();
      $data['title']='Admin Home';
      header('location:'.URLROOT_ADMIN.'/dbmanager'); //If logged in, go to database manager
      exit();
    }
    public function login($optional_parameters = [])
    {
      if($_SESSION['admin']!='')
      {
        header('location:'.URLROOT_ADMIN.'/dbmanager');
        exit();
      }
      $data['title']='Login';
      if($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        $this->postModel = $this->model('AdminModel');
        $admin = $this->postModel->getAdminByName($_POST['name']);
        if($admin->id!=null)
        {
          if($this->postModel->verifyPass($admin->password, $_POST['password']))
          {
            $admin->password='';
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin;
            $this->redirect($_SERVER['REQUEST_URI']);
          }
        }
      }
      $this->view('admin/login',$data,1);
    }
    public function logout()
    {
      $_SESSION['admin']='';
      header('location:'.URLROOT_ADMIN);
      exit();
    }
    public function dbmanager($optional_parameters = [])
    {
      //Make sure user is logged in
      $this->postModel = $this->model('AdminModel');
      $this->postModel->mustBeLoggedIn();
      //Get all unverified runs to show
      $this->postModel = $this->model('SpeedrunModel');
      $data['runs'] = $this->postModel->getUnverifiedRuns();
      $this->postModel = $this->model('UserModel');
      for ($i=0; $i < count($data['runs']); $i++)
      {
        $data['runs'][$i]->runnername = $this->postModel->getUsernameFromID($data['runs'][$i]->runner_id);
      }
      if($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        $this->postModel = $this->model('SpeedrunModel');
        foreach ($_POST as $key => $value)
        {
          foreach ($data['runs'] as $run)
          {
            if($run->id == $key)
            {
              if($value=='Verify')
              {
                $this->postModel->verifyRun($key);
                header('refresh:0');
                exit();
              }
              if($value == 'Delete')
              {
                $this->postModel->deleteSpeedrun($key);
                header('refresh:0');
                exit();
              }
            }
          }
        }
      }
      $this->view('admin/dbmanager',$data,1);
    }
  }
?>
