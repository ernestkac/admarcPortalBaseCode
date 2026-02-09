<?php


    require_once 'classes\authentication.php';

    for ($i=1; $i < 7; $i++) { 
      
    $AuthenticationObject = new Authentication($i);
    $AuthenticationObject->hashPasswords();
    }

?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <link
    rel="stylesheet"
    href="assets\css\profilemodal.css"
  />  
    
<style>

</style>


</head>
<body>
    <button class="btn logout" onclick="openprofile()">
          <i class="fas fa-chart-line"></i>
          View Stats
        </button>

<div id="profileModal" class="profile-modal">
  <div class="profile-modal-content">

    <span class="close-btn" onclick="closeprofile()">
        <i class="fas fa-xmark" ></i>
    </span>

    <!-- Header -->
    <div class="header">
      <div class="avatar-wrapper">
        <img src='\assets\img\admarc logo.png' class="avatar" alt="User" />
        <i class="fa fa-pencil avatar-pencil"></i>
      </div>
      <div class="last-login">
        last login 01/15/25 10:00 PM
      </div>
    </div>

    <!-- Name with edit -->
    <div >
      <div>
        <span id="user-name" class="user-name">Ernest kachingwe</span>
        <i class="fa fa-pencil name-pencil"></i>
      </div>
      
      <div class="name-edit" style="display:none;">
        <input type="text" id="name-input" class="name-input">
        <button id="name-ok" class="name-ok">OK</button>
      </div>
    </div>

    <!-- Other rows -->
    <div class="row"><span>765114</span></div>
    <div class="bottom-profile-modal">
        <div class="row"><label>Access code</label><span>WNMKNM</span></div>
        <div class="row"><label>Status</label><div class="switch">Active</div></div>

        <!-- Access Level -->
        <div class="row">
        <label>Access level</label>
        <div>
            <span>User</span><br>
            <small class="toggle-btn" data-target="access-level-dropdown">Change Access level</small>
            <select id="access-level-dropdown" class="dropdown">
            <option>Admin</option>
            <option>finance</option>
            <option>HR</option>
            <option>User</option>
            </select>
        </div>
        </div>

        <!-- Division -->
        <div class="row">
        <label>Division</label>
        <div>
            <span>Blantyre</span><br>
            <small class="toggle-btn" data-target="division-dropdown">Change Division</small>
            <select id="division-dropdown" class="dropdown">
            <option value="201" >NGABU</option>
            <option value="202" >BLANTYRE</option>
            <option value="203">Luchenza</option>
            <option value="205">Liwonde</option>
            <option value="207">Balaka</option>
            <option value="308">Lilongwe</option>
            <option value="310">Mponera</option>
            <option value="311">Kasungu</option>
            <option value="312">SALIMA</option>
            <option value="413">Mzuzu</option>
            <option value="415">Karonga</option>
            </select>
        </div>
        </div>
    </div>

  </div>
</div>

  <script src="assets\js\profilemodal.js"></script>
<script>

</script>

</body>
</html>
