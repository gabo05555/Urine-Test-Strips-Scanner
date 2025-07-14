<?php 
session_start();
require_once 'DATABASE/function.php'; 

// Add a check to ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch history data for the current user
$data = $db->select('history', '*', ['user_id' => $_SESSION['user_id']]);

// If $data is not an array, set it to an empty array to avoid foreach errors
if (!is_array($data)) {
    $data = [];
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
        .btn {
            transition: background 0.2s, color 0.2s, border 0.2s;
        }
        .btn:hover, .btn:focus {
            background-color: #F1BB65 !important;
            color: #23232E !important;
            border-color: #F1BB65 !important;
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
    </style>
</head>
<body>
<?php require_once 'nav.php'; ?>     
    <div class="container mt-5"> 
        <div class="row">
            <div class="col-md-12 mt-5">
                <button id="downloadPdf" class="btn btn-primary mb-3">Export</button>
                <div class="d-flex justify-content-end mb-2" id="customDateFilterWrapper">
                    <!-- Single Date Filter -->
                    <div class="d-flex align-items-center" id="dateFilter" style="gap:8px;">
                        <label for="filterDate" class="mb-0 mr-1">Date:</label>
                        <input type="date" id="filterDate" class="form-control form-control-sm mr-2" style="max-width: 140px;">
                        <button id="clearDateFilter" class="btn btn-secondary btn-sm">Clear</button>
                    </div>
                </div>
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th> </th>
                            <th>Details</th>
                            <th>ID</th>
                            <th>Comment</th>
                            <th>Timestamp</th>
                        </tr>
                    <tbody>
                    <?php foreach ($data as $idx => $row): 
                        $info = json_decode($row['information'], true);
                        $predictions = $info['predictions'];
                        $filteredPredictions = array_filter($predictions, function($p) { return $p['class'] !== 'strip'; });
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="select_row" value="<?php echo $idx; ?>">
                            </td>
                            <td>
                                <button 
                                    class="btn btn-info btn-sm view-details-btn"
                                    data-predictions='<?php echo json_encode(array_values($filteredPredictions)); ?>'
                                    data-image="<?php echo htmlspecialchars($row['upload']); ?>"
                                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-date="<?php echo htmlspecialchars($row['created_at']); ?>"
                                >View Details</button>
                            </td>
                            <!-- Change the rightmost column from Name to ID -->
                            <td>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </td>
                            <td class="editable-comment" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                <?php echo htmlspecialchars($row['comment']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="background:#23232E; color:#fff;">
          <div class="modal-header">
            <h5 class="modal-title" id="detailsModalLabel">Test Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="detailsModalBody">
            <!-- Populated by JS -->
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
    $(document).ready(function () {
        // Initialize DataTable only once, with custom dom to place filter and length controls in one row
        var table = $('#example').DataTable({
            "order": [[4, "desc"]],
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-2"lf>tip'
        });

        // Move date filter beside search filter
        $('#customDateFilterWrapper').detach().insertAfter('.dataTables_filter');

        // Date filter logic (single date)
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var filterDate = $('#filterDate').val();
                if (!filterDate) return true;
                var rowDate = data[4] ? new Date(data[4]) : null;
                var filterDateObj = new Date(filterDate);

                // Compare only the date part (ignore time)
                if (
                    rowDate &&
                    rowDate.getFullYear() === filterDateObj.getFullYear() &&
                    rowDate.getMonth() === filterDateObj.getMonth() &&
                    rowDate.getDate() === filterDateObj.getDate()
                ) {
                    return true;
                }
                return false;
            }
        );

        $('#filterDate').on('change', function() {
            table.draw();
        });

        $('#clearDateFilter').on('click', function() {
            $('#filterDate').val('');
            table.draw();
        });

        // Modal handler
        $('#example').on('click', '.view-details-btn', function() {
            var predictions = $(this).data('predictions');
            var image = $(this).data('image');
            // var name = $(this).data('name'); // REMOVE name
            var date = $(this).data('date');
            var html = '';
            html += '<div class="row">';
            html += '<div class="col-md-4 text-center">';
            html += '<img id="modalTestImage" src="' + image + '" alt="Uploaded Image" style="max-width:180px;max-height:300px;border-radius:8px;cursor:pointer;transition:all 0.2s;">';
            // Ethical Disclaimer below the picture, left-aligned
            html += '<div class="alert alert-warning mt-3 text-left" style="font-size: 0.98rem; color: #856404; background-color: #fff3cd; border-color: #ffeeba;">' +
                '<h6>Disclaimer:</h6>' +
                'The result provided by this medical tool are for informational/educational purposes only and should not be considered a substitute for professional medical advice, diagnosis, or treatment. This tool is designed to assist healthcare providers.' +
                '</div>';
            html += '</div>';
            html += '<div class="col-md-8">';
            // REMOVE Name row
            // html += '<div><strong>Name:</strong> ' + name + '</div>';
            html += '<div><strong>Date:</strong> ' + date + '</div>';
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
            $('#detailsModalBody').html(html);
            $('#detailsModal').modal('show');

            // Add click-to-zoom functionality
            $('#modalTestImage').off('click').on('click', function() {
                var $img = $(this);
                if (!$img.hasClass('zoomed')) {
                    $img.css({
                        'max-width': '70%',
                        'max-height': '65%',
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

        // Move export handler here so it works after DataTable is ready
        $('#downloadPdf').on('click', async function () {
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
            const table = $('#example').DataTable();

            // Find selected row indexes (checkboxes)
            let selectedIdxs = $('.select_row:checked').map(function(){ return parseInt($(this).val()); }).get();
            let indexes;
            if (selectedIdxs.length > 0) {
                indexes = selectedIdxs;
            } else {
                indexes = table.rows({ search: 'applied' }).indexes().toArray();
            }

            // Set "Image" as the column header, but leave all row cells blank
            const headers = ['ID', 'Prediction', 'Image', 'Comment', 'Timestamp'];
            const rows = [];

            await Promise.all(indexes.map(async function(idx) {
                const $tr = $(table.row(idx).node());
                const btn = $tr.find('.view-details-btn');
                if (!btn.length) return;

                const predictions = btn.data('predictions');
                const name = btn.data('name');
                const image = btn.data('image');
                const comment = $tr.find('td').eq(-2).text();
                const date = btn.data('date');

                // Convert image to base64 if possible, but keep aspect ratio and avoid upscaling
                let imgData = '';
                if (image) {
                    try {
                        imgData = await new Promise((resolve, reject) => {
                            const img = new window.Image();
                            img.crossOrigin = 'Anonymous';
                            img.onload = function () {
                                // Resize image to fit cell, but do not upscale
                                const maxDim = 40; // smaller size for better clarity
                                let w = img.width;
                                let h = img.height;
                                if (w > h && w > maxDim) {
                                    h = Math.round(h * (maxDim / w));
                                    w = maxDim;
                                } else if (h >= w && h > maxDim) {
                                    w = Math.round(w * (maxDim / h));
                                    h = maxDim;
                                }
                                // If image is already smaller, keep original size
                                const canvas = document.createElement('canvas');
                                canvas.width = w;
                                canvas.height = h;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, w, h);
                                resolve(canvas.toDataURL('image/png'));
                            };
                            img.onerror = function() { resolve(''); };
                            img.src = image;
                        });
                    } catch (e) {
                        imgData = '';
                    }
                }

                rows.push([name, predictions, imgData, comment, date]);
            }));

            // Sort rows by timestamp descending (latest first)
            rows.sort(function(a, b) {
                // a[4] is the timestamp column
                return new Date(b[4]) - new Date(a[4]);
            });

            // Helper to render a mini-table as aligned plain text inside a cell
            function predictionsToText(preds) {
                // Convert predictions array to readable string (no [object Object])
                if (!Array.isArray(preds)) return '';
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
                    row[0],
                    predictionsToText(row[1]), // readable text, not object
                    '', // leave cell blank, image will be drawn by didDrawCell
                    row[3],
                    row[4]
                ]),
                startY: 20,
                margin: { left: 8 },
                didDrawCell: function (data) {
                    // Only draw image in body cells, not in the header
                    if (
                        data.section === 'body' &&
                        data.column.index === 2 &&
                        rows[data.row.index] &&
                        rows[data.row.index][2]
                    ) {
                        const imgData = rows[data.row.index][2];
                        if (imgData) {
                            // Set image size, e.g., 12x12mm (fit cell, avoid upscaling)
                            const cellW = data.cell.width - 2;
                            const cellH = data.cell.height - 2;
                            const maxW = Math.min(12, cellW);
                            const maxH = Math.min(12, cellH);
                            let w = maxW, h = maxH;
                            // Center image in cell
                            const x = data.cell.x + (data.cell.width - w) / 2;
                            const y = data.cell.y + (data.cell.height - h) / 2;
                            try {
                                doc.addImage(imgData, 'PNG', x, y, w, h);
                            } catch (e) {
                                // fallback: leave cell blank
                            }
                        }
                    }
                },
                styles: { fontSize: 7, cellPadding: 3, font: 'courier', fontStyle: 'bold' },
                columnStyles: {
                    0: { cellWidth: 35 },
                    1: { cellWidth: 60 },
                    2: { cellWidth: 18 }, // image column width
                    3: { cellWidth: 35 },
                    4: { cellWidth: 40 }
                }
            });

            const userName = '<?php echo htmlspecialchars($_SESSION["user_name"] ?? "User"); ?>';
            const fileName = `${userName}_History.pdf`;
            doc.save(fileName);

            // Close SweetAlert loading
            if (window.Swal) {
                Swal.close();
            }
        });

        // Inline comment editing
        $('#example').on('click', '.editable-comment', function() {
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
    });

    </script>


</body>
</html>