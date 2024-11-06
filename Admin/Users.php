<?php

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgTech - Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../images/logo.ico" type="image/x-icon">
    <style>
        .main-container {
            padding: 20px;
        }

        .search-bar {
            margin-bottom: 20px;
            margin-top: 20px;
            margin-left:40px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .user-table {
        width: 97%; /* Full width */
        border-collapse: collapse; /* Merge borders */
        margin-top: 20px; /* Space above the table */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Shadow for a subtle 3D effect */
        margin-left: 40px;
        overflow: hidden;
        }

        .user-table th,
        .user-table td {
        padding: 12px; /* Space inside cells */
        text-align: center; /* Align text to the left */
        border-bottom: 1px solid #ddd; /* Light gray border below each row */
        }

        .user-table th {
        background-color: #2D4A36; /* Green background for header */
        color: white; /* White text color for header */
        font-weight: bold; /* Bold font for header */
        }

        .user-table tr:hover {
        background-color: #f1f1f1; /* Light gray background on hover */
        }

        .user-table tr:nth-child(even) {
        background-color: #f9f9f9; /* Slightly darker background for even rows */
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
        <h1>Users</h1>
        <div class="search-bar">
            <input type="text" placeholder="Search for users...">
            <button class="adduser-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class='bx bx-user-plus'></i>Add user
</button>
        </div>
        <div class="table-container">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>User Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
// Database connection
require_once '../Connection/connection.php';

// Fetch only admin users from the database
$query = "SELECT user_id, first_name, last_name, phone_number, email, user_type, profile FROM user WHERE user_type = 'admin'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Determine profile image URL
        $profileImage = !empty($row['profile_image']) ? "../images/" . htmlspecialchars($row['profile']) : "../images/default-profile.png";
        
        echo "<tr>";
        echo "<td><img src='" . $profileImage . "' alt='Profile Picture' class='user-profile'></td>";
        echo "<td>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_type']) . "</td>";
        echo "<td>Active</td>"; // Example status
        echo "<td>";
        echo "<button class='btn btn-sm btn-primary' onclick='editUser(" . $row['user_id'] . ")'><i class='bi bi-pencil-square'></i></button> ";
        echo "<button class='btn btn-sm btn-danger' onclick='deleteUser(" . $row['user_id'] . ")'><i class='bi bi-trash'></i></button>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No admin users found</td></tr>";
}

// Close connection
mysqli_close($conn);
?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x"></i></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="userId" name="userId">
                    <div class="mb-3">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                    </div>
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phoneNumber" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" required>
                    </div>
                    <div class="modal-footer">
                    <button type="submit" class="save-btn">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x"></i></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="newFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="newFirstName" name="newFirstName" required>
                    </div>
                    <div class="mb-3">
                        <label for="newLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="newLastName" name="newLastName" required>
                    </div>
                    <div class="mb-3">
                        <label for="newEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="newEmail" name="newEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPhoneNumber" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="newPhoneNumber" name="newPhoneNumber" required>
                    </div>
                    <div class="modal-footer">
                    <button type="submit" class="save-btn">Add User</button>
</div>
                </form>
            </div>
        </div>
    </div>
</div>

</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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

  // Apply saved submenu state on page load
  if (localStorage.getItem('submenuState') === 'open') {
    submenuItems.forEach(item => {
      item.classList.add('show');
    });
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

      // Save submenu state in localStorage
      if (submenuItems[0].classList.contains('show')) {
        localStorage.setItem('submenuState', 'open');
      } else {
        localStorage.setItem('submenuState', 'closed');
      }
    });
  }
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

<script>
function editUser(userId) {
    // Fetch user data via AJAX
    fetch(`get_user.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Populate modal fields
                document.getElementById('userId').value = data.user.user_id;
                document.getElementById('firstName').value = data.user.first_name;
                document.getElementById('lastName').value = data.user.last_name;
                document.getElementById('email').value = data.user.email;
                document.getElementById('password').value = data.user.password;
                document.getElementById('phoneNumber').value = data.user.phone_number;

                // Show the modal
                var editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                editUserModal.show();
            } else {
                Swal.fire('Error!', 'Unable to load user data.', 'error');
            }
        })
        .catch(error => console.error('Error:', error));
}

document.getElementById('editUserForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);
    
    fetch('update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('Success!', 'User details updated successfully.', 'success').then(() => {
                location.reload(); // Reload the page to update the user table
            });
        } else {
            Swal.fire('Error!', 'Unable to update user details.', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
});


function deleteUser(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send an AJAX request to delete the user
            fetch('delete_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })  // Ensure userId is passed correctly
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Deleted!', 'User has been deleted.', 'success').then(() => {
                        window.location.reload(); // Reload the page to update the user table
                    });
                } else {
                    Swal.fire('Error!', 'Unable to delete user.', 'error');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
}

document.getElementById('addUserForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);
    
    fetch('add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('Success!', 'User added successfully.', 'success').then(() => {
                location.reload(); // Reload the page to update the user table
            });
        } else {
            Swal.fire('Error!', 'Unable to add user.', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
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
