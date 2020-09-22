<?php
class Watch extends Controller
{
  public function __construct()
  {

  }
  //Watch page, loads run info and plays the video
  public function index($runid = '')
  {
    if($_GET['run'])
    {
      $runid = $_GET['run'];
    }
    $this->postModel = $this->model('SpeedrunModel');
    $data = [];
    $data['runid'] = $runid;
    $data['run'] = $this->postModel->getRunByID($runid);
    $data['rawplacement'] = $this->postModel->getCategoryPlacement($runid, $data['run']->category);
    $data['placement'] = $data['rawplacement'] . ' ('.$data['run']->category.')';
    $data['dq_reason'] = $this->postModel->getDQReason($runid);
    $this->postModel = $this->model('UserModel');
    $data['runnername'] = $this->postModel->getUserFromID($data['run']->runner_id)->username;
    $data['title'] = '['.$data['run']->run_time.'s] '.$data['runnername'];
    $data['description'] = $data['rawplacement'].' place for category: '.$data['run']->category;
    if($data['run']==null)
    {
      $data = ['title' => 'Run Not Found'];
      $this->view('pages/run_not_found');
      exit();
    }
    $token = substr($data['run']->url, 17);
    $data['embed'] = 'https://youtube.com/embed/' . $token . '?autoplay=1';
    $this->view('pages/watch', $data);
  }
}
?>
