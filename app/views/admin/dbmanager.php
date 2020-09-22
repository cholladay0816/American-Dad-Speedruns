<form method='post'>
<div class='container table-responsive my-5'>
  <table class='table table-hover'>
    <thead class="thead-dark">
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Uploader</th>
      <th scope="col">Category</th>
      <th scope="col">Platform</th>
      <th scope="col">Time</th>
      <th scope="col">URL</th>
      <th scope="col">Actions</th>
    </tr>
    </thead>
    <tbody>
      <?php foreach ($data['runs'] as $run): ?>

      <tr>
        <td scope="col"><?php echo $run->id?></td>
        <td scope="col"><?php echo $run->runnername?></td>
        <td scope="col"><?php echo $run->category?></td>
        <td scope="col"><?php echo $run->platform?></td>
        <td scope="col"><?php echo $run->run_time?></td>
        <td scope="col"><a href='<?php echo $run->url?>'><?php echo $run->url?></a></td>
        <td scope="col">
          <div class='btn-group'>
            <input class='btn btn-primary' type='submit' name='<?php echo $run->id?>' value='Verify' />
            <input class='btn btn-danger' type='submit' name='<?php echo $run->id?>' value='Delete' />
          </div>

        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

</form>
