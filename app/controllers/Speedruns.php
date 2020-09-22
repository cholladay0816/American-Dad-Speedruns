<?php

class Speedruns extends Controller
{
    public function __construct()
    {

    }
    //Show the leaderboards
    public function index($optional_parameters=[])
    {
      $this->leaderboards($optional_parameters);
    }
    //Delete a speedrun
    public function delete($optional_parameters=[])
    {
      //Must be logged in
      if($_SESSION['account']=="")
      {
        array_push($_SESSION['errors'],"You must be logged in.");
        header("location:".URLROOT.'/user/login');
        exit();
      }
      $this->postModel = $this->model('SpeedrunModel');
      if($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        //If view clicked, go to the run's URL
        if($_POST['view'])
        {
          header('location:'.URLROOT.'/watch/'.$_POST['view']);
          exit();
        }
        //If deleted, check if owner of run and then delete
        if($_POST['delete'])
        {
          $run = $this->postModel->getRunByID($_POST['delete']);
          if($run->runner_id==$_SESSION['account']->id)
          {
            $this->postModel->deleteSpeedrun($_POST['delete']); //Only delete if submitter is logged in
          }
          header('refresh:0');
          exit();
        }
      }
      $data = ['title' => 'Delete Speedruns'];
      $data['runs'] = $this->postModel->getRunsByID($_SESSION['account']->id);
      $this->view('speedruns/delete', $data);
    }
    //Submitting a run to the site
    public function submit($optional_parameters=[])
    {
      //Must be logged in
      if($_SESSION['account']=="")
      {
        array_push($_SESSION['errors'],"You must be logged in.");
        header("location:".URLROOT.'/user/login');
        exit();
      }

      $this->postModel = $this->model('SpeedrunModel');
      $data = ['title' => 'Submit Run'];
      //Autofilling dropdowns
      $data['categories']=$this->postModel->getCategories();
      $data['platforms']=$this->postModel->getPlatforms();
      //On form submit, clean up data and post it to the server (duplicates deleted automatically)
      if($_SERVER['REQUEST_METHOD'] == 'POST')
      {
        $this->postModel = $this->model('UserModel');
        $data['post']->platform = $this->postModel->sanitize($_POST['platform']);
        $data['post']->category = $this->postModel->sanitize($_POST['category']);
        $data['post']->run_time = $this->postModel->sanitize($_POST['time']);
        $data['post']->url = $this->postModel->sanitize($_POST['url']);
        $this->postModel = $this->model('SpeedrunModel');
        $this->postModel->submitRun($data['post']);
        $this->postModel = $this->model('UserModel');
        //Sends an email to staff to verify new runs manually
        $this->postModel->send_email(SUPPORT,'New Speedrun Submitted','A new speedrun has been uploaded from '.$_SESSION['account']->username.'.<br />'.var_export($data['post'],true).'<br /><br /><a href="'.URLROOT_ADMIN.'/dbmanager">Database Manager</a>','noreply');
        $this->view('speedruns/run_submitted', $data);
      }
      else
      {
        $this->view('speedruns/submit', $data);
      }

    }
    //Displays a leaderboard of all runs
    public function leaderboards($optional_parameters=[])
    {
      $this->postModel = $this->model('SpeedrunModel');
      $data = ['title' => 'Leaderboards'];
      $data['categories']=$this->postModel->getCategories();
      $data['runs']=$this->postModel->getLeaderboard($_GET['category'],$_GET['runner'],$_GET['platform']);
      $data['shame']=$this->postModel->getWallOfShame();
      $this->postModel = $this->model('UserModel');
      for ($i=0; $i < count($data['runs']); $i++) //List of valid runs
      {
        $data['runs'][$i]->runnername = $this->postModel->getUserFromID($data['runs'][$i]->runner_id)->username;
      }
      for ($i=0; $i < count($data['shame']); $i++) //List of dq runs
      {
        $data['shame'][$i]->runnername = $this->postModel->getUserFromID($data['shame'][$i]->runner_id)->username;
      }
      $this->view('speedruns/leaderboards', $data);
    }
    //Shows a page with a list of categories and platforms to pick from
    public function categories($optional_parameters=[])
    {
      $this->postModel = $this->model('SpeedrunModel');
      $data = ['title' => 'Categories'];
      $data['categories']=$this->postModel->getCategories();
      $data['platforms']=$this->postModel->getPlatforms();
      $this->view('speedruns/categories', $data);
    }

}
