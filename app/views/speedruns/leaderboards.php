
<script type='text/javascript'>

jQuery(document).ready(function() {
  jQuery("time.timeago").timeago();

});

</script>

<div class="container mt-4">

    <div class="card card-body mb-4 bg-light">

        <div class="mb-4">
            <div class="row">
                <div class="col-xs-2 col-md-2 center">
                	<img src="<?php echo URLROOT?>/public/img/american_<?php echo $_GET['category']=='Joe%' ? 'joe' : 'dad'?>_speedrun_logo.png" alt="logo" width="80%" style='max-height:200px;'>
                </div>
                <div class="col-xs-5 col-md-3 mt-4">
                    <h2>American Dad</h2>
                    <p>Category: <strong><?echo ($_GET['category']=='' ? 'All' : $_GET['category'] );?></strong></p>
                </div>
                <div class="container col-xs col-md mt-4">
                  <div class='row'>

                	<a href="<?php echo URLROOT?>/speedruns/leaderboards" class='col btn btn-<?php echo strtolower($_GET['category'])=='' ? "" : "outline-"?>success'>All</a>

                    <?php foreach ($data['categories'] as $category): ?>
                      <a href="<?php echo URLROOT?>/speedruns/leaderboards?category=<?php echo $category?>" class= 'col btn btn-<?php echo strtolower($_GET['category'])==strtolower($category) ? "" : "outline-"?>success'><?php echo $category?></a>
                    <?php endforeach; ?>
                    <div class='col-8 '>

                    </div>
                    <a href="<?php echo URLROOT?>/speedruns/submit" class="btn btn-primary mt-3 col-4">Submit a run</a>
                  </div>

                </div>
            </div>
        </div>
      </div>
        <table style="<?php echo count($data['runs'])>0?'':'display:none'?>" class="table table-striped bg-light">
    <thead class="thead-dark">
        <tr>
            <th class="d-sm-table-cell" scope="col">Rank</th>
            <th class="d-sm-table-cell" scope="col">Player</th>
            <th class="d-sm-table-cell" scope="col">Time</th>
            <th class="d-none d-sm-table-cell" scope="col">Verified</th>
            <th class="d-none d-sm-table-cell" scope="col">Platform</th>
            <th class="d-none d-sm-table-cell" scope="col">Date</th>
            <th class="d-none d-sm-table-cell" scope="col">Video</th>
        </tr>
    </thead>
    <tbody>
      <?php foreach ($data['runs'] as $run): ?>
        <tr>
                <th class="d-sm-table-cell center" scope="row"><?php echo $run->placement_img?> <?php echo $run->placement?></th>
                <td class="d-sm-table-cell center"><a href="?runner=<?php echo $run->runner_id?>"><?php echo $run->runnername?></a></th>
                <td class="d-sm-table-cell center"><i class="fas fa-stopwatch"></i> <?php echo number_format($run->run_time,2)?>s</th>
                <td class="d-none d-sm-table-cell center"><?php echo $run->run_status=='Verified'?'Yes':'No'?></th>
                <td class="d-none d-sm-table-cell center"><a class='text text-dark' href="?platform=<?php echo $run->platform?>"><?php echo $run->platform?></a></th>
                <td class="d-none d-sm-table-cell center"><time class="timeago" datetime="<?php echo $run->creation_date?>"><?php echo $run->creation_date?></time></th>
                <td class="d-none d-sm-table-cell center"><a href="<?php echo URLROOT.'/watch/'.$run->id?>" class="btn btn-outline-success btn-sm">View Run</a></th>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

    <p class="mx-auto my-5" style="<?php echo count($data['shame'])>0?'':'display:none'?>"><h1>The Wall of Shame</h1></p>
        <table class="table table-striped bg-light my-5" style="<?php echo count($data['shame'])>0?'':'display:none'?>">
        <thead class="thead-dark">
            <tr>
                <th class="d-sm-table-cell" scope="col">Rank</th>
                <th class="d-sm-table-cell" scope="col">Player</th>
                <th class="d-sm-table-cell" scope="col">Time</th>
                <th class="d-none d-sm-table-cell" scope="col">Verified</th>
                <th class="d-none d-sm-table-cell" scope="col">Platform</th>
                <th class="d-none d-sm-table-cell" scope="col">Date</th>
                <th class="d-none d-sm-table-cell" scope="col">Video</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($data['shame'] as $run): ?>
          <tr>
                  <th class="d-sm-table-cell center" scope="row"><?php echo $run->placement_img?> <?php echo $run->placement?></th>
                  <td class="d-sm-table-cell center"><a href="?runner=<?php echo $run->runner_id?>"><?php echo $run->runnername?></a></th>
                  <td class="d-sm-table-cell center"><i class="fas fa-stopwatch"></i> <?php echo number_format($run->run_time,0)?>s</th>
                  <td class="d-none d-sm-table-cell center"><?php echo $run->run_status=='Verified'?'Yes':'No'?></th>
                  <td class="d-none d-sm-table-cell center"><a class='text text-dark' href="?platform=<?php echo $run->platform?>"><?php echo $run->platform?></a></th>
                  <td class="d-none d-sm-table-cell center"><time class="timeago" datetime="<?php echo $run->creation_date?>"><?php echo $run->creation_date?></time></th>
                  <td class="d-none d-sm-table-cell center"><a href="<?php echo URLROOT.'/watch/'.$run->id?>" class="btn btn-outline-danger btn-sm">View Run</a></th>
          </tr>
        <?php endforeach; ?>
      </tbody>


    </div>
</div>
