
<div class="container mt-4">
    <div class="mb-4">
        <h2>
            Run Categories
        </h2>
    </div>

    <div class="row">
      <?php foreach ($data['categories'] as $category): ?>
        <div class="col-xs-6 col-md-4 my-3">
            <div class="card card-body mb-4 bg-light h-100">
                <div class="row">
                    <div class="col-md-4">
                        <img src="https://americandadspeedruns.com/public/img/american_<?php echo $category == 'Joe%' ? 'joe' : 'dad'?>_speedrun_logo.png" alt="<?php echo $category?>" class="img" width="100px">
                    </div>
                    <div class="col-md-7 ml-3 mt-3">
                        <h4><strong>American Dad</strong></h4>
                        <p>Category: <strong><?php echo $category?></strong></p>
                        <a href="<?php echo URLROOT?>/speedruns/leaderboards?category=<?php echo $category?>" class="btn btn-outline-success btn-block mt-4">View Runs</a>
                    </div>
                </div>
            </div>
        </div>
      <?php endforeach; ?>
    </div>
</div>

<div class="container-fluid col-11 mt-4">
    <div class="mb-4">
        <h2>
            Platforms
        </h2>
    </div>

    <div class="row">
      <?php foreach ($data['platforms'] as $category): ?>
        <div class="col-xs-6 col-md-3 my-3">
            <div class="card card-body mb-4 bg-light h-100">
                <div class="row">
                    <div class="col-md-4">
                        <img src="https://americandadspeedruns.com/public/img/american_dad_speedrun_logo.png" alt="<?php echo $category?>" class="img" width="100px">
                    </div>
                    <div class="col-md-7 ml-3 mt-3">
                        <h4><strong>American Dad</strong></h4>
                        <p>Category: <strong><?php echo $category?></strong></p>
                        <a href="<?php echo URLROOT?>/speedruns/leaderboards?platform=<?php echo $category?>" class="btn btn-outline-success btn-block mt-4">View Runs</a>
                    </div>
                </div>
            </div>
        </div>
      <?php endforeach; ?>
    </div>
</div>
