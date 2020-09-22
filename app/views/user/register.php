<div class='container col-md-4 my-5'>
    <form class="form-signin" action="register" method="POST">
        <h1 class="h3 mb-4 font-weight-normal">Create an account</h1>

        <div class="form-group">
            <label for="inputUsername" class="sr-only">Username</label>
            <input type="text" id="inputUsername" class="form-control" name="username" placeholder="Username" required autofocus>
        </div>

        <div class="form-group">
            <label for="inputEmail" class="sr-only">Email Address</label>
            <input type="email" id="inputEmail" class="form-control" name="email" placeholder="Email Address" required>
        </div>

        <div class="form-group">
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
        </div>

        <div class="form-group">
            <label for="inputPassword" class="sr-only">Confirm Password</label>
            <input type="password" id="inputPassword" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
        </div>

        <button class="btn btn-lg btn-success btn-block" type="submit">Create account</button>

    </form>


</div>
