<?php
// Database connection
require_once '../Connection/connection.php';

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if start_date and end_date are set in URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Modify SQL query to filter by date range if start and end dates are provided
$sql = "
SELECT 
    t.transaction_id, 
    t.status, 
    t.timestamp, 
    b.first_name AS buyer_first_name, 
    b.last_name AS buyer_last_name, 
    s.first_name AS seller_first_name, 
    s.last_name AS seller_last_name,
    td.product_id,
    td.quantity,
    td.amount,
    td.AgreedPrice,
    t.timestamp,
    p.product_name,
    pp.current_price
FROM 
    Transaction t
INNER JOIN 
    user b ON t.buyer_id = b.user_id
INNER JOIN 
    user s ON t.seller_id = s.user_id
INNER JOIN 
    Transaction_details td ON t.transaction_id = td.transaction_id
INNER JOIN 
    products p ON td.product_id = p.product_id
INNER JOIN 
    productprices pp ON p.product_id = pp.product_id
WHERE 
    t.status = 'completed'
";

// Apply date range filter
if ($start_date && $end_date) {
    $sql .= " AND t.timestamp BETWEEN '$start_date' AND '$end_date'";
}

$sql .= " ORDER BY t.timestamp DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgTech - Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../images/lg.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <style>
        .main-container {
            padding: 10px;
        }

        .header h1 {
            color: #2D4A36;
        }

        .export-dropdown {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            align-content: flex-end;
            justify-content: flex-end;
            gap: 15px;
        }

        .export-button {
            background-color: #2D4A36;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .report-dropdown select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #2D4A36;
            color: white;
            padding: 10px;
        }


        .table-container {
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #fff;
            margin-top: 20px;

            margin-left:40px;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container th, .table-container td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table-container th {
            background-color: #2D4A36;
            color: #fff;
        }

    </style>
</head>
<body>
<nav class="sidebar close">
    <header>
        <div class="image-text">
            <span class="image">
                <img src="../images/lg.png" alt="">
            </span>
            <div class="text logo-text">
                <span class="name">AgTech</span>
            </div>
        </div>
        <i class='bx bx-chevron-right toggle'></i>
    </header>
    <div class="menu-bar">
        <div class="menu">
            <ul class="menu-links">
                <li class="nav-link">
                    <a href="Dashboard.php">
                        <i class='bx bx-tachometer icon'></i>
                        <span class="text nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="#" class="toggle-menu" onclick="toggleIcon()">
                        <i class='bx bx-cog icon'></i>
                        <span class="text nav-text">Management</span>
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </a>
                </li>
                <li class="submenu">
                    <a href="Products.php" class="sub-menu">
                        <span class="text nav-text">Products</span>
                    </a>
                </li>
                <li class="submenu">
                    <a href="Users.php" class="sub-menu">
                        <span class="text nav-text">Users</span>
                    </a>
    </li>
                <li class="nav-link">
                    <a href="Approval.php">
                        <i class="bi bi-check-circle icon"></i>
                        <span class="text nav-text">Approval</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="Reviews.php">
                        <i class="bi bi-list-stars icon"></i>
                        <span class="text nav-text">Reviews</span>
                    </a>
                </li>
                <li class="nav-link">
                    <a href="Reports.php">
                        <i class="bi bi-file-earmark-bar-graph icon"></i>
                        <span class="text nav-text">Reports</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="bottom-content">
    <li>
        <a href="#" id="logout-link">
            <i class='bx bx-log-out icon'></i>
            <span class="text nav-text">Logout</span>
        </a>
    </li>
</div>
</nav>
<section class="home">
<div class="nav-container">
        <h1>Admin</h1>
        <div class="profile-con">
            <i class="bi bi-person-circle"></i>
        </div>
    </div>
    <div class="main-container">
            <h1>Reports</h1>
            <div class="export-dropdown">
            <input type="text" id="dateRangePicker" class="export-button" placeholder="Select Date Range" />

            <button id="exportCsvButton" class="export-button" onclick="exportData('csv')">Export as CSV</button>
            <button id="exportPdfButton" class="export-button" onclick="exportData('pdf')">Export as PDF</button>

            
            <script>
                // Function to export the currently displayed report table to a CSV file
                document.getElementById('exportCsvButton').addEventListener('click', function() {
                    // Your CSV export logic here
                    exportAsCsv();
                });

                document.getElementById('exportPdfButton').addEventListener('click', function() {
                    // Your PDF export logic here
                    exportAsPdf();
                });
                function exportData(format) {
    const reportType = 'sales'; // Set your report type here
    const url = `../Farmer-Trader/export.php?reportType=${reportType}&format=${format}`;
    
    // Redirect to export.php with the selected format
    window.location.href = url;
}

$(document).ready(function() {
    $('#dateRangePicker').daterangepicker({
        opens: 'left',
        autoUpdateInput: true, // Automatically updates the input field
        ranges: {
            'Today': [moment(), moment()],
            '7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 15 Days': [moment().subtract(14, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
        },
        locale: {
            format: 'YYYY-MM-DD'
        }
    }, function(start, end, label) {
        // When a date range is selected, trigger the redirect automatically
        window.location.href = `?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`;
    });
});
            </script>

                <div class="report-dropdown">
                    <select id="report-type">
                        <option value="default">Select Report Type</option>
                        <option value="sales">Sales</option>
                        <option value="insights">Market Insights</option>
                    </select>
            </div>
        </div>
        <div class="table-container" id="report-table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Buyer Name</th>
                    <th>Seller Name</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Selling Price</th>
                    <th>Agreed Price</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['transaction_id']}</td>
                                 <td>{$row['timestamp']}</td>
                                <td>{$row['buyer_first_name']} {$row['buyer_last_name']}</td>
                                <td>{$row['seller_first_name']} {$row['seller_last_name']}</td>
                                <td>{$row['product_name']}</td>
                                <td>{$row['quantity']}</td>
                                 <td>{$row['current_price']}</td>
                                 <td>{$row['AgreedPrice']}</td>
                                <td>{$row['amount']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No sales found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    <?php $conn->close(); ?>
</section>
<script>
function toggleIcon() {
    var icon = document.querySelector('.toggle-icon');
    icon.classList.toggle('rotated');
}

document.addEventListener('DOMContentLoaded', () => {
    const toggleMenu = document.querySelector('.toggle-menu');
    const submenuItems = document.querySelectorAll('.submenu');

    toggleMenu.addEventListener('click', (e) => {
        e.preventDefault();
        submenuItems.forEach(item => {
            item.classList.toggle('show');
        });
    });

    const reportDropdown = document.getElementById('report-type');
    const reportTableContainer = document.getElementById('report-table-container');

    reportDropdown.addEventListener('change', function() {
        const selectedReport = this.value;
        fetchReport(selectedReport);
    });

    function fetchReport(reportType) {
        // Clear previous table content
        reportTableContainer.innerHTML = '';

        // Initialize table structure and headers
        let tableContent = '';
        let tableHeaders = '';

        switch (reportType) {
            case 'sales':
                tableHeaders = `
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                    </tr>
                `;
                break;
            case 'inventory':
                tableHeaders = `
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                    </tr>
                `;
                break;
            case 'reviews':
                tableHeaders = `
                    <tr>
                        <th>Name</th>
                        <th>Trader</th>
                        <th>Created</th>
                        <th>Rating</th>
                        <th>Action</th>
                    </tr>
                `;
                break;
            default:
                break;
        }

        // Construct table structure with headers
        if (tableHeaders !== '') {
            tableContent = `
                <table>
                    <thead>${tableHeaders}</thead>
                    <tbody>
                        <!-- Initial rows can be added here if needed -->
                    </tbody>
                </table>
            `;
        }

        reportTableContainer.innerHTML = tableContent;
    }
});

const body = document.querySelector('body'),
    sidebar = body.querySelector('nav'),
    toggle = body.querySelector(".toggle"),
    searchBtn = body.querySelector(".search-box"),
    modeSwitch = body.querySelector(".toggle-switch"),
    modeText = document.querySelector(".mode-text");

toggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");
});

searchBtn.addEventListener("click", () => {
    sidebar.classList.remove("close");
});
</script>

<script>
    document.getElementById('logout-link').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default action

    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'green',
        cancelButtonColor: 'lightgreen',
        confirmButtonText: 'Yes, log me out!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../Login/Login.php'; // Redirect to logout page
        }
    });
});
  </script>
</body>
</html>
<?php
// Logout script (logout.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    session_unset();
    session_destroy();
    echo json_encode(['status' => 'logged out']);
    exit;
}
?>
