<?php
/*
Plugin Name: Custom Job Search
Description: Adds a custom job search form for searching job titles, locations, and categories.
Version: 1.0
Author: Deepak Jangra
*/

// Enqueue necessary scripts and styles (if needed)
function custom_job_search_enqueue_scripts() {
    // Add custom styles or scripts here if needed
}
add_action('wp_enqueue_scripts', 'custom_job_search_enqueue_scripts');

// Shortcode to display the custom search form
function custom_job_search_form() {
    // Get all categories for the dropdown
    $categories = get_categories( array(
        'taxonomy' => 'category',
        'hide_empty' => false,
    ));

    ob_start(); ?>
    <form role="search" method="get" id="custom-job-search-form" action="<?php echo home_url( '/' ); ?>">
        <div>
            <label for="job-title-search">Job Title</label>
            <input type="text" name="s" id="job-title-search" placeholder="Search job titles" value="<?php echo get_search_query(); ?>" />
        </div>
        <div>
            <label for="job-location-search">Location</label>
            <input type="text" name="location" id="job-location-search" placeholder="Search location" value="<?php echo isset($_GET['location']) ? esc_attr($_GET['location']) : ''; ?>" />
        </div>
        <div>
            <label for="job-category-search">Category</label>
            <select name="category" id="job-category-search">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo $category->term_id; ?>" <?php selected( isset($_GET['category']) ? $_GET['category'] : '', $category->term_id ); ?>>
                        <?php echo $category->name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit">Search Jobs</button>
        </div>
        <input type="hidden" name="post_type" value="job" />
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_job_search', 'custom_job_search_form');

// Modify the main query to include the custom search fields
function custom_job_search_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && $query->is_search() && isset($_GET['post_type']) && $_GET['post_type'] === 'job' ) {

        // Modify the query to search for custom field 'location'
        if ( isset($_GET['location']) && !empty($_GET['location']) ) {
            $meta_query = array(
                array(
                    'key' => 'location',
                    'value' => sanitize_text_field( $_GET['location'] ),
                    'compare' => 'LIKE'
                )
            );
            $query->set('meta_query', $meta_query);
        }

        // Filter by category
        if ( isset($_GET['category']) && !empty($_GET['category']) ) {
            $query->set('cat', absint($_GET['category']));
        }
    }
}
add_action('pre_get_posts', 'custom_job_search_query');
