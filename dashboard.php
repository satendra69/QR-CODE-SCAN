<?php
session_start();

 if (!isset($_SESSION["emp_mst_login_id"])) {
     header("Location: index.php");
     exit();
	 
 }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset QR Code Tracking</title>
    <link rel="stylesheet" href="dashboard.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://unpkg.com/html5-qrcode"></script>

<style>

 #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            display: none; /* Initially hidden */
        }
        #result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 18px;
        }
        .btn {
            padding: 12px 20px;
            background: #4285f4;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 16px;
        }
        .btn-stop {
            background: #db4437;
        }
.overlay {
    position: absolute;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 550px;
    background: rgba(255, 255, 255, 0.95);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: none;
    text-align: center;
}


#stopBtn {
	display: none;
    position: absolute;
    bottom: 10px; /* Adjust spacing from bottom */
    left: 50%;
    transform: translateX(-50%); /* Center horizontally */
    background-color: red;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
#loadingText {
    font-size: 16px;
    color: #555;
}
    </style>
</head>
<body>
   
	<header>
    <div class="left-section">
        <img src="logo.png" alt="Evantage Logo" class="logo_header">
        
    </div>
    <button id="logout">Logout</button>
</header>

    <!-- Asset Search Form -->
	<div class="container">
        <div class="user-greeting">👋 Hello, <?php echo $_SESSION["emp_mst_name"]; ?></div>
	</div>
	
	
    <div class="search-container">
        <img src="3d-female-hand-holds-rr.png" alt="Evantage Logo" class="logo2">
			
        <h2>Asset QR Code Tracking</h2>
			<p class="boxP">Easily manage and locate your assets by scanning QR codes for instant access to detailed information.</p>
        <div class="input-group">
            <input type="text" placeholder="Enter Asset No" id="assetInput" maxlength="30">
            <button class="scan-btn"  id="startBtn">
           <i class="fa-solid fa-qrcode" id="scanQrIcon"></i>
        </button>
        </div>
		<button class="search-btn" id="searchBtn"><i class="fas fa-search" id="fa-search"></i> Search </button>

		 <!--div id="reader"></div-->
		 
    </div>
	<div id="scannerOverlay" class="overlay">
	 <p id="loadingText">Loading camera...</p>
		<div id="reader"></div>
		<button id="stopBtn" class="btn btn-stop">Stop Scanner</button>
	</div>
   
    
<!-- Popup Modal (Hidden by Default) -->
<div id="popupModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
       
        <div class="modal-body">
           <div class="modal-img">
                <img id="assetImage" src="https://placehold.co/400" alt="Asset Image">
            </div>
          
			 <div class="modal-text" id="modalText">
				<div class="form-group">
					<label for="asset_no" id="label_asset_no">Asset No:</label>
					<input type="text" id="asset_no" disabled>
				</div>
				<div class="form-group">
					<label for="asset_description" id="label_short_desc">Asset Description:</label>
					<input type="text" id="asset_description" readonly>
				</div>
				<div class="form-group">
					<label for="cost_center" id="label_cost_center">Cost Center:</label>
					<input type="text" id="cost_center" readonly>
				</div>
				
			</div>
			<div class="bottom-fields">
				<div class="form-group">
					<label for="work_area" id="label_work_area">Work Area:</label>
					<input type="text" id="work_area" readonly>
				</div>
				<div class="form-group">
					<label for="ast_lvl" id="label_ast_lvl">Level:</label>
					<input type="text" id="ast_lvl" readonly>
				</div>
				<div class="form-group">
					<label for="asset_locn" id="label_asset_locn">Asset Location:</label>
					<input type="text" id="asset_locn" readonly>
				</div>
				<div class="form-group">
					<label for="assigned_to" id="label_assigned_to">Assign To:</label>
					<input type="text" id="assigned_to" readonly>
				</div>
				</div>

        </div>
    </div>
</div>

   
	<script>
	 
    $(document).ready(function () {
	// CustomizeLabel name pass in html code 
	
	function getCustomizeLabel(assetData, columnName) {
		const column = assetData.find(item => item.column_name === columnName);
		return column ? column.customize_header : null;
	}

	// Assuming response.data.ast_mst contains the asset data
	function updateLabels(assetData) {
		const labelsMap = {
			"ast_mst_work_area": "label_work_area",
			"ast_mst_asset_locn": "label_asset_locn",
			"ast_mst_assigned_to": "label_assigned_to",
			"ast_mst_asset_shortdesc" : "label_short_desc",
			"ast_mst_ast_lvl": "label_ast_lvl",
			"ast_mst_cost_center" : "label_cost_center",
			"ast_mst_asset_no" : "label_asset_no",
		};

		Object.entries(labelsMap).forEach(([columnName, labelId]) => {
			const labelText = getCustomizeLabel(assetData, columnName);
			if (labelText) {
				document.getElementById(labelId).textContent = labelText;
			}
		});
	}

		// Call API when document loads
        $.ajax({
            url: "Api/get_asset_from_lebal.php",
            type: "GET",
            dataType: "json",
            success: function (response) {
				console.log("response___",response);
               if (response.status === "SUCCESS" && response.data) {
					let assetData = response.data.ast_mst;
					 updateLabels(assetData);
                   
                } else {
                    console.error("No data found or invalid response.");
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching asset data:", error);
            }
        });
		
		
		let baseUrl = window.location.origin;
		let firstPathSegment = window.location.pathname.split("/")[1];
		let rootUrl = `${baseUrl}/${firstPathSegment}`;
			
		// sacn qr code start 
		let scanner = null;

        function startScanner() {
           const scannerOverlay = document.getElementById("scannerOverlay");
			const resultDiv = document.getElementById("result");
			const startBtn = document.getElementById("startBtn");
			const stopBtn = document.getElementById("stopBtn");
			const reader = document.getElementById("reader");
			const loadingText = document.getElementById("loadingText");

            loadingText.style.display = "block";
			 // Show overlay
			startBtn.style.display = "none"; // Hide start button
			//stopBtn.style.display = "inline-block"; // Show stop button
			document.body.classList.add("disable-clicks");
            //scanner = new Html5Qrcode("reader");
			if (!scanner) {
				scanner = new Html5Qrcode("reader");
				 
			}
			 reader.style.display = "block"; 
			 scannerOverlay.style.display = "block";
            scanner.start(
                { facingMode: "environment" }, // Prefer back camera
                 { fps: 10, qrbox: { width: 300, height: 300 } },
                (decodedText) => {
					let firstLine = decodedText.split("\n")[0];
					if (firstLine) {
						fetchAssetDetails(firstLine);
						 
					}
                    
                    if (navigator.vibrate) navigator.vibrate(200); // Vibrate on scan
                    stopScanner(); // Stop scanner after scan
                },
                (error) => { console.warn(error); } // Handle errors
            ).then(() => {
				  loadingText.style.display = "none";
            
            // Show the stop button after 1 second delay
            setTimeout(() => {
                stopBtn.style.display = "block";
            }, 500);
        }).catch(err => {
            console.error("Scanner error:", err);
        });
        }

        function stopScanner() {
            if (scanner) {
                scanner.stop().then(() => {
                document.getElementById("scannerOverlay").style.display = "none"; // Hide overlay
                document.getElementById("reader").style.display = "none"; // Hide reader div
                document.getElementById("startBtn").style.display = "inline-block"; // Show start button
                document.getElementById("stopBtn").style.display = "none"; 
				document.body.classList.remove("disable-clicks");
				scanner = null;
				
                }).catch(err => console.error("Stop error:", err));
            }
        }

        document.getElementById("startBtn").addEventListener("click", startScanner);
        document.getElementById("stopBtn").addEventListener("click", stopScanner);

        // Fetch Data from PHP API
        function fetchAssetDetails(assetNo) {
			let site_cd = "<?php echo $_SESSION['Site_cd']; ?>";
			
            $.ajax({
                url: "Api/fetch_asset.php",
                type: "POST",
                data: { asset_no: assetNo, site_cd:site_cd,_t: new Date().getTime() },
                dataType: "json",
                success: function (response) {
                    //console.log("api__res___", response);
                    if (response.status === "SUCCESS" && Array.isArray(response.data.AllData) && response.data.AllData.length > 0) {
                        
                        function formatField(mainField, descField) {
                            if (mainField && descField) {
                                return mainField + " : " + descField;
                            } else if (mainField) {
                                return mainField;  
                            } else if (descField) {
                                return descField; 
                            } else {
                                return "";  
                            }
                        }

                        let asset = response.data.AllData[0];
						$("#asset_no").val(formatField(asset.ast_mst_asset_no));

						$("#asset_description").val(formatField(asset.ast_mst_asset_shortdesc)); 
                        $("#cost_center").val(formatField(asset.ast_mst_cost_center, asset.cost_center_desc));
                        $("#work_area").val(formatField(asset.ast_mst_work_area, asset.work_area_desc));
                        $("#assigned_to").val(formatField(asset.ast_mst_assigned_to));
                        $("#ast_lvl").val(formatField(asset.ast_mst_ast_lvl, asset.ast_lvl_desc));
                        $("#asset_locn").val(formatField(asset.ast_mst_asset_locn, asset.ast_loc_desc));

                        // Update image if available
                        if (response.status === "SUCCESS" && Array.isArray(response.data.AllRef) && response.data.AllRef.length > 0) {
						let asset_img = response.data.AllRef[0];
						 
						 let imageUrl = `${rootUrl}/${asset_img.attachment}`;
						 $("#assetImage").attr("src", imageUrl);
						
					}else{
						$("#assetImage").attr("src", "https://placehold.co/400");
					}

                        $("#popupModal").fadeIn(); // Show modal
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Oops No Data Found",
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Error fetching data.",
                    });
                }
            });
        }
		// Search button click to call code
	  $("#popupModal").hide();
	 
        $("#searchBtn").click(function () {
            let assetNo = $("#assetInput").val().trim();
			console.log("btn click___",assetNo);
			
			 if (assetNo === "") {
                swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Asset No cannot be empty!",
                });
                return;
            }
			
           
             let site_cd = "<?php echo $_SESSION['Site_cd']; ?>";
			 $("#searchBtn").prop("disabled", true).css("opacity", "0.5");
			 
            // Fetch Data from PHP API
            $.ajax({
                url: "Api/fetch_asset.php",
                type: "POST",
                data: { asset_no: assetNo, site_cd: site_cd,_t: new Date().getTime() },
				dataType: "json",
                success: function (response) {
				//console.log("api__res___",response);
				if (response.status === "SUCCESS" && Array.isArray(response.data.AllData) && response.data.AllData.length > 0) {
				
				function formatField(mainField, descField) {
					if (mainField && descField) {
						return mainField + " : " + descField;
					} else if (mainField) {
						return mainField;  
					} else if (descField) {
						return descField; 
					} else {
						return "";  
					}
				}

				let asset = response.data.AllData[0];
				    $("#asset_no").prop("readonly", false).val(formatField(asset.ast_mst_asset_no));
				    $("#asset_description").val(formatField(asset.ast_mst_asset_shortdesc)); 
					$("#cost_center").val(formatField(asset.ast_mst_cost_center, asset.cost_center_desc));
					$("#work_area").val(formatField(asset.ast_mst_work_area, asset.work_area_desc));
					$("#assigned_to").val(formatField(asset.ast_mst_assigned_to));
					$("#ast_lvl").val(formatField(asset.ast_mst_ast_lvl, asset.ast_lvl_desc));
					$("#asset_locn").val(formatField(asset.ast_mst_asset_locn, asset.ast_loc_desc));

                    // Update image if available
					 if (response.status === "SUCCESS" && Array.isArray(response.data.AllRef) && response.data.AllRef.length > 0) {
						let asset_img = response.data.AllRef[0];
						 
						 let imageUrl = `${rootUrl}/${asset_img.attachment}`;
						 $("#assetImage").attr("src", imageUrl);
						
					}else{
						$("#assetImage").attr("src", "https://placehold.co/400");
					}

					$("#popupModal").fadeIn(); // Show modal
					
				
				}else {
					Swal.fire({
						icon: "error",
						title: "Oops...",
						text: "Asset No does not exist!",
					}).then(() => {
						// Enable the input field when the user clicks OK
						$("#assetInput").prop("readonly", false).focus();
					});
				}
                },
                error: function () {
                    //console.log("error_____",error);
					 Swal.fire({
						icon: "error",
						title: "Oops...",
						text: "Error fetching data.",
					});
					
                },complete: function () {
				// Re-enable the Search button after request completes
					$("#searchBtn").prop("disabled", false).css("opacity", "1");
				}
            });
			
        });
		
		// Enable search button when user types a new asset number
	

        // Close Modal
        $(".close").click(function () {
            $("#popupModal").fadeOut();
			 $("#assetInput").val('');
			 
			 // remove all temp file
			 fetch('Api/cleanup.php')
			.then(response => response.json())
			.then(data => {
				console.log("delete__img___",data.message); // Show success or error message
			})
			.catch(error => console.error('Error:', error));
        });

      
		// Prevent modal from closing when clicking outside
        $("#popupModal").click(function (event) {
            event.stopPropagation(); // Stop event from bubbling up
        });
    });
	
	document.getElementById("logout").addEventListener("click", function() {
		
             window.location.href = "logout.php"; // Redirect to login page
			 console.log("out session");
        });
</script>
</body>
</html>
