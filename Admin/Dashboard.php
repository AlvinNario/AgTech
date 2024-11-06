<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: ../Login/Login.php");
    exit(); // Stop further execution
}

// Include your database connection
require_once '../Connection/connection.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of rows per page
$offset = ($page - 1) * $limit;

$activityLogsQuery = $conn->prepare(
    "SELECT activity_log.admin_id, user.first_name, user.last_name, activity_log.activity, activity_log.created_at 
    FROM activity_log 
    JOIN user ON activity_log.admin_id = user.user_id 
    ORDER BY activity_log.created_at DESC 
    LIMIT ? OFFSET ?"
);
$activityLogsQuery->bind_param("ii", $limit, $offset);
$activityLogsQuery->execute();
$activityLogsResult = $activityLogsQuery->get_result();

// Total rows calculation for pagination
$totalLogsQuery = $conn->query("SELECT COUNT(*) AS total FROM activity_log");
$totalLogs = $totalLogsQuery->fetch_assoc()['total'];
$totalPages = ceil($totalLogs / $limit);

// Prepare and execute query to count users
$userCountQuery = $conn->prepare("SELECT COUNT(*) as user_count FROM User");
$userCountQuery->execute();
$userCountResult = $userCountQuery->get_result();
if ($userCountRow = $userCountResult->fetch_assoc()) {
    $userCount = $userCountRow['user_count'];
} else {
    $userCount = 0;
}

// Prepare and execute query to count products
$productCountQuery = $conn->prepare("SELECT COUNT(*) as product_count FROM Products");
$productCountQuery->execute();
$productCountResult = $productCountQuery->get_result();
if ($productCountRow = $productCountResult->fetch_assoc()) {
    $productCount = $productCountRow['product_count'];
} else {
    $productCount = 0;
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
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
        <h1>Dashboard</h1>
        <div class="card-container">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-count"><?php echo htmlspecialchars($userCount); ?></h5>
                    <i class="bi bi-person"></i>
                </div>
                <div class="card-title">
                    <h5>Users</h5>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-count"><?php echo htmlspecialchars($productCount); ?></h5>
                    <i class="bi bi-cart4"></i>
                </div>
                <div class="card-title">
                    <h5>Products</h5>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-count">0</h5>
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="card-title">
                    <h5>Approval</h5>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-count">0</h5>
                    <i class="bi bi-list-stars"></i>
                </div>
                <div class="card-title">
                    <h5>Reviews</h5>
                </div>
            </div>
        </div>

        <h2>Activity Logs</h2>
        <div class="activity-logs">
            <?php if ($activityLogsResult->num_rows > 0): ?>
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Admin ID</th>
                            <th>Admin Name</th>
                            <th>Activity</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $activityLogsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['admin_id']); ?></td>
                                <td><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($log['activity']); ?></td>
                                <td><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($log['created_at']))); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
            <?php else: ?>
                <p>No activity logs found.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function toggleIcon() {
  var icon = document.querySelector('.toggle-icon');
  icon.classList.toggle('rotated');
}

document.addEventListener('DOMContentLoaded', () => {
  const body = document.querySelector('body'),
        sidebar = body.querySelector('nav'),
        toggle = body.querySelector(".toggle"),
        navLinks = body.querySelectorAll(".nav-link"),
        toggleMenu = document.querySelector('.toggle-menu'),
        submenuItems = document.querySelectorAll('.submenu');

  // Apply saved sidebar state on page load
  if (localStorage.getItem('sidebarState') === 'open') {
    sidebar.classList.remove('close');
  } else {
    sidebar.classList.add('close');
  }

  // Toggle sidebar and save state in localStorage
  toggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");
    if (sidebar.classList.contains('close')) {
      localStorage.setItem('sidebarState', 'closed');
    } else {
      localStorage.setItem('sidebarState', 'open');
    }
  });

  // Save sidebar state before navigating to another page
  navLinks.forEach(link => {
    link.addEventListener("click", () => {
      if (sidebar.classList.contains('close')) {
        localStorage.setItem('sidebarState', 'closed');
      } else {
        localStorage.setItem('sidebarState', 'open');
      }
    });
  });

  // Handle toggle menu for management section
  if (toggleMenu) {
    toggleMenu.addEventListener('click', (e) => {
      e.preventDefault();
      submenuItems.forEach(item => {
        item.classList.toggle('show');
      });
    });
  }
});

// Active link highlight
document.addEventListener("DOMContentLoaded", function() {
    const currentLocation = window.location.pathname; // Get the current page URL
    const navLinks = document.querySelectorAll('.menu-links .nav-link');

    navLinks.forEach(link => {
        if (link.firstElementChild.href === window.location.href) {
            link.classList.add('active'); // Add 'active' class to the current page
        }
    });
});
  </script>

  <script>
    // Logout confirmation with CSRF token check
document.getElementById('logout-link').addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Are you sure you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../Login/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' })
            }).then(response => response.json()).then(data => {
                if (data.status === 'logged out') {
                    window.location.href = '../Login/Login.php';
                } else {
                    Swal.fire('Logout failed', data.message, 'error');
                }
            });
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
