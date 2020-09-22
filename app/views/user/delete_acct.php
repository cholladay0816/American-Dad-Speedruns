<form method="post">

<div class='container'>
<div class='my-5'>

<h1>WARNING</h1>
<p class='lead text-muted'>
  This will delete all of your runs and userdata, are you sure?
</p>
</div>
<div class='btn-group row col-lg-6 col-md-12'>
  <input class='btn btn-danger' type='button' data-toggle="modal" data-target="#deleteModal" value='Yes'/>
  <input class='btn btn-primary' type='button' value='No'/>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Delete Your Account</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>
          This action is irreversable, and will permanently remove all of your data and speedruns.  As a safety measure, please enter your password below to verify this action.
        </p>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" class="form-control" name='password' id="password">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Confirm</button>
      </div>
    </div>
  </div>
</div>
</form>
