<?php
require_once '../Connection/connection.php'; // Include your database connection

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // Default to 'all'

$query = "
    SELECT 
        p.product_id,
        p.product_name, 
        c.category_name, 
        pp.current_price, 
        CONCAT(u.first_name, ' ', u.last_name) AS owner_name,
        CONCAT(u.barangay, ', ', u.municipality, ', ', u.province) AS location
    FROM 
        Products p
    JOIN 
        Category c ON p.category_id = c.category_id
    JOIN 
        UserProducts up ON p.product_id = up.product_id
    JOIN 
        User u ON up.user_id = u.user_id
    JOIN 
        ProductPrices pp ON p.product_id = pp.product_id
";

// Add filter condition to the query
if ($filter === 'active') {
    $query .= " WHERE p.status = 'active'"; // Adjust this condition based on your schema
} elseif ($filter === 'sold') {
    $query .= " WHERE p.status = 'sold'"; // Adjust this condition based on your schema
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

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
    <title>AgTech - Products</title>
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
            margin-top: 20px;
            margin-left: 40px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .product-table {
        width: 97%; /* Full width */
        border-collapse: collapse; /* Merge borders */
        margin-top: 20px; /* Space above the table */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Shadow for a subtle 3D effect */
        margin-left: 40px;
        overflow: hidden;
        }

        .product-table th,
        .product-table td {
        padding: 12px; /* Space inside cells */
        text-align: center; /* Align text to the left */
        border-bottom: 1px solid #ddd; /* Light gray border below each row */
        }

        .product-table th {
        background-color: #2D4A36; /* Green background for header */
        color: white; /* White text color for header */
        font-weight: bold; /* Bold font for header */
        }

        .product-table tr:hover {
        background-color: #f1f1f1; /* Light gray background on hover */
        }

        .product-table tr:nth-child(even) {
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
<section class="home"  aria-hidden="true">
<div class="nav-container">
        <h1>Admin</h1>
        <div class="profile-con">
            <i class="bi bi-person-circle"></i>
        </div>
    </div>
    <div class="main-container">
        <h1>Product List</h1>
        <div class="search-bar">
            <input type="text" placeholder="Search for products...">
            <select id="productFilter" class="form-select">
            <option value="all" <?php if ($filter === 'all') echo 'selected'; ?>>All Products</option>
        <option value="active" <?php if ($filter === 'active') echo 'selected'; ?>>Active</option>
        <option value="sold" <?php if ($filter === 'sold') echo 'selected'; ?>>Sold</option>
            </select>
    </div>
        <div class="table-container">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['product_name'] . "</td>";
                            echo "<td>" . $row['category_name'] . "</td>";
                            echo "<td>" . $row['current_price'] . "</td>";
                            echo "<td>" . $row['owner_name'] . "</td>";
                            echo "<td>" . $row['location'] . "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-sm btn-primary edit-btn' 
                                    data-id='" . $row['product_id'] . "' 
                                    data-name='" . $row['product_name'] . "' 
                                    data-category='" . $row['category_name'] . "' 
                                    data-price='" . $row['current_price'] . "' 
                                    data-owner='" . $row['owner_name'] . "' 
                                    data-location='" . $row['location'] . "'>
                                    <i class='bi bi-pencil-square'></i>
                                    </button> ";
                            echo "<button class='btn btn-sm btn-danger' onclick='deleteProduct(" . $row['product_id'] . ")'><i class='bi bi-trash'></i></button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x"></i></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category</label>
                        <input type="hidden" name="categoryId" id="categoryId">
                        <select class="form-control" id="categoryName" required>
                            <option value="" disabled selected>Select a category</option>
                            <option value="Swine">Swine</option>
                            <option value="Chicken">Chicken</option>
                            <option value="Poultry">Poultry</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="currentPrice" class="form-label">Price</label>
                        <input type="number" class="form-control" id="currentPrice" required>
                    </div>
                    <div class="mb-3">
                        <label for="ownerFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="ownerFirstName" required>
                    </div>
                    <div class="mb-3">
                        <label for="ownerLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="ownerLastName" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" required>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="productId">
                        <button type="submit" class="save-btn">Save changes</button>
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

// Active link highlight
document.addEventListener("DOMContentLoaded", function() {
    const currentLocation = window.location.pathname; // Get the current page URL
    const navLinks = document.querySelectorAll('.menu-links .nav-link .sub-menu');

    navLinks.forEach(link => {
        if (link.firstElementChild.href === window.location.href) {
            link.classList.add('active'); // Add 'active' class to the current page
        }
    });
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
    // Edit button click event
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
        // Get data from button attributes
        const productId = this.getAttribute('data-id');
        const productName = this.getAttribute('data-name');
        const categoryName = this.getAttribute('data-category');
        const currentPrice = this.getAttribute('data-price');
         // Get owner's first and last names from data attributes
         const ownerFullName = this.getAttribute('data-owner'); // Example: "John Doe"
        const [ownerFirstName, ownerLastName] = ownerFullName.split(' '); // Split into first and last names
        // Get location directly from data attributes
        const location = this.getAttribute('data-location'); // Ensure this is correct

        // Set the values in the modal
        document.getElementById('productId').value = productId;
        document.getElementById('productName').value = productName;
        document.getElementById('categoryName').value = categoryId; // You might want to map category names to IDs
        document.getElementById('currentPrice').value = currentPrice;
        document.getElementById('location').value = location;
        document.getElementById('ownerFirstName').value = ownerFirstName; // Set first name in the modal
        document.getElementById('ownerLastName').value = ownerLastName; // Set last name in the modal

         // Show the modal
         const editProductModal = new bootstrap.Modal(document.getElementById('editProductModal'));
        editProductModal.show();

         // Focus the modal
         document.getElementById('editProductModal').focus();

        // Optionally set inert on backdrop if you choose that route
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.setAttribute('inert', 'true');
        }
    });
});

// Listen for modal close event to manage inert attribute
document.getElementById('editProductModal').addEventListener('hidden.bs.modal', function () {
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.removeAttribute('inert');
    }
});

document.getElementById('editProductForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form submission

    // Gather form data
    const productId = document.getElementById('productId').value;
    const productName = document.getElementById('productName').value;
    const selectedCategoryName = document.getElementById('categoryName').value; // Category dropdown value (category name)
    const currentPrice = document.getElementById('currentPrice').value;
    const location = document.getElementById('location').value;
    const ownerFirstName = document.getElementById('ownerFirstName').value; // Update to the new first name field
    const ownerLastName = document.getElementById('ownerLastName').value; // Update to the new last name field

    // Send data to your server using AJAX
    fetch('update_product.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            productId: productId,
            productName: productName,
            categoryId: selectedCategoryName, // Send the category name to fetch category_id
            currentPrice: currentPrice,
            location: location,
            ownerFirstName: ownerFirstName, // Updated to match the new field
            ownerLastName: ownerLastName // Updated to match the new field
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
    Swal.fire('Success!', 'Product updated successfully!', 'success').then(() => {
        // Use window.location to ensure we are referencing the global location object
        window.location.reload();
    });
}else {
            Swal.fire('Error!', 'There was an error updating the product.', 'error');
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        Swal.fire('Error!', 'There was an error updating the product.', 'error');
    });
});

document.getElementById('productFilter').addEventListener('change', function() {
    const selectedFilter = this.value;
    const currentUrl = window.location.href.split('?')[0]; // Remove any existing query string
    window.location.href = `${currentUrl}?filter=${selectedFilter}`; // Redirect with the selected filter
});

// Track initial values to detect changes
let initialFormState = {};

// Function to save the initial state of the form
function saveInitialFormState() {
    initialFormState = {
        productName: document.getElementById('productName').value,
        categoryName: document.getElementById('categoryName').value,
        currentPrice: document.getElementById('currentPrice').value,
        ownerFirstName: document.getElementById('ownerFirstName').value,
        ownerLastName: document.getElementById('ownerLastName').value,
        location: document.getElementById('location').value
    };
}

// Function to check if the form has unsaved changes
function hasUnsavedChanges() {
    return (
        document.getElementById('productName').value !== initialFormState.productName ||
        document.getElementById('categoryName').value !== initialFormState.categoryName ||
        document.getElementById('currentPrice').value !== initialFormState.currentPrice ||
        document.getElementById('ownerFirstName').value !== initialFormState.ownerFirstName ||
        document.getElementById('ownerLastName').value !== initialFormState.ownerLastName ||
        document.getElementById('location').value !== initialFormState.location
    );
}

// Save the initial form state when the modal is opened
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        saveInitialFormState(); // Save initial state when the edit modal is opened
    });
});

// Add event listener to prevent the modal from closing if there are unsaved changes
document.getElementById('editProductModal').addEventListener('hide.bs.modal', function(event) {
    if (hasUnsavedChanges()) {
        event.preventDefault(); // Prevent the modal from hiding

        // Show confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "You have unsaved changes. Do you want to discard them or keep editing?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Discard',
            cancelButtonText: 'Keep Editing',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove the event listener to allow the modal to close
                const modalElement = document.getElementById('editProductModal');
                modalElement.removeEventListener('hide.bs.modal', arguments.callee);

                // Close the modal after removing the listener
                const editProductModal = bootstrap.Modal.getInstance(modalElement);
                editProductModal.hide();
            }
            // If "Keep Editing" is clicked, do nothing and keep the modal open
        });
    }
});

function deleteProduct(productId) {
    // Show a confirmation dialog
    Swal.fire({
        title: 'Are you sure?',
        text: "This action will permanently delete the product and all related data.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading message while the request is processed
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the product.',
                onBeforeOpen: () => {
                    Swal.showLoading();
                }
            });

            // If confirmed, send AJAX request to delete the product
            fetch('deleteProduct.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ productId: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message and reload the page
                    Swal.fire('Deleted!', 'The product has been deleted.', 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    // Show error message
                    const errorMessage = data.error || 'There was a problem deleting the product.';
                    Swal.fire('Error!', errorMessage, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'There was a problem with the request.', 'error');
            });
        }
    });
}


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
