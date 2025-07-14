<?php 
session_start();
require_once 'DATABASE/function.php'; 

$table = isset($_GET['table']) ? $_GET['table'] : '';

$data = [];
if (!empty($table)) {
    $data = $db->select($table);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
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
            padding-top: 32px;
            transition: width 0.3s, background 0.3s;
            z-index: 1050;
        }
        .sidebar .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .sidebar .logo img {
            width: 110px;
            height: 110px;
            margin-bottom: 8px;
        }
        .sidebar .logo h1 {
            color:rgb(227, 168, 96);
            font-size: 2.1rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 32px;
            margin-top: 0;
            text-transform: uppercase;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .sidebar .menu {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-left: 24px;
            margin-top: 0;
        }
        .sidebar .menu a {
            padding: 0;
            text-decoration: none;
            font-size: 22px;
            color: #ffffff;
            background: none;
            border: none;
            outline: none;
            display: block;
            transition: color 0.2s;
            font-weight: 400;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .sidebar .menu a:hover {
            color:rgb(220, 163, 94);
            background: none;
        }
        /* Compact menu for profile modal */
        .sidebar.compact {
            background-color: #23232E;
        }
        .sidebar.compact .logo h1 {
            margin-bottom: 16px;
            color:rgb(232, 168, 89);
            font-weight: 700;
            letter-spacing: 2px;
        }
        .sidebar.compact .menu {
            gap: 16px;
        }
        .sidebar.compact .menu a {
            color: #bdbdbd;
            font-weight: 400;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
            background: #262633; /* Add background to match the body */
            min-height: 100vh;
        }
        /* DataTables override for dark background */
        .dataTables_wrapper,
        .dataTables_wrapper .row,
        .dataTables_wrapper .col-md-12 {
            background: transparent !important;
        }
        .table-dark {
            background-color: #23232E !important;
        }
        .table-dark th, .table-dark td {
            background-color: #23232E !important;
            color: #fff !important;
        }
        .table-dark thead th {
            background-color: #23232E !important;
            color: #fff !important;
            border-bottom: 2px solid #444 !important;
        }
        .table-dark tbody tr {
            background-color:rgb(105, 105, 194) !important;
        }
        .table-dark tbody tr:hover {
            background-color:rgb(116, 116, 146) !important;
        }
        /* DataTables info/filter/length text color */
        .dataTables_info, .dataTables_length, .dataTables_filter, .dataTables_paginate {
            color: #ffffff !important;

        }
        /* Responsive */
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
        @media (max-width: 400px) {
            .sidebar .logo img {
                width: 80px;
                height: 80px;
            }
            .sidebar .logo h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="hamburger" onclick="toggleSidebar()">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="assets/abstract-user-flat-4.png" alt="Logo" style="border-radius: 50%;">
            <h1>URINALYZE</h1>
        </div>
        <div class="menu">
            <a href="table.php?table=users">User List</a>
            <a href="table.php?table=history">History</a>
            <a class="nav-link" href="#" id="profileButton">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.getElementById('profileButton').addEventListener('click', function() {
                // Add compact class to sidebar
                document.getElementById('sidebar').classList.add('compact');
                const email = "<?php  echo $_SESSION['useremail']; ?>";
                const name = "<?php  echo $_SESSION['name']; ?>";
                Swal.fire({
                    title: 'Update Profile',
                    html: `
                    <input type="text" id="name" class="swal2-input" placeholder="Name" value="${name}">
                        <input type="email" id="email" class="swal2-input" placeholder="Email" value="${email}">
                        <input type="password" id="password" class="swal2-input" placeholder="Password">
                    `,
                    confirmButtonText: 'Save',
                    focusConfirm: false,
                    preConfirm: () => {
                        const email = document.getElementById('email').value;
                        const name = document.getElementById('name').value;
                        const password = document.getElementById('password').value;
                        if (!email) {
                            Swal.showValidationMessage('Please enter an email');
                        }
                        return { email: email, password: password, name:name };
                    }
                }).then((result) => {
                    // Remove compact class when modal closes
                    document.getElementById('sidebar').classList.remove('compact');
                    if (result.isConfirmed) {
                        // Send the data to the server via AJAX
                        fetch('nav.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(result.value)
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log(data);
                            if (data.success) {
                                Swal.fire('Saved!', '', 'success');
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Email already exist.', 'error');
                        });
                    }
                });
            });
        </script>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['email']) && isset($input['password'])) {
                $email = htmlspecialchars($input['email']);
                $password = htmlspecialchars($input['password']);
                echo json_encode(['success' => true]);
            } 
        }
        ?>
    </div>
    <div class="content" > 
        <div class="container mt-5"> 
            <div class="row justify-content-center"> 
                <div class="col-md-12"> 
                    <h1 class="text-center"><?php echo ucfirst(htmlspecialchars($table)); ?> List</h1>
                    <?php if (!empty($data)): ?>
                        <?php if ($table == 'history'): ?>
                            <div class="text-right mb-3">
                                <button id="printButton" class="btn btn-primary">Export</button>
                            </div>
                        <?php endif; ?>
                        <table id="dataTable" class="table table-dark"> 
                            <thead>
                                <tr>
                                    <th> </th>
                                    <?php foreach (array_keys($data[0]) as $column): ?>
                                        <?php
                                            // Remove password column in user list
                                            if ($table == 'users' && $column == 'password') continue;
                                        ?>
                                        <?php if ($column != 'updated_at' && $column != 'id' && !($table == 'history' && $column == 'upload')): ?>
                                            <th>
                                                <?php 
                                                    if ($column == 'information' && $table == 'history') {
                                                        echo 'Details';
                                                    } elseif ($column == 'created_at' && $table == 'history') {
                                                        echo 'Timestamp';
                                                    } elseif ($column == 'user_id') {
                                                        echo 'Name';
                                                    } elseif ($column == 'name' && $table == 'history') {
                                                        echo 'Urine ID';
                                                    } elseif ($column == 'comment' && $table == 'history') {
                                                        echo 'Comment';
                                                    } else {
                                                        echo ucfirst(htmlspecialchars($column));
                                                    }
                                                ?>
                                            </th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $idx => $row): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="select_row" value="<?php echo $idx; ?>">
                                        </td>
                                        <?php foreach ($row as $key => $cell): ?>
                                            <?php
                                                // Remove password column in user list
                                                if ($table == 'users' && $key == 'password') continue;
                                            ?>
                                            <?php if ($key != 'updated_at' && $key != 'id' && !($table == 'history' && $key == 'upload')): ?>
                                                <td
                                                    <?php if ($key == 'comment' && $table == 'history'): ?>
                                                        class="editable-comment" data-id="<?php echo htmlspecialchars($row['id']); ?>"
                                                    <?php endif; ?>
                                                    <?php 
                                                        if ($key == 'type') {
                                                            echo $cell == 1 ? 'Admin' : 'User';
                                                        } elseif ($key == 'information' && $table == 'history') {
                                                            $info = json_decode($cell, true);
                                                            $filtered = array_filter($info['predictions'], function($p) { return $p['class'] !== 'strip'; });
                                                            $img = htmlspecialchars($row['upload']);
                                                            $name = htmlspecialchars($row['name']);
                                                            $date = htmlspecialchars($row['created_at']);
                                                            // Remove image from the cell, only show button
                                                            echo '<button class="btn btn-info btn-sm view-info-btn" 
                                                                data-inference="' . htmlspecialchars($info['inference_id']) . '"
                                                                data-predictions=\'' . json_encode(array_values($filtered)) . '\'
                                                                data-image="' . $img . '"
                                                                data-name="' . $name . '"
                                                                data-date="' . $date . '"
                                                                data-userid="' . htmlspecialchars($row['user_id']) . '"
                                                                data-id="' . htmlspecialchars($row['id']) . '"
                                                            >View Details</button>';
                                                        } elseif ($key == 'user_id') {
                                                            $user = $db->select('users', 'name', ['id' => $cell]);
                                                            echo htmlspecialchars($user[0]['name']);
                                                        } elseif ($key == 'name' && $table == 'history') {
                                                            // Show Urine ID (strip name)
                                                            echo htmlspecialchars($cell);
                                                        } elseif ($key == 'upload') {
                                                            // Do not show image in the table cell for history
                                                            if ($table != 'history') {
                                                                echo '<img src="' . htmlspecialchars($cell) . '" alt="Uploaded Image" style="max-width: 100px; max-height: 100px;">';
                                                            }
                                                        } elseif ($key == 'password') {
                                                            // Do not show password column in user list

                                                        } elseif ($key == 'comment' && $table == 'history') {
                                                            echo htmlspecialchars($cell);
                                                        } else {
                                                            echo htmlspecialchars($cell);
                                                        }
                                                    ?>
                                                </td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center">No data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Info Modal for admin history -->
    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="background:#23232E; color:#fff;">
          <div class="modal-header">
            <h5 class="modal-title" id="infoModalLabel">Test Information</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="infoModalBody">
            <!-- Populated by JS -->
          </div>
        </div>
      </div>
    </div>
    <!-- Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="background:#23232E; color:#fff;">
          <div class="modal-header">
            <h5 class="modal-title" id="passwordModalLabel">Password</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="passwordModalBody" style="font-size:1.2rem;">
          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                <?php if ($table == 'history'): ?>
                "order": [[
                    <?php
                        // Find the zero-based index of the "created_at" column (Timestamp)
                        $colIndex = 0;
                        foreach (array_keys($data[0]) as $i => $column) {
                            if ($column == 'updated_at' || $column == 'id' || ($table == 'history' && $column == 'upload')) continue;
                            if ($column == 'created_at') break;
                            $colIndex++;
                        }
                        // Add 1 for the Select column
                        echo ($colIndex + 1);
                    ?>, "desc"
                ]]
                <?php endif; ?>
            });

            // Password overlay handler
            $('#dataTable').on('click', '.show-password', function(e) {
                e.preventDefault();
                var password = $(this).data('password');
                $('#passwordModalBody').text(password);
                $('#passwordModal').modal('show');
            });

            // Info modal handler for admin history
            $('#dataTable').on('click', '.view-info-btn', function() {
                var inferenceId = $(this).data('inference');
                var predictions = $(this).data('predictions');
                var image = $(this).data('image');
                // var name = $(this).data('name'); // REMOVE name
                var date = $(this).data('date');
                var html = '';
                if (image) {
                    html += '<div class="row"><div class="col-md-4 text-center">';
                    html += '<img id="modalTestImage" src="' + image + '" alt="Uploaded Image" style="max-width:180px;max-height:300px;border-radius:8px;cursor:pointer;transition:all 0.2s;">';
                    html += '</div><div class="col-md-8">';
                } else {
                    html += '<div>';
                }
                // REMOVE Name row
                // if (name) html += '<div><strong>Name:</strong> ' + name + '</div>';
                if (date) html += '<div><strong>Date:</strong> ' + date + '</div>';
                if (inferenceId) html += '<div><strong>Inference ID:</strong> ' + inferenceId + '</div>';
                html += '<hr>';
                html += '<div><strong>Predictions:</strong></div>';
                html += '<div class="table-responsive"><table class="table table-bordered table-dark mb-0">';
                html += '<thead><tr><th>Class</th><th>Confidence</th><th>Intensity</th></tr></thead><tbody>';
                predictions.forEach(function(pred) {
                    html += '<tr>' +
                        '<td>' + pred.class + '</td>' +
                        '<td>' + (pred.confidence * 100).toFixed(2) + '%</td>' +
                        '<td>' + pred.intensity + '</td>' +
                        '</tr>';
                });
                html += '</tbody></table></div>';
                html += '</div></div>';
                $('#infoModalBody').html(html);
                $('#infoModal').modal('show');

                // Add click-to-zoom functionality
                $('#modalTestImage').off('click').on('click', function() {
                    var $img = $(this);
                    if (!$img.hasClass('zoomed')) {
                        $img.css({
                            'max-width': '95vw',
                            'max-height': '90vh',
                            'z-index': 9999,
                            'position': 'fixed',
                            'top': '50%',
                            'left': '50%',
                            'transform': 'translate(-50%, -50%) scale(1.5)',
                            'box-shadow': '0 0 20px #000',
                            'background': '#23232E'
                        }).addClass('zoomed');
                    } else {
                        $img.css({
                            'max-width': '180px',
                            'max-height': '300px',
                            'position': '',
                            'top': '',
                            'left': '',
                            'transform': '',
                            'z-index': '',
                            'box-shadow': '',
                            'background': ''
                        }).removeClass('zoomed');
                    }
                });
            });

            // Inline comment editing for admin
            $('#dataTable').on('click', '.editable-comment', function() {
                var $td = $(this);
                if ($td.find('input').length) return; // already editing

                var current = $td.text();
                var id = $td.data('id');
                var $input = $('<input type="text" class="form-control form-control-sm" />')
                    .val(current)
                    .css('min-width', '120px')
                    .on('blur', save)
                    .on('keydown', function(e) { if (e.key === 'Enter') { save(); } });

                $td.empty().append($input);
                $input.focus().select();

                function save() {
                    var newVal = $input.val();
                    $.post('update_comment.php', { id: id, comment: newVal }, function(resp) {
                        $td.text(newVal);
                    }).fail(function() {
                        $td.text(current);
                        alert('Failed to update comment.');
                    });
                }
            });

            <?php if ($table == 'history'): ?>
            $('#printButton').on('click', async function() {
                // Show SweetAlert loading
                if (window.Swal) {
                    Swal.fire({
                        title: 'Exporting...',
                        text: 'Please wait while your PDF is being generated.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                const table = $('#dataTable').DataTable();

                // Use the same column order as the non-admin for consistency
                const headers = ['Name', 'Prediction', 'Image', 'Urine ID', 'Comment', 'Timestamp'];
                const rows = [];

                // Find selected row indexes (checkboxes)
                let selectedIdxs = $('.select_row:checked').map(function(){ return parseInt($(this).val()); }).get();
                let indexes;
                if (selectedIdxs.length > 0) {
                    indexes = selectedIdxs;
                } else {
                    indexes = table.rows({ search: 'applied' }).indexes().toArray();
                }

                await Promise.all(indexes.map(async function(idx) {
                    const $tr = $(table.row(idx).node());
                    const btn = $tr.find('.view-info-btn');
                    if (!btn.length) return;

                    const predictions = btn.data('predictions');
                    const image = btn.data('image');
                    // Get user name from the table cell (first column after checkbox)
                    const name = $tr.find('td').eq(1).text();
                    // Get Urine ID (strip name) from the correct column
                    const urineId = $tr.find('td').eq(
                        <?php
                            // Find the index of the 'name' column in the table (excluding checkbox)
                            $colIndex = 0;
                            foreach (array_keys($data[0]) as $column) {
                                if ($column == 'updated_at' || $column == 'id' || ($table == 'history' && $column == 'upload')) continue;
                                if ($column == 'name') break;
                                $colIndex++;
                            }
                            // Add 1 for the Select column
                            echo ($colIndex + 1);
                        ?>
                    ).text();
                    const date = btn.data('date');
                    // Get comment cell (last cell before timestamp)
                    const comment = $tr.find('td').eq(-2).text();

                    // Prepare image (as base64 or placeholder)
                    let imgData = null;
                    if (image) {
                        try {
                            imgData = await new Promise((resolve, reject) => {
                                const img = new window.Image();
                                img.crossOrigin = '';
                                img.onload = function () {
                                    const canvas = document.createElement('canvas');
                                    canvas.width = img.width;
                                    canvas.height = img.height;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(img, 0, 0);
                                    resolve(canvas.toDataURL('image/jpeg'));
                                };
                                img.onerror = reject;
                                img.src = image;
                            });
                        } catch (e) {
                            imgData = null;
                        }
                    }

                    // Store for sub-table rendering
                    rows.push([name, predictions, imgData, urineId, comment, date]);
                }));

                // Sort rows by timestamp descending (latest first)
                rows.sort(function(a, b) {
                    // a[5] is the timestamp column
                    return new Date(b[5]) - new Date(a[5]);
                });

                // Helper to render a mini-table as aligned plain text inside a cell
                function predictionsToText(preds) {
                    // Calculate max width for each column
                    let maxClass = 5, maxConf = 10, maxInt = 8;
                    preds.forEach(pred => {
                        maxClass = Math.max(maxClass, (pred.class + '').length);
                        maxConf = Math.max(maxConf, ((pred.confidence * 100).toFixed(2) + '%').length);
                        maxInt = Math.max(maxInt, (pred.intensity + '').length);
                    });
                    let text = 
                        'Class'.padEnd(maxClass + 2, ' ') +
                        'Confidence'.padEnd(maxConf + 2, ' ') +
                        'Intensity'.padEnd(maxInt, ' ') + '\n';
                    preds.forEach(pred => {
                        text +=
                            (pred.class + '').padEnd(maxClass + 2, ' ') +
                            ((pred.confidence * 100).toFixed(2) + '%').padEnd(maxConf + 2, ' ') +
                            (pred.intensity + '').padEnd(maxInt, ' ') + '\n';
                    });
                    return text;
                }

                doc.autoTable({
                    head: [headers],
                    body: rows.map(row => [
                        row[0], // Name (user)
                        predictionsToText(row[1]),
                        '', // image placeholder, will be drawn manually
                        row[3], // Urine ID (strip name)
                        row[4], // comment
                        row[5]  // timestamp
                    ]),
                    startY: 20,
                    margin: { left: 4 }, // Move table more to the left (default is 40)
                    didDrawCell: function (data) {
                        // Only draw image if there is image data
                        if (
                            data.column.index === 2 &&
                            rows[data.row.index] &&
                            typeof rows[data.row.index][2] !== 'undefined' &&
                            rows[data.row.index][2]
                        ) {
                            const imgData = rows[data.row.index][2];
                            // Calculate max size to fit cell
                            const cellW = data.cell.width - 4;
                            const cellH = data.cell.height - 4;
                            const maxW = Math.min(14, cellW);
                            const maxH = Math.min(14, cellH);
                            let w = maxW, h = maxH;
                            // Try to preserve aspect ratio (optional, can be omitted for fixed size)
                            // Center image in cell
                            const x = data.cell.x + (data.cell.width - w) / 2;
                            const y = data.cell.y + (data.cell.height - h) / 2;
                            try {
                                doc.addImage(imgData, 'JPEG', x, y, w, h);
                            } catch (e) {
                                doc.setFillColor(200, 200, 200);
                                doc.rect(x, y, w, h, 'F');
                                doc.setTextColor(100, 100, 100);
                                doc.setFontSize(7);
                                doc.text('No Image', x + w / 2, y + h / 2 + 2, { align: 'center' });
                                doc.setTextColor(0, 0, 0);
                            }
                        } else if (data.column.index === 2) {
                            // If no image, draw a blank cell (remove black box)
                            const cellW = data.cell.width - 4;
                            const cellH = data.cell.height - 4;
                            const w = Math.min(14, cellW);
                            const h = Math.min(14, cellH);
                            const x = data.cell.x + (data.cell.width - w) / 2;
                            const y = data.cell.y + (data.cell.height - h) / 2;
                            doc.setFillColor(240, 240, 240);
                            doc.rect(x, y, w, h, 'F');
                            doc.setTextColor(150, 150, 150);
                            doc.setFontSize(7);
                            doc.text('No Image', x + w / 2, y + h / 2 + 2, { align: 'center' });
                            doc.setTextColor(0, 0, 0);
                        }
                    },
                    styles: { fontSize: 7, cellPadding: 1, font: 'courier', fontStyle: 'bold' },
                    columnStyles: {
                        0: { cellWidth: 35 },
                        1: { cellWidth: 60 },
                        2: { cellWidth: 18 },
                        3: { cellWidth: 20 }, // Urine ID column
                        4: { cellWidth: 35 },
                        5: { cellWidth: 35 }
                    }
                });

                doc.save('history_table.pdf');

                // Close SweetAlert loading
                if (window.Swal) {
                    Swal.close();
                }
            });
            <?php endif; ?>
        });

        function toggleSidebar() {
            var sidebar = document.querySelector('.sidebar');
            if (sidebar.style.width === '250px') {
                sidebar.style.width = '0';
            } else {
                sidebar.style.width = '250px';
            }
        }

        document.addEventListener('click', function(event) {
            var sidebar = document.querySelector('.sidebar');
            var hamburger = document.querySelector('.hamburger');
            // Only collapse sidebar on outside click if on mobile
            if (window.innerWidth <= 768 && sidebar.style.width === '250px') {
                if (!sidebar.contains(event.target) && !hamburger.contains(event.target)) {
                    sidebar.style.width = '0';
                }
            }
        });
    </script>
</body>
</html>
<?php 
// Route: /table.php
// Handles: GET for displaying user/history tables, and AJAX POST for profile update
?>
