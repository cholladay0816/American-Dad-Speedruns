<div class="container my-1">

<?php
if($data['run']->run_status == "Disqualified") {

	echo "<div class='alert alert-danger' style='display:".($data['dq_reason']->id!=null?'block':'none')."'>This run was disqualified for <strong>".$data['dq_reason']->reason."</strong><div class='float-right'><a href='".$data['dq_reason']->url."' target='_blank'>Evidence</a></div></div>";

	}
	?>

    <div class="card card-body bg-light">
        <div class="row">
            <div class="col-12">
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" src="<?php echo $data['embed']; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            <div class="col-auto mt-2">
                <div class="float-right">
                    <div class="row mt-2">
                        <div class="col-auto"><p>Runner: <strong>
                          <a href='https://americandadspeedruns.com/speedruns/leaderboards?runner_id=<?php echo $data['run']->runner_id?>'><?php echo $data['runnername']!=''?$data['runnername']:"N/A"?></a></strong></p></div>
                        <div class="col-auto"><p>Category: <strong><a href='https://americandadspeedruns.com/speedruns/leaderboards?category=<?php echo $data['run']->category?>'><?php echo $data['run']->category!=''?$data['run']->category:"N/A" ?></a></strong></p></div>
                        <div class="col-auto"><p>Time: <i class="fas fa-stopwatch"></i> <strong><?php echo $data['run']->run_time!=''?number_format($data['run']->run_time,3).'s' : "N/A";?></strong></p></div>
                         <div class="col-auto"><p>Placement: <strong><?php echo $data['run']->run_status!='Disqualified' ? ($data['placement']!=''?$data['placement']:'N/A') : '<a class="text-danger">Disqualified</a>'; ?></strong></p></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
