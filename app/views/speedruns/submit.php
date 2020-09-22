
<div class="container my-5">

<div class="row">
    <div class="col-xs-6 col-md-8">
        <form class="form-signin" target="" method="POST">
            <h1 class="h3 mb-4 font-weight-normal">Submit a run</h1>
            <p class="text-secondary" style="margin-top: -3.2%">Runs typically take a couple hours to be verified, but can take upwards of 2 days.<br><i>For any issues or comments, please email <a href="mailto:<?php echo SUPPORT?>"><?php echo SUPPORT?></a></i></p>

            <div class="form-group">
                <label for="inputPlatform">Platform</label>
                <select class="form-control" name="platform" id="inputPlatform">
                  <?php foreach ($data['platforms'] as $platform): ?>
                    <option><?php echo $platform?></option>
                  <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="inputCategory">Category</label>
                <select class="form-control" onchange="changeImage()" name="category" id="inputCategory">
                  <?php foreach ($data['categories'] as $platform): ?>
                    <option><?php echo $platform?></option>
                  <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="inputTime">Time (in Seconds)</label>
                <input type="number" name="time" pattern="[0-9]+([\.,][0-9]+)?" step="0.001" id="inputTime" class="form-control" placeholder="30.00" maxlength="7" required="">
            </div>

            <div class="form-group">
                <label for="inputRecording">Recording</label>
                <input type="text" name="url" pattern="https:\/\/youtu.be\/.*" id="inputRecording" onchange="setsrc(this.value)" class="form-control" placeholder="https://youtu.be/yourvideohere" aria-describedby="urlHelp" maxlength="35" required="">
                <small id="urlHelp" class="form-text text-muted">Must be a valid <a target="_blank" href="https://i.imgur.com/4UY7C4G.png">shortened url</a> to a video of your run.</small>
            </div>
            <div class='row my-5'>
              <div class="embed-responsive embed-responsive-16by9">
                  <iframe id='iframe' class="embed-responsive-item" src="" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
              </div>
            </div>
            <button class="btn btn-lg btn-success float-right" type="submit">Submit run</button>

        </form>
    </div>

    <div class="col-xs-6 col-md-4" style="margin-top: 12.5%">
        <img id="run_icon" src="<?php echo URLROOT?>/public/img/american_dad_speedrun_logo.png" alt="logo" width="100%">
    </div>

</div>

</div>
<script type="text/javascript">
function changeImage(){
  let icon = document.getElementById('run_icon');
  let field = document.getElementById('inputCategory');
  if(field.value=="Joe%"){
  icon.src="<?php echo URLROOT?>/public/img/american_joe_speedrun_logo.png";
  }
  else{
  icon.src="<?php echo URLROOT?>/public/img/american_dad_speedrun_logo.png";
  }

}
function setsrc(src){
document.getElementById('iframe').src = 'https://youtube.com/embed/' + src.substring(17) +'?autoplay=0';
}
</script>
