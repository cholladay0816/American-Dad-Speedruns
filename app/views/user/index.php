<div class="container mt-4">

    <p class="display-4">
        <?php echo $data['account']->username?>
    </p>

    <div class="row my-5">
      <div class='col'>
        <div class="card card-body">
          <h3 class='card-title'>Account Settings</h3>
          <div class='btn-group my-2'>
            <a href="<?php echo URLROOT?>/speedruns/submit" class='btn btn-outline-primary'>Submit a Run</a>
            <a href="<?php echo URLROOT?>/user/request_data" class='btn btn-outline-info'>Request Data</a>
            <a href="<?php echo URLROOT?>/speedruns/delete" class='btn btn-outline-warning'>Delete a Run</a>
            <a href="<?php echo URLROOT?>/user/delete_account" class='btn btn-outline-danger'>Delete Account</a>
          </div>
        </div>
      </div>
    </div>


</div>
