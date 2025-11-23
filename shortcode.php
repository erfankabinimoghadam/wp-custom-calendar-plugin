<?php
// Shortcode to render the calendar on frontend
add_shortcode('plugin_calendar', function($atts) {
    // Default attributes
    $atts = shortcode_atts([
        'event_type' => 'All',                  // Filter: All / Courses / Events
        'calendar_title' => '',                 // Calendar heading
        'course_type' => 'all',                 // Course filter: all / advanced-only / on-campus-only
        'hide_manual_events' => 'no',           // Hide manually added events
    ], $atts, 'plugin_calendar');

    // Map attributes to variables
    $event_type = $atts['event_type'];
    $course_type = $atts['course_type'];
    $hide_manual = $atts['hide_manual_events'] === 'yes';

    // Get all events (manual + auto) from helper function
    $events = get_plugin_calendar_events();

    // Filter by event type
    if ($event_type === 'Events') {
        $events = array_filter($events, function($e) {
            return !empty($e['from_parent_SPECIFIC']); // Placeholder for auto events
        });
    } elseif ($event_type === 'Courses') {
        $events = array_filter($events, function($e) {
            return empty($e['from_parent_SPECIFIC']); // Exclude auto event type
        });

        // Course-specific filters
        if ($course_type === 'advanced-only') {
            $events = array_filter($events, function($e) {
                return isset($e['auto']) && $e['auto'] && (int)($e['parent_id'] ?? 0) === PARENT_ID_ADVANCED; // Placeholder
            });
        } elseif ($course_type === 'on-campus-only') {
            $events = array_filter($events, function($e) {
                return isset($e['auto']) && $e['auto'] && (int)($e['parent_id'] ?? 0) === PARENT_ID_ON_CAMPUS; // Placeholder
            });
        }

        // Optionally hide manual events
        if ($hide_manual) {
            $events = array_filter($events, function($e) {
                return isset($e['auto']) && $e['auto'];
            });
        }
    } else {
        // General filter for "All"
        $events = array_filter($events, function($e) use ($course_type, $hide_manual) {
            if (!empty($e['from_parent_SPECIFIC'])) return true;
            if ($course_type === 'advanced-only' && (!isset($e['auto']) || !$e['auto'] || (int)($e['parent_id'] ?? 0) !== PARENT_ID_ADVANCED)) return false;
            if ($course_type === 'on-campus-only' && (!isset($e['auto']) || !$e['auto'] || (int)($e['parent_id'] ?? 0) !== PARENT_ID_ON_CAMPUS)) return false;
            if ($hide_manual && (!isset($e['auto']) || !$e['auto'])) return false;
            return true;
        });
    }

    // Reindex array
    $events = array_values($events);

    // Prepare events for FullCalendar JS
    $fc_events = array_map(function($e) {
        return [
            'title' => html_entity_decode($e['name'] ?? ''),
            'start' => $e['start'] ?? '',
            'end' => date('Y-m-d', strtotime(($e['end'] ?? '') . ' +1 day')), // FullCalendar end date is exclusive
            'description' => $e['description'] ?? '',
            'subscription_link' => $e['subscription_link'] ?? '',
            'auto' => $e['auto'] ?? false,
            'permalink' => $e['permalink'] ?? '',
            'from_parent_SPECIFIC' => $e['from_parent_SPECIFIC'] ?? false,
        ];
    }, $events);

    // Start output buffering
    ob_start(); 
    ?>
    <!-- Calendar Section -->
    <section class="section">
        <div class="container">
            <div class="row">
                <!-- Left vertical line placeholder -->
                <div class="v-line-left-light"></div>

                <div class="col-12 px-4 pt-4 px-md-5 pt-md-5">
                    <div class="row p-3 p-md-5 justify-content-start">
                        <!-- Calendar heading -->
                        <div class="col-12 col-lg-4 mb-5 pb-md-4">
                            <div class="eyebrow-dark"></div>
                            <div class="heading-dark"><?php echo esc_html($atts['calendar_title']); ?></div>
                        </div>

                        <!-- Calendar wrapper -->
                        <div class="col-12 pt-4 pb-5 mb-md-5">
                            <div class="calendar-wrapper">
                                <?php if ($event_type === 'All'): ?>
                                    <!-- Legend for filtering courses vs events -->
                                    <div id="legend" class="legend d-flex pt-1">
                                        <div class="legend-filter" data-event-type="Courses">COURSES_LEGEND_PLACEHOLDER</div>
                                        <div class="legend-filter" data-event-type="Events">EVENTS_LEGEND_PLACEHOLDER</div>
                                        <div id="reset-button">RESET_LEGEND_PLACEHOLDER</div>
                                    </div>
                                <?php endif; ?>

                                <!-- FullCalendar container -->
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right vertical line placeholder -->
                <div class="v-line-right-light"></div>
            </div>
        </div>
    </section>

    <!-- Pass PHP events to JS -->
    <script>
        const PLUGIN_CALENDAR_EVENTS = <?php echo wp_json_encode($fc_events); ?>;
    </script>
    <?php
    return ob_get_clean(); // Return rendered HTML
});
