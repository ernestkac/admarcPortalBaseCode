<?php
 
  session_start();
  $username= "";

  $alert = "";
  $alert_header;
  $alert_content;

  $login_message = "Enter your creditials.";
  $text_color = "";

  if(isset($_POST['logout'])){

  }

  if (isset($_SESSION['user_type'])) {
  }

  /*if (isset($_POST["useremail"])) {
    $systemObject = new System(get_org_name($_POST["useremail"]));

    if($systemObject->login($_POST["useremail"], $_POST["userpassword"])){
     
      $alert_header = "Alet Message!";
      $alert_content = implode('**', $systemObject->error);
      $alert = '<div class="alert alert-warning" role="alert" style="position: fixed;bottom: 20px;right:20px;left:80px;"> <button type="button" class="close" data-dismiss="alert">×</button>
      <h4 class="alert-heading" >'.$alert_header.'</h4>
      <p class="mb-0"> <pre>'.$alert_content.'.</pre></p>
      </div>';
    }else{

      $text_color = "text-danger";
      $login_message = "either your username or password is incorrect!";
      $username = $_POST['useremail'];

    }
  }*/

  function get_org_name($email){
    $substr = strstr($email, '@');
    return substr($substr, 1, strpos($substr, '.') - 1);
  }
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="asset/css/font-awesome.min.css" type="text/css">
  <link rel="stylesheet" href="asset/css/theme.css" type="text/css">
</head>

<body>
  <nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container"> <a class="navbar-brand" href="#">
        <i class="fa d-inline fa-lg fa-stop-circle"></i>
        <b> AIEM Lodge</b>
      </a> <button class="navbar-toggler navbar-toggler-right border-0" type="button" data-toggle="collapse" data-target="#navbar10">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar10">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"> <a class="nav-link" data-target="#login_modal" data-toggle="modal"><i class="fa fa-lg fa-sign-in" aria-hidden="true"></i> Log in </a> </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="text-center text-white h-100 align-items-center d-flex" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, .75), rgba(0, 0, 0, .75)), url(https://static.pingendo.com/cover-bubble-dark.svg);  background-position: center center, center center;  background-size: cover, cover;  background-repeat: repeat, repeat;">
    <div class="container py-5">
      <div class="row">
        <div class="mx-auto col-lg-8 col-md-10">
          <h1 class="display-3 mb-4">A wonderful serenity</h1>
          <p class="lead mb-5">Has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart. I am alone, and feel the charm of existence.</p> <a href="#" class="btn btn-lg btn-primary mx-1">Take me there</a> <a class="btn btn-lg mx-1 btn-outline-primary" href="#">Let's Go</a>
        </div>
      </div>
    </div>
  </div>
  <div id="login_modal" class="modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <b><h5 class="modal-title <?php echo $text_color;?>"><?php echo $login_message; ?></h5></b> <button type="button" class="close" data-dismiss="modal"> <span>×</span> </button>
          </div>
          <div class="modal-body text-center">
            <div class="form-group row">
              <div class="input-group col-12">
                <div class="input-group-append"><label class="btn btn-primary" ><i class="fa fa-user-o"></i></label></div>
                <input type="email" class="form-control" id="inputmail" required="required" name="useremail" value="<?php echo $username;?>" placeholder="user@aiem.com">
              </div>
            </div>
            <div class="form-group row">
              <div class="input-group col-12">
                  <div class="input-group-append"><label class="btn btn-primary" ><i class="fa fa-key"></i></label></div>
                  <input type="password" class="form-control" id="inputpassword" required="required" name="userpassword" placeholder="Password">
              </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-sign-in" aria-hidden="true"></i> Login </button>
          </div>
        </form>
      </div>
    </div>

<?php echo $alert;?>
  </div>
  <script src="asset/js/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous" style=""></script>
  <script src="asset/js/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="asset/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous" style=""></script>
  <script type="text/javascript">
    $('#login_modal').show();
  </script>

</body>

</html>