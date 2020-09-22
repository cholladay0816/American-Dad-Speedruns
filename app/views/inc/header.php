<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta property='og:title' content='<?php echo $data['title']?>'/>
    <meta property='og:type' content='website'/>
    <meta property='og:description' content='<?php echo $data['description']?>'/>
    <meta property='og:image' content='<?php echo URLROOT?>/public/img/american_dad_speedrun_logo.png'/>

    <title><?php echo $data['title'].' - '.SITENAME; ?></title>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/style.css">
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top" style="padding: 0.5%;">
  <div class="container-fluid">
      <a class="navbar-brand" href="<?php echo URLROOT?>"><i class="fas fa-trophy text-warning"></i> <strong><?php echo SITENAME?></strong></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">
              <li class="nav-item">
                  <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] =='/speedruns/categories' ? 'active' : ''?>" href="<?php echo URLROOT?>/speedruns/categories">Categories</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link <?php echo substr($_SERVER['REQUEST_URI'],0,23) =='/speedruns/leaderboards' ? 'active' : ''?>" href="<?php echo URLROOT?>/speedruns/leaderboards">Leaderboards</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] =='/speedruns/submit' ? 'active' : ''?>" href="<?php echo URLROOT?>/speedruns/submit">Submit Run</a>
              </li>
          </ul>
          <ul class="navbar-nav ml-auto">
          <?php
          	if(isset($_SESSION['account'])){
              echo ('<li class="nav-item">
                  <a class="nav-link '. ($_SERVER["REQUEST_URI"] =="/user/index" ? "active" : "").'" href="'.URLROOT.'/user/index">Account</a>
              </li>');
              echo('<li class="nav-item">
                  <a class="nav-link" href="'.URLROOT.'/user/logout">Logout</a>
              </li>');
              }
              else{
              echo('<li class="nav-item">
                  <a class="nav-link '. ($_SERVER["REQUEST_URI"] =="/user/login" ? "active" : "").'" href="'.URLROOT.'/user/login">Login</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link '. ($_SERVER["REQUEST_URI"] =="/user/register" ? "active" : "").'" href="'.URLROOT.'/user/register">Register</a>
              </li>');
              }
              ?>
          </ul>
      </div>
    </div>
  </nav>
  <?php
  $marginCount=0;
  $margin_inc_amt = 50;
//LOOP THRU ERRORS
  foreach ($_SESSION['errors'] as $msg):
  ?>
  <div class="container col-6 position-fixed fixed-top mx-auto mt-5" style="pointer-events: none">
    <div class="row mt-4">
    <div class="alert alert-danger alert-dismissible fade show mx-auto mt-3" style="z-index:1024;pointer-events: auto" role="alert">
      <strong>ERROR: </strong> <?php echo $msg; ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </div>
  </div>
<?php
$marginCount+=$margin_inc_amt;
endforeach;
$_SESSION['errors']=[];
