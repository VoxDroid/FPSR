<style>
.sticky-top {
    position: fixed;
    top: 0;
    z-index: 10000; /* Ensure it's above other content */
    width: 100%;
    margin-bottom: 10px;
}
body {
    transition: padding-top 0.5s ease; /* Adjust duration and timing function as needed */
}
.active .nav-link {
            color: #ffffff; /* Active link text color */
            background-color: #343a40; /* Active link background color */
            /* Add any additional styles for active state */
        }
        .custom-input {
    background-color: #273447; /* Background color of the search box */
    color: #fff; /* Text color of the search box */
    border-color: #273447; /* Border color of the search box */
    font-size: 16px; /* Font size of the text */
    padding: 10px; /* Padding inside the input */
    transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease; /* Smooth transition for better user experience */
}

.custom-input::placeholder {
    color: #aab2bd; /* Placeholder text color */
}

.custom-input:focus {
    background-color: #273447; /* Maintain background color on focus */
    border-color: #273447; /* Maintain border color on focus */
    box-shadow: 0 0 0 0.2rem rgba(39, 52, 71, 0.25); /* Optional: Add a subtle shadow on focus */
    color:#aab2bd;
}

.custom-btn {
    background-color: #273447; /* Background color of the search button */
    border-color: #273447; /* Border color of the search button */
    color: #fff; /* Text color of the search button */
    transition: background-color 0.3s ease, border-color 0.3s ease; /* Smooth transition for better user experience */
}

.custom-btn:hover {
    background-color: #1e2e3e; /* Darken the background color on hover */
    border-color: #1e2e3e; /* Darken the border color on hover */
}
.custom-dropdown-toggle {
    background-color: #273447; /* Background color of the dropdown toggle button */
    color: #fff; /* Text color of the dropdown toggle button */
    border-color: #273447; /* Border color of the dropdown toggle button */
    padding: 10px;
}

.custom-dropdown-toggle:hover {
    background-color: #1e2e3e; /* Darken the background color on hover */
    color: #aab2bd;
    border-color: #1e2e3e; /* Darken the border color on hover */
}

.custom-dropdown-menu {
    background-color: #273447; /* Background color of the dropdown menu */
    border-color: #273447; /* Border color of the dropdown menu */
}

.custom-dropdown-item {
    color: #fff; /* Text color of dropdown items */
}

.custom-dropdown-item:hover,
.custom-dropdown-item:focus {
    background-color: #1e2e3e; /* Darken the background color on hover/focus */
    color: #fff; /* Text color on hover/focus */
}

</style>

<script>
window.addEventListener('load', function() {
    // Function to adjust body padding and modal positions based on header height
    function adjustLayout() {
        var headerHeight = document.querySelector('.sticky-top').offsetHeight;
        
        // Adjust body padding to accommodate sticky header
        document.body.style.paddingTop = headerHeight + 'px';

        // Get all modals
        var modals = document.querySelectorAll('.modal');
        
        // Loop through each modal to adjust its position
        modals.forEach(function(modal) {
            var modalTop = headerHeight;
            modal.style.top = modalTop + 'px';
        });
    }

    // Call adjustLayout initially and on window resize
    adjustLayout();
    window.addEventListener('resize', adjustLayout);
});
</script>
<?php
$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page === 'index.php') {
    echo <<<HTML
    <header class="py-3 sticky-top" style="background-color: #161c27;">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a class="navbar-brand" href="#">
                        <img src="ASSETS/IMG/EMS_icons/logo.png" width="125" height="50" alt="EMS" class="me-3">
                    </a>
HTML;

    if ($loggedIn) {
        echo <<<HTML
        <span class="text-light ms-3">Welcome back, <strong>{$username}!</strong></span>
HTML;
    }

    echo <<<HTML
                </div>
                <form action="EMS/search_event.php" method="GET" class="flex-grow-1 mx-3">
                <div class="input-group">
                    <input type="text" name="q" class="form-control custom-input" placeholder="Search events...">
                    <button type="submit" class="btn btn-primary custom-btn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                </form>

HTML;

    if ($loggedIn) {
        echo <<<HTML
        <div class="dropdown">
    <button class="btn btn-light dropdown-toggle custom-dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-fill"></i>
        <span class="d-none d-lg-inline-block ms-2">{$username}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userDropdown">
HTML;

        if ($isAdmin) {
            echo <<<HTML
            <li><a class="dropdown-item custom-dropdown-item" href="ADMIN/administrator.php">Admin Page Settings</a></li>
HTML;
        } else {
            echo <<<HTML
            <li><a class="dropdown-item custom-dropdown-item" href="USER/request_event.php">Request Event</a></li>
            <li><a class="dropdown-item custom-dropdown-item" href="USER/view_my_requests.php">View My Requests</a></li>
HTML;
        }

        echo <<<HTML
            <li><a class="dropdown-item custom-dropdown-item" href="EMS/profile.php">Profile</a></li>
        <li><hr class="dropdown-divider"></li>
        <form method="post">
            <li><button type="submit" name="logout" class="dropdown-item btn btn-link text-danger custom-dropdown-item">Logout</button></li>
        </form>
    </ul>
</div>
HTML;
    } else {
        echo <<<HTML
        <a href="EMS/login.php" class="btn btn-light">Login</a>
HTML;
    }

    echo <<<HTML
            </div>
            <div class="row mt-3">
            <div class="col">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="index.php"><i class="fas fa-calendar-alt"></i> Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="EMS/calendar.php"><i class="fas fa-calendar-day"></i> Calendar</a>
                    </li>
                </ul>
            </div>
        </div>
        </div>
    </header>
HTML;
} else {
    // Include scripts for other pages (assuming they are in a subdirectory)
    echo <<<HTML
    <header class="py-3 sticky-top" style="background-color: #161c27;">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a class="navbar-brand" href="../index.php">
                        <img src="../ASSETS/IMG/EMS_icons/logo.png" width="125" height="50" alt="EMS" class="me-3">
                    </a>
HTML;

    if ($loggedIn) {
        echo <<<HTML
        <span class="text-light ms-3">Welcome back, <strong>{$username}!</strong></span>
HTML;
    }

    echo <<<HTML
                </div>
                <form action="../EMS/search_event.php" method="GET" class="flex-grow-1 mx-3">
                <div class="input-group">
                    <input type="text" name="q" class="form-control custom-input" placeholder="Search events...">
                    <button type="submit" class="btn btn-primary custom-btn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                </form>
HTML;

    if ($loggedIn) {
        echo <<<HTML
        <div class="dropdown">
    <button class="btn btn-light dropdown-toggle custom-dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-fill"></i>
        <span class="d-none d-lg-inline-block ms-2">{$username}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userDropdown">
HTML;

        if ($isAdmin) {
            echo <<<HTML
            <li><a class="dropdown-item custom-dropdown-item" href="../ADMIN/administrator.php">Admin Page Settings</a></li>
HTML;
        } else {
            echo <<<HTML
            <li><a class="dropdown-item custom-dropdown-item" href="../USER/request_event.php">Request Event</a></li>
            <li><a class="dropdown-item custom-dropdown-item" href="../USER/view_my_requests.php">View My Requests</a></li>
HTML;
        }

        echo <<<HTML
            <li><a class="dropdown-item custom-dropdown-item" href="../EMS/profile.php">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <form method="post">
                        <li><button type="submit" name="logout_EMS" class="dropdown-item btn btn-link text-danger custom-dropdown-item">Logout</button></li>
                    </form>
                </ul>
            </div>
            HTML;
                } else {
                    echo <<<HTML
                    <a href="../EMS/login.php" class="btn btn-light">Login</a>
            HTML;
                }
            
                echo <<<HTML
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <ul class="nav">
                                    <li class="nav-item">
                                        <a class="nav-link text-light" href="../index.php">
                                            <i class="fas fa-calendar-alt"></i> Events
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-light" href="../EMS/calendar.php">
                                            <i class="fas fa-calendar-day"></i> Calendar
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </header>
            HTML;
            }
            ?>
            
