$(document).ready(function() {
    // Initially show only the active item
    $('.carousel-item.active').fadeIn();

    // Lock carousel during animation
    var isAnimating = false;

    // Show the next item when the next arrow is clicked
    $('.carousel-control-next').click(function() {
        if (isAnimating) return;
        isAnimating = true;

        var $currentItem = $('#approvedEventsCarousel .carousel-item.active');
        var $nextItem = $currentItem.next('.carousel-item');
        if ($nextItem.length === 0) {
            $nextItem = $('#approvedEventsCarousel .carousel-item').first();
        }

        // Disable next button during animation
        $('.carousel-control-next').addClass('disabled');

        $currentItem.fadeOut(function() {
            $currentItem.removeClass('active');
            $nextItem.fadeIn(function() {
                $nextItem.addClass('active');
                isAnimating = false;
                updateCarouselControls();
            });
        });
    });

    // Show the previous item when the previous arrow is clicked
    $('.carousel-control-prev').click(function() {
        if (isAnimating) return;
        isAnimating = true;

        var $currentItem = $('#approvedEventsCarousel .carousel-item.active');
        var $prevItem = $currentItem.prev('.carousel-item');
        if ($prevItem.length === 0) {
            $prevItem = $('#approvedEventsCarousel .carousel-item').last();
        }

        // Disable previous button during animation
        $('.carousel-control-prev').addClass('disabled');

        $currentItem.fadeOut(function() {
            $currentItem.removeClass('active');
            $prevItem.fadeIn(function() {
                $prevItem.addClass('active');
                isAnimating = false;
                updateCarouselControls();
            });
        });
    });

    // Function to update carousel control button states
    function updateCarouselControls() {
        var $activeItem = $('#approvedEventsCarousel .carousel-item.active');
        var $nextControl = $('.carousel-control-next');
        var $prevControl = $('.carousel-control-prev');

        // Enable/disable next button
        if ($activeItem.is(':last-child')) {
            $nextControl.addClass('disabled');
        } else {
            $nextControl.removeClass('disabled');
        }

        // Enable/disable previous button
        if ($activeItem.is(':first-child')) {
            $prevControl.addClass('disabled');
        } else {
            $prevControl.removeClass('disabled');
        }

        // Update carousel indicators
        var activeIndex = $activeItem.index();
        $('.carousel-indicators li').removeClass('active');
        $('.carousel-indicators li').eq(activeIndex).addClass('active');
    }

    // Automatically slide the carousel
    setInterval(function() {
        if (!isAnimating) {
            $('.carousel-control-next').click();
        }
    }, 5000); // Adjust the interval as needed
});