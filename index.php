<?php
session_start();
if (isset($_SESSION["emp_mst_login_id"])) {
    header("Location: dashboard.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evantage QR Asset Scan</title>
    <link rel="stylesheet" href="login.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	 
</head>
<body>
  <div class="container">
        <!-- Left Side Image -->
        <div class="left-side">
            <img src="3D-QR-Code.png" alt="Login Illustration">
        </div>

        <!-- Right Side Login Form -->
        <div class="right-side">
            <div class="login-box">
			 <img src="logo.png" alt="Evantage Logo" class="logo">
                <h2>Evantage QR Asset Scan</h2>
               
                <form id="loginForm">
                    <input type="text" id="username" placeholder="Username" required>
                    <div class="password-field">
                        <input type="password" id="password" placeholder="Password" required>
                       
						 <span class="toggle-password" id="togglePassword">
							<i class="fas fa-eye" style="color: #4a6baf;"></i>
						</span>
                    </div>
                    <select id="site_cd" required>
                        <option value="" disabled selected>Loading Sites...</option>
                    </select>
                    <button type="submit">Login</button>
					
                </form>
            </div>
        </div>
    </div>
	<script>
        $(document).ready(function () {
			
	   // Toggle password visibility
        $("#togglePassword").click(function () {
            let passwordField = $("#password");
            let type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);

            // Change eye icon
           $(this).html(type === "password" 
                ? '<i class="fas fa-eye" style="color: #4a6baf;"></i>' 
                : '<i class="fas fa-eye-slash" style="color: #4a6baf;"></i>');
        });
		
            // Fetch site options dynamically
            $.ajax({
                url: "Api/get_sitecode.php",
                type: "GET",
                dataType: "json",
                success: function (response) {
				//console.log("response____site__cd___",response);
                    let siteDropdown = $("#site_cd");
                    siteDropdown.html('<option value="" disabled selected>Select Site</option>'); // Reset
                    response.data.forEach(site => { 
						siteDropdown.append(`<option value="${site.site_cd}">${site.site_name}</option>`);
					});
					siteDropdown.val(response.data[0].site_cd);
                },
                error: function () {

                    $("#site_cd").html('<option value="" disabled selected>Error Loading Sites</option>');
                }
            });

            // Handle Login Form Submission
            $("#loginForm").submit(function (e) {
                e.preventDefault(); // Prevent default form submission

                let login_id = $("#username").val();
                let password = $("#password").val();
                let site_cd = $("#site_cd").val();

                $.ajax({
                    url: "Api/authenticate_login.php",
                    type: "POST",
                    data: { login_id, password, site_cd },
                    dataType: "json",
                    success: function (response) {
					//console.log("response__login___",response);
                        if (response.status === "SUCCESS") {
                            window.location.href = "dashboard.php"; 
                        } else {
							Swal.fire({
								icon: "error",
								title: "Oops...",
								text: `${response.message}`,
							});
							
                            
                        }
                    },
                    error: function () {
					Swal.fire({
								icon: "error",
								title: "Oops...",
								text: "Login failed. Please try again.",
							});
                       
                    }
                });
            });
        });
    </script>
</body>
</html>
