<form method="post">

<div class='container my-5'>

<h1><?php echo $data['title']?></h1>
<p>
  Deleting a speedrun is irreversable.  Use this page at your own risk.
</p>
<div class='container' id='deleteContainer'>
<?php foreach ($data['runs'] as $run): ?>
  <div class="card card-body mb-4 bg-light">
      <div class="row">
          <div class="col-1">
              <img src="<?php echo $run->category != 'Joe%' ? URLROOT.'/public/img/american_dad_speedrun_logo.png' : URLROOT.'/public/img/american_joe_speedrun_logo.png' ?>" alt="latest_run_<?php echo $run->id?>" class="img" width="50px">
          </div>
          <div class="col-md-2">
              <p>Platform:</p>
              <p><strong><?php echo $run->platform?></strong></p>
          </div>
          <div class="col-md-2">
              <p>Run type:</p>
              <p><strong><?php echo $run->category?></strong></p>
          </div>
          <div class="col-md-2">
              <p>Placement:</p>
              <p><strong><?php echo $run->placement?></strong></p>
          </div>
          <div class="col-md-1">
              <p>Time:</p>
              <p><strong><?php echo $run->run_time?>s</strong></p>
          </div>
          <div class="col btn-group">
              <button type='submit' name= 'view' value = "<?php echo $run->id?>" class="btn btn-outline-primary">View Run</button>
              <button type='submit' name='delete' value='<?php echo $run->id?>' class="btn btn-outline-danger">Delete Run</button>
          </div>
      </div>
  </div>
<?php endforeach; ?>
</div>

</div>

</form>
