$(document).ready(function() {
    // Handle form submission
    $('#searchForm').on('submit', function(e) {
        e.preventDefault(); // Prevent page reload

        // Get search query
        var query = $('#searchInput').val();
        $('#queryDisplay').text(query);

        // Animate search wrapper to top
        $('.search-wrapper').addClass('top');
        $('.hero').addClass('active');

        // Show results
        $('.results-container').addClass('active');

        // Optional: Reset animation if you want to "search again"
        setTimeout(function() {
            // Could reset here for demo purposes, but omitted for simplicity
        }, 1000);
    });

    // Keep the pulsing border effect (optional)
    setInterval(function() {
        $('.form-control').css('border-color', '#00FFFF').animate({
            'border-color': '#8A2BE2'
        }, 1000).animate({
            'border-color': '#00FFFF'
        }, 1000);
    }, 2000);
});