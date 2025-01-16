<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="icon" type="image/png" sizes="96x96" href="images/icon.png">
	<link rel="stylesheet" href="../assets/style.css">
  <link rel="stylesheet" href="../assets/reports.css">

	<title>Ucheque</title>
	<!-- <script src="calendar.js" type="text/javascript"></script> -->
	
</head>
  <body>
    <div class="sidebar">
        <div class="logo"><img src="../images/logoall-light.png" alt=""></div>
        <ul class="menu">
            <li><a href="index.php"><i class="bx bxs-dashboard"></i><span>dashboard</span></a></li>
            <li><a href="user.php"><i class="bx bxs-group"></i><span>user management</span></a></li>
            <li><a href="itl.php"><i class='bx bxs-doughnut-chart'></i><span>employee ITL</span></a></li>
            <li><a href="dtr.php"><i class='bx bxs-time'></i><span>employee DTR</span></a></li>
            <li><a href="reports.php"><i class='bx bxs-book-alt'></i><span>reports</span></a></li>
            <li class="switch"><a href="/loginas.php"><i class='bx bx-code'></i><span>switch</span></a></li>
        </ul>
      </div>

    <div class="main--content">

      <div class="header--wrapper">
        <div class="header--title">
          <h2>generate Reports</h2>
        </div>
        <div class="user--info">

          <div class="profile-dropdown">
            <div onclick="toggle()" class="profile-dropdown-btn">
              <div class="profile-img"></div>
              <i class="bx bx-chevron-down"></i>
            </div>
        
            <ul class="profile-dropdown-list">

              <li class="profile-dropdown-list-item">
                <a href="profile.php">
                  <i class="bx bxs-user"></i>
                  My Profile
                </a>
              </li>
              <li class="profile-dropdown-list-item">
                <a href="/login.php">
                  <i class="bx bxs-log-out"></i>
                  Log out
                </a>
              </li>

            </ul>

          </div>
          
        </div>
      </div>    
      
      <div class="tabular--wrapper">

        <div class="table-container">
          <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'Cert1')">Cleared Faculty</button>
            <button class="tablinks" onclick="openTab(event, 'Cert2')">Faculty Overload</button>
            <button class="tablinks" onclick="openTab(event, 'Cert_1')">Cleared Faculty (many)</button>
            <button class="tablinks" onclick="openTab(event, 'Cert_2')">Faculty Overload(many)</button>
          </div>
            
            <!-- Tab content -->
          <div id="Cert1" class="tabcontent">
            <img src="../images/Cert_1.PNG">
            <div class="generate">
                <button class="generate-btn">Generate</button>
            </div>
          </div>
          
          <div id="Cert2" class="tabcontent">
            <img src="../images/Cert_2.PNG">
            <div class="generate">
                <button class="generate-btn">Generate</button>
            </div>
          </div>

          <div id="Cert_1" class="tabcontent">
            <img src="../images/Cert1.PNG">
            <div class="generate">
                <button class="generate-btn">Generate</button>
            </div>
          </div>
          
          <div id="Cert_2" class="tabcontent">
            <img src="../images/Cert2.PNG">
            <div class="generate">
                <button class="generate-btn">Generate</button>
            </div>
          </div>
         
           
        </div><!--- tablle-container-->
      </div> <!--- tabular--wrapper-->
    </div><!---main-content-->   
  </body>
   
  <script>
    function openTab(evt, tabName) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
    }
  </script>

  <script>
      
    let profileDropdownList = document.querySelector(".profile-dropdown-list");
    let btn = document.querySelector(".profile-dropdown-btn");

    let classList = profileDropdownList.classList;

    const toggle = () => classList.toggle("active");

    window.addEventListener("click", function (e) {
    if (!btn.contains(e.target)) classList.remove("active");
    });
  </script>

</html>    
    

 