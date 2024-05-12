<header class="bg-dark py-3 sticky-top">
    <!-- Decorative Element - Rainbow Animation -->
    <div class="decorative-element2 mt-2 mb-2"></div>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h2 class="text-light me-4 mb-0">EMS</h2>
                <?php if ($loggedIn): ?>
                    <span class="text-light">Welcome back, <?php echo $username; ?>!</span>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($loggedIn): ?>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $username; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <?php if ($isAdmin): ?>
                                <!-- UI for Admin -->
                                <li><a class="dropdown-item" href="../ADMIN/view_requests.php">View Requests</a></li>
                                <li><a class="dropdown-item" href="../ADMIN/admin_page_settings.php">Admin Page Settings</a></li>
                            <?php else: ?>
                                <!-- UI for Regular User -->
                                <li><a class="dropdown-item" href="../USER/request_event.php">Request Event</a></li>
                                <li><a class="dropdown-item" href="../USER/view_my_requests.php">View My Requests</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="../EMS/profile.php">Profile</a></li>
                            <form method="post">
                                <li><button type="submit" name="logout" class="dropdown-item btn btn-link text-danger">Logout</button></li>
                            </form>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="../EMS/login.php" class="btn btn-light">Log In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Decorative Element - Rainbow Animation -->
    <div class="decorative-element mt-2"></div>
</header>

<style>
.decorative-element {
    width: 100%;
    height: 10px;
    background: linear-gradient(-90deg,
        hsl(0, 100%, 50%),
        hsl(30, 100%, 50%),
        hsl(60, 100%, 50%),
        hsl(90, 100%, 50%),
        hsl(120, 100%, 50%),
        hsl(150, 100%, 50%),
        hsl(180, 100%, 50%),
        hsl(210, 100%, 50%),
        hsl(240, 100%, 50%),
        hsl(270, 100%, 50%),
        hsl(300, 100%, 50%),
        hsl(330, 100%, 50%),
        hsl(360, 100%, 50%)
    );
    background-size: 200% auto; 
    animation: rainbow 8s linear infinite;
}


.decorative-element2 {
    width: 100%;
    height: 10px;
    background: linear-gradient(90deg,
        hsl(0, 100%, 50%),
        hsl(30, 100%, 50%),
        hsl(60, 100%, 50%),
        hsl(90, 100%, 50%),
        hsl(120, 100%, 50%),
        hsl(150, 100%, 50%),
        hsl(180, 100%, 50%),
        hsl(210, 100%, 50%),
        hsl(240, 100%, 50%),
        hsl(270, 100%, 50%),
        hsl(300, 100%, 50%),
        hsl(330, 100%, 50%),
        hsl(360, 100%, 50%)
    );
    background-size: 200% auto; 
    animation: rainbow 8s linear infinite;
}


@keyframes rainbow {
    0% {
        background-position: 0% 50%;
    }
    100% {
        background-position: 200% 50%;
    }
}

</style>
