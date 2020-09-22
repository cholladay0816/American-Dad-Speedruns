
<div class="container text-center col-4 mt-5">

    <form class="form-signin" method="POST">
        <h1 class="h3 mb-4 font-weight-normal">Please sign in</h1>

	<?php

	if($_GET['registration'] == 'success') {

		echo('
		<div class="alert alert-success">
			You have been successfully registered!
		</div>
		');

	} ?>
        <div class="form-group">
            <p class='lead text-danger'>
              <?php echo $data['error']?>
            </p>
        </div>
        <div class="form-group">
            <label for="inputUsername" class="sr-only">Username</label>
            <input type="text" id="inputUsername" name='username' class="form-control" placeholder="Username" required="" autofocus="">
        </div>

        <div class="form-group">
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" name='password' id="inputPassword" class="form-control" placeholder="Password" required="">
        </div>

        <button class="btn btn-lg btn-success btn-block" type="submit">Sign in</button>

    </form>


</div>
