<div class='container mt-2'>


</div>

<div class="container mt-4">

    <div class="card card-body bg-light">
        <div class="row">
            <div class="col-md-9">
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?php echo substr($data['featured']->url, 17) ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            <div class="col-md-auto">
                <div class="float-right">
                    <h3 class="mb-4">Featured Run</h3>
                    <p>Runner: <a href="<?php echo URLROOT?>/speedruns/leaderboards?runner=<?php echo $data['featured']->runner_id; ?>"><?php echo $data['featured']->runnername; ?></a></p>
                    <p>Category: <strong><?php echo $data['featured']->category; ?></strong></p>
                    <p>Time: <i class="fas fa-stopwatch"></i> <strong><?php echo $data['featured']->run_time; ?>s</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="mb-4">
        <h2>
            Latest runs
        </h2>
    </div>

    <?php foreach ($data['recents'] as $run): ?>
      <div class="card mb-4 bg-light">
        <div class='card-body'>

          <div class="row">
              <div class="col-lg-1 col-12">
                  <img class='img-fluid' src="<?php echo $run->category != 'Joe%' ? URLROOT.'/public/img/american_dad_speedrun_logo.png' : URLROOT.'/public/img/american_joe_speedrun_logo.png' ?>" alt="latest_run_<?php echo $run->id?>" class="img">
              </div>
              <div class="col-lg-2 col-6">
                  <p>Runner:</p>
                  <p><strong><?php echo $run->runnername?></strong></p>
              </div>
              <div class="col-lg-2 col-6">
                  <p>Platform:</p>
                  <p><strong><?php echo $run->platform?></strong></p>
              </div>
              <div class="col-lg-2 col-6">
                  <p>Run type:</p>
                  <p><strong><?php echo $run->category?></strong></p>
              </div>
              <div class="col-lg-2 col-6">
                  <p>Placement:</p>
                  <p><strong><?php echo $run->placement?></strong></p>
              </div>
              <div class="col-lg-1 col-6">
                  <p>Time:</p>
                  <p><strong><?php echo $run->run_time?>s</strong></p>
              </div>
              <div class="col">
                  <a href="<?php echo URLROOT.'/watch/'.$run->id?>" class="btn btn-outline-success btn mt-4 ml-4">View Run</a>
              </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

</div>

<div class="navbar mt-5 bg-light">
    <div class="container">
        <div class="row">

            <div class="col-md-6">
                <img class="img-fluid" src="<?php echo URLROOT?>/public/img/stan_says.png" width="100%" alt="stan_says">
            </div>
            <div class="col-md-6" style="margin-top: 15%;">
                <h1 class="display-4">Stan Says</h1>

                <h3 class="mb-2">You should join the Spicy Central Discord!</h3>

                <p class="mb-4">All the cool kids are already <b>@here<b>.</p>


                <a href="https://discord.gg/tV9KSPU" target="_blank" aria-describedby="discordBtn" style="background-color: #7289da; color: white;" class="btn btn-lg btn-block"><i class="fab fa-discord"></i> Join the Server!</a>
                <small id="discordBtn" class="form-text text-muted">NOTE - We don't run the server, but colemanchu is the cool dude who created the History of American Dad Speedrunning video and popularized the meme.</small>
            </div>

        </div>
    </div>
</div>
