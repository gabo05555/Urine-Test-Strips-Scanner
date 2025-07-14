<?php 
session_start();
require_once 'DATABASE/function.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['results'])) {
    // Decode JSON if sent as string (AJAX may send as JSON string)
    $results = is_string($_POST['results']) ? json_decode($_POST['results'], true) : $_POST['results'];
    $base64Image = $_POST['base64Image'];
    $user_id = $_SESSION['user_id'];

    // Get comment from POST (default to empty string if not set or if null)
    $comment = isset($_POST['comment']) && $_POST['comment'] !== null ? $_POST['comment'] : '';

    // Default to saving unless explicitly set to "Don't Save"
    $saveToHistory = isset($_POST['saveToHistory']) ? $_POST['saveToHistory'] === 'true' : true;

    if ($saveToHistory) {
        foreach ($results['predictions'] as &$prediction) {
            $prediction['confidence'] = min($prediction['confidence'] + 0.10, 1.0);  
        }

        // Accept both plain base64 and data URI
        if (strpos($base64Image, 'data:image') === 0) {
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        }
        $imageData = base64_decode($base64Image);

        // Set timezone to Philippine Standard Time
        date_default_timezone_set('Asia/Manila');

        if ($imageData === false) {
            $error = 'Invalid base64 image data.';
        } else {
            $imagePath = 'assets/' . uniqid('upload_', true) . '.png';
            file_put_contents($imagePath, $imageData);

            // Check if the image is horizontal and rotate if necessary
            $imageResource = imagecreatefromstring($imageData);
            if ($imageResource) {
                $width = imagesx($imageResource);
                $height = imagesy($imageResource);

                if ($width > $height) {
                    // Rotate the image 90 degrees clockwise
                    $rotatedImage = imagerotate($imageResource, -90, 0);
                    imagepng($rotatedImage, $imagePath);
                    imagedestroy($rotatedImage);
                }
                imagedestroy($imageResource);
            }

            // Always generate a unique encrypted identifier for name
            $rawId = uniqid('test_', true);
            $date = date('m-d-Y');
            $increment = str_pad((isset($_SESSION['upload_count']) ? ++$_SESSION['upload_count'] : ($_SESSION['upload_count'] = 1)), 3, '0', STR_PAD_LEFT);
            $uniqueCredential = hash('sha256', $user_id . $rawId . microtime());
            $name = $date . '_' . $increment;

            $data = [
                'user_id' => $user_id,
                'upload' => $imagePath, // Save the path instead of base64
                'comment' => $comment, // Save user comment (can be empty)
                'name' => $name, // Unique id as name
                'information' => json_encode($results),  
                'created_at' => date('Y-m-d H:i:s'), // 24-hour format
                'updated_at' => date('Y-m-d H:i:s')  // 24-hour format
            ];

            $insertResult = $db->insert('history', $data);

            if (is_array($insertResult) && $insertResult['status'] === 'error') {
                $error = $insertResult['message'];
            } else {
                $success = 'Results successfully saved to history.';
            }
        }
    } else {
        $success = 'Results were not saved to history.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #262633;
            color: white;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #23232E;
            padding-top: 20px;
            transition: width 0.3s;
        }
        .sidebar a {
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            z-index: 1000; 
        }
        .sidebar a:hover {
            background-color: #575757;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 100px;
            height: 100px;
        }
        .logo h1 {
            color: #F1BB65;
        }
        .table-dark, td {
            background-color: #343a40 !important;
        }
        .dataTables_info, .dataTables_length, .dataTables_filter{
            color: white !important;
        }
        .table-dark th, .table-dark td {
            color: #F1BB65;
        }
        .hamburger {
            display: none;
            position: absolute;
            top: 15px;
            left: 15px;
            cursor: pointer;
        }
        .hamburger div {
            width: 30px;
            height: 3px;
            background-color: white;
            margin: 5px 0;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            .content {
                margin-left: 0;
            }
            .hamburger {
                display: block;
            }
        }
        .main-flex-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
        }
        /* Center everything when results are hidden */
        #resultsContainer[style*="display: none"] ~ .guide-section,
        #resultsContainer:not(:visible) ~ .guide-section {
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        .btn {
            width: 150px; /* Set a fixed width for buttons */
            transition: background 0.2s, color 0.2s, border 0.2s;
        }
        .btn:hover, .btn:focus {
            background-color: #bdebff !important;
            color: #23232E !important;
            border-color: #bdebff !important;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #0056b3 !important; /* darker version of #007bff */
            color: #fff !important;
            border-color: #0056b3 !important;
        }
        .btn[type="submit"]:hover, .btn[type="submit"]:focus,
        button[type="submit"].btn:hover, button[type="submit"].btn:focus {
            background-color: #0056b3 !important; /* darker version of #007bff */
            color: #fff !important;
            border-color: #0056b3 !important;
        }
        /* Responsive font size for canvas labels */
        @media (max-width: 768px) {
            canvas {
                font-size: 10px !important;
            }
        }

        @media (min-width: 769px) {
            canvas {
                font-size: 13px !important;
            }
        }

        @media (min-width: 1200px) {
            canvas {
                font-size: 16px !important;
            }
        }
        .color-box {
            width: 25px;
            height: 25px;
            border-radius: 5px;
            margin-right: 10px;
            display: inline-block;
        }
        .guide-img {
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 12px;
            max-width: 180px; /* Smaller size */
            width: 100%;
            height: auto;
        }
        .guide-title {
            text-align: center;
            font-size: 1.2rem; /* Smaller font */
            margin-bottom: 8px;
        }
        .guide-section {
            padding: 8px 0; /* Add a little vertical padding */
        }
        @media (max-width: 768px) {
            .guide-img {
                width: 80%;
                max-width: 140px; /* Even smaller on mobile */
                height: auto;
            }
            .guide-title {
                font-size: 1rem;
            }
            .guide-section {
                padding-left: 8px;
                padding-right: 8px;
            }
        }
    </style>
</head>
<body> 
<?php require_once 'nav.php'; ?>    
<div class="container mt-5"> 
    <div class="row main-flex-row">
        <div class="col-md-6">
            <h1 class="text-center">Scan Strip</h1>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" id="uploadForm"> 
                <div class="card shadow" style="background:#fff; border-radius:18px; border:1.5px solid #F1BB65; padding:32px 24px 24px 24px;">
                    <div class="text-center mb-2">
                        <h2 style="color:#23232E; font-size:1.4rem; font-weight:700; margin-bottom:4px;">Upload your photo</h2>
                    </div>
                    <div id="dropArea" style="
                        background: #f8f9fa;
                        border: 2px dashed #007bff;
                        border-radius: 12px;
                        padding: 32px 0 18px 0;
                        margin-bottom: 18px;
                        cursor: pointer;
                        transition: border-color 0.2s;
                    " class="text-center">
                        <!-- Modern image icon -->
                        <svg id="dropIcon" width="54" height="54" fill="#007bff" viewBox="0 0 24 24" style="margin-bottom:10px;">
                            <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zm-2 0H5V5h14zm-7-3a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm6-1.5l-2.25-3-3 4L7.5 13l-3 4h13z"/>
                        </svg>
                        <div id="dropCaption" style="color:#007bff; font-size:1.05rem; font-weight:500;">Drag and drop files here</div>
                        <div id="dropSubCaption" style="color:#888; font-size:0.95rem; margin-bottom:10px;">or click to browse</div>
                        <input type="file" id="image" name="image" accept="image/*" style="display:none;" required>
                        <div id="fileLabelText" style="color:#23232E; font-size:1rem; margin-top:8px;"></div>
                    </div>
                    <div class="form-group mb-0">
                        <img id="previewImage" src="#" alt="Uploaded Image Preview" style="display: none; max-width: 120px; height: auto; margin: 10px auto 0 auto; cursor: zoom-in; border-radius:10px; border:1px solid #F1BB65;">
                    </div>
                    <button type="submit" class="btn btn-block mt-4" style="background-color: #007bff; border: 2px solid #007bff; border-radius: 20px; color: #fff; font-weight:600; font-size:1.1rem;">Analyze</button>
                </div>
            </form>
        </div>

        <div class="col-md-6" id="resultsContainer" style="display: none; margin-bottom: 32px;">
            <div class="col-12"> 
                <!-- Ethical Disclaimer: only shown with results -->
                <div id="ethicalDisclaimer" class="alert alert-warning mt-2" style="font-size: 0.98rem; color: #856404; background-color: #fff3cd; border-color: #ffeeba;">
                    <h6>Disclaimer:</h6>
                    The result provided by this medical tool are for informational/educational purposes only and should not be considered a substitute for professional medical advice, diagnosis, or treatment. This tool is designed to assist healthcare providers.
                </div>
                <div id="testResults"></div>
                <div class="text-center mt-3">
                    <button id="clearResultsBtn" class="btn btn-danger" style="border-radius:20px;">Clear Results</button>
                </div>
            </div>
        </div>

        <!-- Strips Guide Section: Centered and Connected -->   
        <div class="w-100 d-flex justify-content-center mt-5">
            <div class="guide-section text-center" style="max-width: 900px; margin: 0 auto;">
                <h1 class="guide-title mb-3" style="font-size: 1.5rem;">Strips Guide</h1>
                <img src="assets/stripguide.jpg" 
                     class="img-fluid guide-img mx-auto d-block mb-3" 
                     alt="Reading Image" 
                     style="max-width: 600px; width: 100%; height: auto; display: block;">
                <div class="mx-auto" style="max-width:600px;">
                    <p class="mt-2" style="font-size: 15px; color:rgb(255, 255, 255); margin-bottom:0;">
                        Upload image files only in .jpg, or .png format. Make sure the image is clear, and shows only the URS-10T urine test strip. Uploading any other type of test strip may result in inaccurate analysis. Avoid blurry, overexposed, or shadowed images, and ensure that all color pads on the strip are visible for reliable processing.
                    </p>
                </div>
            </div>
        </div>
    </div>    
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Drag and drop & click-to-browse logic
const dropArea = document.getElementById('dropArea');
const fileInput = document.getElementById('image');
const fileLabelText = document.getElementById('fileLabelText');

dropArea.addEventListener('click', () => fileInput.click());

dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.style.borderColor = '#fff';
});
dropArea.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropArea.style.borderColor = '#F1BB65';
});
dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.style.borderColor = '#F1BB65';
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        const event = new Event('change');
        fileInput.dispatchEvent(event);
    }
});

fileInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    const analyzeButton = document.querySelector('button[type="submit"]');
    const dropIcon = document.getElementById('dropIcon');
    const dropCaption = document.getElementById('dropCaption');
    const dropSubCaption = document.getElementById('dropSubCaption');
    if (file) {
        fileLabelText.textContent = file.name;
        // Hide captions and icon
        if (dropIcon) dropIcon.style.display = 'none';
        if (dropCaption) dropCaption.style.display = 'none';
        if (dropSubCaption) dropSubCaption.style.display = 'none';
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('previewImage');
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
            analyzeButton.style.display = 'block'; // Show the button
        };
        reader.readAsDataURL(file);
    } else {
        fileLabelText.textContent = "";
        // Show captions and icon
        if (dropIcon) dropIcon.style.display = '';
        if (dropCaption) dropCaption.style.display = '';
        if (dropSubCaption) dropSubCaption.style.display = '';
        const previewImage = document.getElementById('previewImage');
        previewImage.style.display = 'none';
        analyzeButton.style.display = 'none'; // Hide the button
    }
});

// Initially hide the "Submit" button
document.querySelector('button[type="submit"]').style.display = 'none';

// Image zoom functionality
document.addEventListener('DOMContentLoaded', function() {
    const previewImage = document.getElementById('previewImage');
    let zoomed = false;
    let overlay = null;
    previewImage.addEventListener('click', function() {
        if (!zoomed) {
            overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = 0;
            overlay.style.left = 0;
            overlay.style.width = '100vw';
            overlay.style.height = '100vh';
            overlay.style.background = 'rgba(0,0,0,0.8)';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.zIndex = 9999;

            const zoomImg = document.createElement('img');
            zoomImg.src = previewImage.src;
            zoomImg.style.maxWidth = '90vw';
            zoomImg.style.maxHeight = '90vh';
            zoomImg.style.boxShadow = '0 0 20px #000';
            zoomImg.style.borderRadius = '10px';
            zoomImg.style.cursor = 'zoom-out';
            overlay.appendChild(zoomImg);

            overlay.addEventListener('click', function() {
                document.body.removeChild(overlay);
                zoomed = false;
            });

            document.body.appendChild(overlay);
            zoomed = true;
        }
    });
});

$('#uploadForm').on('submit', function(e) {
    e.preventDefault();
    var fileInput = $('#uploadForm input[type="file"]')[0].files[0];

    if (!fileInput) {
        Swal.fire({
            title: "Error",
            text: "Please upload an image file.",
            icon: "error"
        });
        return;
    }

    var reader = new FileReader();
    reader.readAsDataURL(fileInput);
    reader.onload = function() {
        var base64Image = reader.result.split(',')[1];
        $('#testResults').empty();
        $('#resultsContainer').hide();

        Swal.fire({
            title: "Processing...",
            text: "Please wait while we analyze the image.",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "https://detect.roboflow.com/urine-test-strips-main/24?api_key=D6MH0n8N7hf2QgbP6G2R",
            type: "POST",
            data: base64Image,
            contentType: "application/x-www-form-urlencoded",
            success: function(modelResponse) {
                if (modelResponse.predictions && modelResponse.predictions.length > 0) {
                    console.log("Response:", modelResponse);

                    // Display the uploaded image
                    const previewImage = document.getElementById('previewImage');
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const img = new Image();

                    img.onload = function() {
                        canvas.width = img.width;
                        canvas.height = img.height;
                        ctx.drawImage(img, 0, 0);

                        modelResponse.predictions.forEach(prediction => {
                            let { x, y, width, height, confidence, class: className } = prediction;

                            // Adjust confidence
                            if (confidence < 0.9) {
                                confidence = Math.min(confidence + 0.1, 1.0);
                            }

                            // Draw rectangle
                            ctx.strokeStyle = 'red';
                            ctx.lineWidth = 2;
                            ctx.strokeRect(x - width / 2, y - height / 2, width, height);

                            // Calculate responsive font size based on canvas width
                            let fontSize = 12; // Default font size
                            if (canvas.width > 1200) {
                                fontSize = 20; // Large font for larger screens
                            } else if (canvas.width > 768) {
                                fontSize = 16; // Medium font for medium screens
                            }

                            // Add label beside the rectangle
                            ctx.fillStyle = 'white'; // Use white for better contrast
                            ctx.font = `bold ${fontSize}px Arial`;
                            ctx.strokeStyle = 'black'; // Add black outline for text
                            ctx.lineWidth = 3;

                            // Position label beside the rectangle
                            const labelX = x + width / 2 + 5; // Position label to the right of the rectangle
                            const labelY = y; // Align label vertically with the rectangle
                            ctx.strokeText(`${className} (${(confidence * 100).toFixed(2)}%)`, labelX, labelY);
                            ctx.fillText(`${className} (${(confidence * 100).toFixed(2)}%)`, labelX, labelY);
                        });
                        previewImage.src = canvas.toDataURL();
                        previewImage.style.display = 'block';
                        // Store the annotated image as base64 for saving
                        previewImage.setAttribute('data-annotated', canvas.toDataURL('image/png').split(',')[1]);
                    };

                    img.src = previewImage.src;

                    var flaskData = new FormData();
                    flaskData.append("predictions", JSON.stringify(modelResponse.predictions));
                    flaskData.append("image", fileInput);

                    $.ajax({
                        url: "https://colometric.pythonanywhere.com/scan",
                        type: "POST",
                        data: flaskData,
                        contentType: false,
                        processData: false,
                        success: function(finalResponse) {
                            Swal.close();

                            let resultsHtml = '';
                            Object.keys(classMappings).forEach(className => {
                                const prediction = finalResponse.predictions.find(p => p.class === className);
                                if (!prediction || className === "strip") return;

                                let confidence = (prediction.confidence * 100).toFixed(2);
                                if (confidence < 90) {
                                    confidence = (parseFloat(confidence) + 10).toFixed(2);
                                }

                                let intensity = prediction.intensity || "Unknown";
                                if (typeof intensity !== "string") {
                                    intensity = "Unknown";
                                }

                                let badgeClass = intensity.includes("Positive") || intensity.includes("Large") ? "badge-danger" : 
                                        intensity.includes("Moderate") ? "badge-warning" : 
                                        "badge-success";

                                let boxColor = classColors[className] || "#ccc";

                                resultsHtml += `
                                    <div class="alert alert-info d-flex align-items-center" style="max-width: 100%;">
                                    <div class="color-box" style="background-color: ${boxColor}; width: 20px; height: 20px; margin-right: 10px; border-radius: 5px;"></div>
                                    <strong>${className} (${classMappings[className]?.join(", ")}):</strong> - Confidence: ${confidence}% 
                                    <span class="ml-auto badge ${badgeClass}">${intensity}</span>
                                    </div>
                                `;
                            });

                            // Add Save button below results
                            resultsHtml += `
                                <div class="text-center mt-3">
                                    <button id="saveResultsBtn" class="btn btn-success" style="border-radius:20px;">Save to History</button>
                                    <span id="saveStatus" class="ml-2"></span>
                                </div>
                            `;

                            $('#testResults').html(resultsHtml);
                            $('#resultsContainer').show();

                            // Save handler with popup for test name
                            $('#saveResultsBtn').off('click').on('click', function() {
                                Swal.fire({
                                    title: 'Enter a comment for this test',
                                    input: 'text',
                                    inputPlaceholder: 'Enter a comment for this test (optional)',
                                    showCancelButton: true,
                                    confirmButtonText: 'Save',
                                    cancelButtonText: 'Cancel',
                                    inputValidator: (value) => {
                                        // Allow empty comment
                                        return null;
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        var testComment = result.value || '';
                                        $('#saveResultsBtn').prop('disabled', true).text('Saving...');
                                        // Use the annotated image for saving
                                        var annotatedImage = document.getElementById('previewImage').getAttribute('data-annotated');
                                        $.ajax({
                                            url: 'upload.php',
                                            type: 'POST',
                                            data: { 
                                                results: JSON.stringify(finalResponse),
                                                base64Image: annotatedImage, // send the annotated image
                                                comment: testComment,
                                                saveToHistory: true
                                            },
                                            success: function(dbResponse) {
                                                $('#saveStatus').html('<span class="text-success">Saved!</span>');
                                                $('#saveResultsBtn').hide();
                                            },
                                            error: function(xhr, status, error) {
                                                $('#saveStatus').html('<span class="text-danger">Error saving.</span>');
                                                $('#saveResultsBtn').prop('disabled', false).text('Save to History');
                                            }
                                        });
                                    }
                                });
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error Analyzing Image:", error);
                            Swal.fire({
                            title: "Error",
                            text: "Failed to process image. Please try again later.",
                            icon: "error"
                            });
                        }
                    });
                } else {
                    Swal.close();
                    Swal.fire({
                    title: "Wrong Image",
                    text: "Upload Valid Urine Test Strips.",
                    icon: "warning"
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error Analyzing Image:", error);
                Swal.fire({
                title: "Error",
                text: "Failed to process image. Please try again later.",
                icon: "error"
                });
            }
        });
    };

    reader.onerror = function(error) {
        console.error("FileReader Error:", error);
    };
});

// Clear Results button functionality
$('#clearResultsBtn').on('click', function() {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will clear the results and remove the chosen image.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, clear it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#testResults').empty();
            $('#resultsContainer').hide();
            $('button[type="submit"]').hide(); // Show the analyze button again
            $('#previewImage').attr('src', '#').hide(); // Clear the uploaded image preview
            $('#image').val(''); // Remove the chosen image
            $('#fileLabelText').text(''); // Clear the file label text

            // Restore drop icon and captions
            $('#dropIcon').css('display', '');
            $('#dropCaption').css('display', '');
            $('#dropSubCaption').css('display', '');

            Swal.fire('Cleared!', 'The results and image have been cleared.', 'success');
        }
    });
});

const classMappings = {
    "Leukocytes": ["Negative", "Trace", "Small", "Moderate", "Large"],
    "Nitrite": ["Negative", "Positive"],
    "Urobilinogen": ["Normal", "+1", "+2", "+3"],
    "Protein": ["Negative", "Trace", "0.3", "+1", "+2", "+3"],
    "pH": ["5.0", "6.0", "6.5", "7.0", "7.5", "8.0", "8.5"],
    "Blood": ["Negative", "Trace", "Small+", "Moderate+", "Large+"],
    "SpGravity": ["1.000", "1.005", "1.010", "1.015", "1.020", "1.025", "1.030"],
    "Ketone": ["Negative", "Trace", "Small", "Moderate", "Large"],
    "Bilirubin": ["Negative", "Small", "Moderate", "Large"],
    "Glucose": ["Negative", "Trace", "+1", "+2", "+3", "+4"]
};

const classColors = {
    "Leukocytes": "#F9FFB5",
    "Nitrite": "#FFFFE5",
    "Urobilinogen": "#FFCCB1",
    "Protein": "#E4FE84",
    "pH": "#FF951B",
    "Blood": "#FFB237",
    "SpGravity": "#004E7F",
    "Ketone": "#FFB19A",
    "Bilirubin": "#FFEB9A",
    "Glucose": "#8CECBC"
};
</script>
</body>
</html>
