<?php
session_start();

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Assuming you already have a connection to the database
require_once '../Connection/connection.php';

$product_data = [];
if (isset($_GET['approval_type']) && $_GET['approval_type'] === 'products') {
    $query = "SELECT pr.product_name, pr.product_price, pr.product_category, pr.product_description, pr.images, pr.location, pr.product_review_id, u.first_name, u.last_name, pr.status
              FROM product_review pr
              JOIN user u ON pr.user_id = u.user_id
              ORDER BY pr.created_at DESC";  // Sorting products by created_at in descending order
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Decode the images JSON into an array
            $images = json_decode($row['images'], true); // Decode as associative array

            // Check if there are images and get the first one
            $firstImage = !empty($images) ? $images[0] : 'default.jpg'; // Fallback to a default image if none

            $row['first_image'] = $firstImage;  // Store the first image separately
            $product_data[] = $row;
        }
    }
    echo json_encode($product_data);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgTech - Approval</title>
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
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .search-bar input[type="text"] {
            width: 300px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-bar select {
            margin-left: 10px;
            padding: 10px;
            background-color: #2D4A36;
            color: #fff;
            border: none;
            border-radius: 4px;
        }

        .search-bar select option {
            background-color: #2D4A36;
            color: #fff;
        }

        .table-container {
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #fff;
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
        <h1>Approval</h1>
        <div class="search-bar">
            <input type="text" placeholder="Search...">
            <select id="approval-dropdown">
                <option value="">Select Approval Type</option>
                <option value="products">Products</option>
                <option value="registration">Registration</option>
            </select>
        </div>
        <div class="table-container">
            <table id="approval-table">
                <thead>
                    <tr id="approval-table-headers">
                        <!-- Table headers will be dynamically populated based on selection -->
                    </tr>
                </thead>
                <tbody id="approval-table-body">
                    <!-- Table rows will be dynamically populated based on selection -->
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal Structure -->
<div id="product-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Product Details</h2>
        <div id="modal-body" class="modal-body">
            <!-- Detailed product info will be injected here -->
        </div>
    </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleMenu = document.querySelector('.toggle-menu');
    const submenuItems = document.querySelectorAll('.submenu');

    toggleMenu.addEventListener('click', (e) => {
        e.preventDefault();
        submenuItems.forEach(item => {
            item.classList.toggle('show');
        });
    });

    const dropdown = document.getElementById('approval-dropdown');
    const tableHeaders = document.getElementById('approval-table-headers');
    const tableBody = document.getElementById('approval-table-body');

    dropdown.addEventListener('change', function () {
        const selectedOption = this.value;

        // Clear existing table headers and rows
        tableHeaders.innerHTML = '';
        tableBody.innerHTML = '';

        if (selectedOption === 'products') {
            tableHeaders.innerHTML = `
                <th>Product Image</th>
                <th>Product Name</th>
                <th>Type</th>
                <th>Price</th>
                <th>Description</th>
                <th>Owner</th>
                <th>Location</th>
                <th>Action</th>
            `;

            fetch('approval.php?approval_type=products')
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        const imagePath = `../product_images/${item.first_image}`;

                        // Determine button or status based on approval status
                        let actionHtml;
                        if (item.status === 'Approved') {
                            actionHtml = '<span>Approved</span>';
                        } else {
                            actionHtml = `
                                <button class="view-btn" data-tooltip="View"><i class="bi bi-eye"></i></button>
                                <button class="approve-btn" data-tooltip="Approve" data-product-review-id="${item.product_review_id}"><i class="bi bi-check"></i></button>
                                <button class="deny-btn" data-tooltip="Deny"><i class="bi bi-x"></i></button>
                            `;
                        }

                        const row = `
                            <tr>
                                <td><img src="${imagePath}" alt="Product Image" class="product_image"></td>
                                <td>${item.product_name}</td>
                                <td>${item.product_category}</td>
                                <td>${item.product_price}</td>
                                <td>${item.product_description}</td>
                                <td>${item.first_name} ${item.last_name}</td>
                                <td>${item.location}</td>
                                <td>${actionHtml}</td>
                            </tr>
                        `;
                        tableBody.insertAdjacentHTML('beforeend', row);
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        } else if (selectedOption === 'registration') {
            tableHeaders.innerHTML = `
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Created</th>
                <th>Status</th>
                <th>Action</th>
            `;
        } else {
            tableHeaders.innerHTML = '';
        }
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target && event.target.matches('.approve-btn')) {
            const button = event.target;
            const productReviewId = button.getAttribute('data-product-review-id');
            const row = button.closest('tr'); // Get the closest row

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to approve this product!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'green',
                cancelButtonColor: 'lightgreen',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('approve_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            product_review_id: productReviewId
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update the action cell in the table row
                            const actionCell = row.querySelector('td:last-child');
                            actionCell.innerHTML = 'Approved'; // Change the button to "Approved"
                            Swal.fire('Approved!', 'The product has been approved.', 'success');
                        } else {
                            Swal.fire('Error!', data.message || 'There was a problem approving the product.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error); // Log the entire error object
                        Swal.fire('Error!', 'There was a problem processing your request.', 'error');
                    });
                }
            });
        }
    });
});


const body = document.querySelector('body'),
    sidebar = body.querySelector('nav'),
    toggle = body.querySelector(".toggle"),
    searchBtn = body.querySelector(".search-box"),
    modeSwitch = body.querySelector(".toggle-switch"),
    modeText = body.querySelector(".mode-text");

toggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");
});

searchBtn.addEventListener("click", () => {
    sidebar.classList.remove("close");
});
</script>

<script>
    // Logout confirmation
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
                // Destroy the session and redirect to the login page
                fetch('logout.php', { method: 'POST', credentials: 'same-origin' }).then(() => {
                    window.location.href = '../Login/Login.php';
                });
            }
});
});
  </script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('product-modal');
    const modalBody = document.getElementById('modal-body');
    const span = document.querySelector('.modal .close');

    document.addEventListener('click', function (e) {
        if (e.target && (e.target.matches('.view-btn') || e.target.matches('.bi.bi-eye'))) {
            // Ensure we're clicking on the right row
            const row = e.target.closest('tr');
            const productName = row.children[1].textContent;
            
            // Fetch detailed data for the clicked product
            fetch('approval.php?approval_type=products')
                .then(response => response.json())
                .then(data => {
                    const product = data.find(item => item.product_name === productName);
                    
                    if (product) {
                        const images = JSON.parse(product.images);
                        let imagesHtml = images.map(img => `<img src="../product_images/${img}" alt="Product Image">`).join('');
                        
                        modalBody.innerHTML = `
                            <div class="product-modal-container">
                                <div class="product-images">${imagesHtml}</div>
                                <div class="product-details">
                                    <p class="product_name"><strong>Product Name:</strong> ${product.product_name}</p>
                                    <p><strong>Price:</strong> ${product.product_price}</p>
                                    <p><strong>Category:</strong> ${product.product_category}</p>
                                    <p><strong>Description:</strong> ${product.product_description}</p>
                                    <p><strong>Owner:</strong> ${product.first_name} ${product.last_name}</p>
                                    <p><strong>Location:</strong> ${product.location}</p>
                                </div>
                            </div>
                        `;
                        modal.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error fetching detailed data:', error));
        }
    });

    // Close the modal when the user clicks on <span> (x)
    span.onclick = function () {
        modal.style.display = 'none';
    }

    // Close the modal when the user clicks anywhere outside of the modal
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
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