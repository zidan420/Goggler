$(document).ready(function() {
    $('#sitemapForm').on('submit', function(e) {
        e.preventDefault(); // Prevent page reload

        var sitemapUrl = $('#sitemapInput').val().trim();
        if (sitemapUrl) {
            // Simulate submission (no real indexing here)
            console.log('Sitemap submitted:', sitemapUrl); // For debugging
            $('#submissionMessage').fadeIn(500); // Show confirmation
            $('#sitemapInput').val(''); // Clear input
        }
    });
});