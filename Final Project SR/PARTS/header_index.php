<!-- Header -->
<header class="bg-dark py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <?php if ($loggedIn): ?>
                    <span class="text-light">Welcome, <?php echo $username; ?></span>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($loggedIn): ?>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $username; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <?php if ($isAdmin): ?>
                                <!-- UI for Admin -->
                                <li><a class="dropdown-item" href="ADMIN/view_requests.php">View Requests</a></li>
                                <li><a class="dropdown-item" href="ADMIN/admin_page_settings.php">Admin Page Settings</a></li>
                            <?php else: ?>
                                <!-- UI for Regular User -->
                                <li><a class="dropdown-item" href="USER/request_event.php">Request Event</a></li>
                                <li><a class="dropdown-item" href="USER/view_my_requests.php">View My Requests</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="EMS/profile.php">Profile</a></li>
                            <form method="post">
                                <li><button type="submit" name="logout" class="dropdown-item btn btn-link">Logout</button></li>
                            </form>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="EMS/login.php" class="btn btn-light">Log In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<!-- End Header -->
