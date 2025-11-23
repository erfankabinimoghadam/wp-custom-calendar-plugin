<?php
// Hook to create a custom admin menu page
add_action('admin_menu', function() {
    // Add a top-level menu page in WordPress admin
    add_menu_page(
        'PLUGIN_CALENDAR_TITLE',        // Page title
        'MENU_TITLE',                   // Menu title
        'manage_options',               // Capability required
        'PLUGIN_CALENDAR_SLUG',         // Menu slug
        'plugin_calendar_admin_page',   // Callback function
        'dashicons-calendar-alt',       // Dashicon
        30                              // Position
    );
});

// Admin page callback
function plugin_calendar_admin_page() {
    // Get existing events from WordPress options table
    $events = get_option('PLUGIN_CALENDAR_OPTION_KEY', []);

    // Handle form submission
    if (isset($_POST['action'])) {

        // Security checks: user permissions and nonce
        if (!current_user_can('manage_options') || !check_admin_referer('PLUGIN_CALENDAR_NONCE')) {
            echo '<div class="notice notice-error">Security check failed.</div>';
            return;
        }

        $action = $_POST['action'];

        // Add new event
        if ($action === 'add_event') {
            $new_event = [
                'id' => time(),  // Use timestamp as unique ID
                'name' => sanitize_text_field($_POST['event_name']),
                'start' => sanitize_text_field($_POST['event_start']),
                'end' => sanitize_text_field($_POST['event_end']),
                'description' => wp_kses_post($_POST['event_description']),
                'subscription_link' => esc_url_raw($_POST['subscription_link']),
            ];
            $events[] = $new_event;                       // Add event to array
            update_option('PLUGIN_CALENDAR_OPTION_KEY', $events); // Save updated events
            echo '<div class="notice notice-success">Event added.</div>';
        }

        // Delete event
        if ($action === 'delete_event') {
            $id = intval($_POST['event_id']); 
            $events = array_values(array_filter($events, function($e) use ($id) {
                return $e['id'] !== $id;  // Keep all events except the one to delete
            }));
            update_option('PLUGIN_CALENDAR_OPTION_KEY', $events);
            echo '<div class="notice notice-success">Event deleted.</div>';
        }

        // Edit event
        if ($action === 'edit_event') {
            $id = intval($_POST['event_id']);
            foreach ($events as &$e) {
                if ($e['id'] === $id) {
                    $e['name'] = sanitize_text_field($_POST['event_name']);
                    $e['start'] = sanitize_text_field($_POST['event_start']);
                    $e['end'] = sanitize_text_field($_POST['event_end']);
                    $e['description'] = wp_kses_post($_POST['event_description']);
                    $e['subscription_link'] = esc_url_raw($_POST['subscription_link']);
                }
            }
            unset($e); // break reference
            update_option('PLUGIN_CALENDAR_OPTION_KEY', $events);
            echo '<div class="notice notice-success">Event updated.</div>';
        }
    }

    // Check if we are editing a specific event
    $edit_event = null;
    if (isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit']);
        foreach ($events as $e) {
            if ($e['id'] === $edit_id) {
                $edit_event = $e;
                break;
            }
        }
    }

    // Enqueue datepicker scripts for date inputs
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');

    ?>
    <!-- Admin HTML Form -->
    <div class="wrap">
        <!-- Admin page header -->
        <h1>PLUGIN_CALENDAR_PAGE_TITLE</h1>

        <!-- Event form -->
        <form method="post">
            <?php wp_nonce_field('PLUGIN_CALENDAR_NONCE'); ?>
            <input type="hidden" name="action" value="<?php echo $edit_event ? 'edit_event' : 'add_event'; ?>">
            <?php if ($edit_event): ?>
                <input type="hidden" name="event_id" value="<?php echo esc_attr($edit_event['id']); ?>">
            <?php endif; ?>

            <table class="form-table">
                <!-- Event Name -->
                <tr>
                    <th><label for="event_name">Event Name</label></th>
                    <td><input id="event_name" name="event_name" type="text" required style="width:50%;" value="<?php echo $edit_event ? esc_attr($edit_event['name']) : ''; ?>"></td>
                </tr>

                <!-- Start Date -->
                <tr>
                    <th><label for="event_start">Start Date</label></th>
                    <td><input id="event_start" name="event_start" type="text" required autocomplete="off" value="<?php echo $edit_event ? esc_attr($edit_event['start']) : ''; ?>"></td>
                </tr>

                <!-- End Date -->
                <tr>
                    <th><label for="event_end">End Date</label></th>
                    <td><input id="event_end" name="event_end" type="text" required autocomplete="off" value="<?php echo $edit_event ? esc_attr($edit_event['end']) : ''; ?>"></td>
                </tr>

                <!-- Description -->
                <tr>
                    <th><label for="event_description">Description</label></th>
                    <td>
                        <?php
                        $content = $edit_event ? $edit_event['description'] : '';
                        $editor_id = 'event_description';
                        $settings = [
                            'textarea_name' => 'event_description',
                            'textarea_rows' => 10,
                            'media_buttons' => true, 
                            'teeny' => false,        
                            'quicktags' => true,
                        ];
                        wp_editor($content, $editor_id, $settings);
                        ?>
                    </td>
                </tr>

                <!-- Subscription Link -->
                <tr>
                    <th><label for="subscription_link">Subscription Link</label></th>
                    <td><input id="subscription_link" name="subscription_link" type="url" style="width:50%;" value="<?php echo $edit_event ? esc_attr($edit_event['subscription_link'] ?? '') : ''; ?>"></td>
                </tr>
            </table>

            <!-- Submit buttons -->
            <input type="submit" class="button button-primary" value="<?php echo $edit_event ? 'Update Event' : 'Add Event'; ?>">
            <?php if ($edit_event): ?>
                <a href="<?php echo admin_url('admin.php?page=PLUGIN_CALENDAR_SLUG'); ?>" class="button">Cancel</a>
            <?php endif; ?>
        </form>

        <!-- Existing Events Table -->
        <h2>Existing Events</h2>
        <?php if ($events): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Description</th>
                        <th>Subscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $e): ?>
                        <tr>
                            <td><?php echo esc_html($e['name']); ?></td>
                            <td><?php echo esc_html($e['start']); ?></td>
                            <td><?php echo esc_html($e['end']); ?></td>
                            <td><?php echo esc_html($e['description']); ?></td>
                            <td>
                                <?php if (!empty($e['subscription_link'])): ?>
                                    <a href="<?php echo esc_url($e['subscription_link']); ?>" target="_blank">Link</a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Edit link -->
                                <a href="<?php echo admin_url('admin.php?page=PLUGIN_CALENDAR_SLUG&edit=' . $e['id']); ?>">Edit</a> |
                                <!-- Delete form -->
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this event?');">
                                    <?php wp_nonce_field('PLUGIN_CALENDAR_NONCE'); ?>
                                    <input type="hidden" name="action" value="delete_event">
                                    <input type="hidden" name="event_id" value="<?php echo esc_attr($e['id']); ?>">
                                    <button type="submit" class="button-link" style="border:none; background:none; padding:0; color:#0073aa; cursor:pointer;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
    </div>

    <!-- Datepicker JS -->
    <script>
        jQuery(function($){
            $('#event_start, #event_end').datepicker({ dateFormat: 'yy-mm-dd' });
        });
    </script>
<?php
}
