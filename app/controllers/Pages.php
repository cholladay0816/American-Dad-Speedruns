<?php

class Pages extends Controller
{
    public function __construct()
    {

    }
    //Homepage
    public function index()
    {
      $data = ['title' => 'Home'];
      $this->postModel = $this->model('SpeedrunModel');
      $data['featured']=$this->postModel->getRandomRun();
      $data['recents']=$this->postModel->getRecentRuns(5);
      for ($i=0; $i < count($data['recents']); $i++)
      {
        $data['recents'][$i]->placement = $this->postModel->getCategoryPlacement($data['recents'][$i]->id, $data['recents'][$i]->category);
      }
      $this->postModel = $this->model('UserModel');
      $data['featured']->runnername=$this->postModel->getUserFromID($data['featured']->runner_id)->username;
      for ($i=0; $i < count($data['recents']); $i++)
      {
        $data['recents'][$i]->runnername = $this->postModel->getUserFromID($data['recents'][$i]->runner_id)->username;
      }
      $this->view('pages/index', $data);
    }
}
?>
