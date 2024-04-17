<?php
/*
 * Plugin Name: Emloyee Management
 * Description: Custom Employee Management System so please add this shorcode when you show the form [frontend_crud].
 * Version: 1.0
 * Author: Dolphine Web Solution
 * Plugin URI: https://dolphinwebsolution.com/
 * Author URI: https://dolphinwebsolution.com/
 */



// Plugin Activation Hook
register_activation_hook(__FILE__, 'ems_activation');
function ems_activation()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'ems';
    $sql = "DROP TABLE IF EXISTS $table_name;
    CREATE TABLE $table_name(
        id mediumint(11) NOT NULL AUTO_INCREMENT,
             ems_firstname varchar (250) NOT NULL,
             ems_lastname varchar (250) NOT NULL,
             ems_contact int(11) NOT NULL,
             ems_email varchar (250) NOT NULL,
             PRIMARY KEY id(id)
             )$charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


// Define a function to enqueue scripts and styles
function my_plugin_enqueue_scripts()
{
    // Enqueue a style
    wp_enqueue_style('my-plugin-style', plugins_url('employee.css', __FILE__), array(), '1.0', 'all');
}
// Hook the function to the 'wp_enqueue_scripts' action
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_scripts');


// Add This Shortcode Fronted When you want to add [frontend_crud]
add_shortcode('frontend_crud', 'ems_frontend_crud');
function ems_frontend_crud()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ems';
    $msg = '';
    if (@$_REQUEST['action'] == 'submit') {

        $wpdb->insert("$table_name", [
            'ems_firstname' => sanitize_text_field($_REQUEST['ems_firstname']),
            'ems_lastname' => sanitize_text_field($_REQUEST['ems_lastname']),
            'ems_contact' => absint($_REQUEST['ems_contact']),
            'ems_email' => sanitize_email($_REQUEST['ems_email']),
        ]);

        if ($wpdb->insert_id > 0) {
            $msg = "Saved Successfully";
        } else {
            $msg = "Failed to save data";
        }
    }

    if (@$_REQUEST['action'] == 'update-emp' && @$_REQUEST['id']) {

        $id = @$_REQUEST['id'];

        if (@$_REQUEST['ems_firstname'] && @$_REQUEST['ems_lastname'] && @$_REQUEST['ems_contact'] && @$_REQUEST['ems_email']) {
            $update = $wpdb->update(
                "$table_name",
                [
                    'ems_firstname' => sanitize_text_field($_REQUEST['ems_firstname']),
                    'ems_lastname' => sanitize_text_field($_REQUEST['ems_lastname']),
                    'ems_contact' => absint($_REQUEST['ems_contact']),
                    'ems_email' => sanitize_email($_REQUEST['ems_email']),
                ],
                ['id' => $id]
            );

            if ($update) {
                $msg = "Data Updated <a class='msgmsg' href='" . get_page_link(get_the_ID()) . "'>Add Employee</a>";
            }
        }

        $employee = $wpdb->get_row($wpdb->prepare("select * from $table_name where id = %d", $id), ARRAY_A);

        $ems_firstname = $employee['ems_firstname'];
        $ems_lastname = $employee['ems_lastname'];
        $ems_contact = $employee['ems_contact'];
        $ems_email = $employee['ems_email'];
    }

    if (@$_REQUEST['action'] == 'delete-emp' && @$_REQUEST['id']) {

        $id = @$_REQUEST['id'];
        if ($id) {
            $row_exits = $wpdb->get_row($wpdb->prepare("select * from $table_name where id = %d", $id), ARRAY_A);
            if (count($row_exits) > 0) {
                $wpdb->delete("$table_name", array('id' => $id));
            }
        } ?>
        <script>
            location.href = "<?php echo get_the_permalink(); ?>";
        </script>
    <?php
    }

    ?>

    <div class="form_container">

        <h4 class="err_mesaage"><?php echo @$msg; ?></h4>
        <form method="post" id="employee_form">
            <?php
            if ($wpdb->insert_id > 0) {
                $msg = "Saved Successfully";
            ?>
                <script>
                    // Reset the form fields using JavaScript
                    document.getElementById('employee_form').reset();
                </script>
            <?php
            }
            ?>
            <div class="form__main">
                <div class="form__main--inner">
                    <label>First Name</label>
                    <input type="text" name="ems_firstname" value="<?php echo @$ems_firstname; ?>" placeholder="Enter First Name" required>
                </div>
                <div class="form__main--inner">
                    <label>Last Name</label>
                    <input type="text" name="ems_lastname" value="<?php echo @$ems_lastname; ?>" placeholder="Enter Last Name" required>
                </div>
            </div>
            <div class="form__main">
                <div class="form__main--inner">
                    <label>Contact</label>
                    <input type="number" name="ems_contact" value="<?php echo @$ems_contact; ?>" placeholder="Enter Contact Number" required>
                </div>
                <div class="form__main--inner">
                    <label>Email</label>
                    <input type="email" name="ems_email" value="<?php echo @$ems_email; ?>" placeholder="Enter Email" required>
                </div>
            </div>
            <button type="submit" name="action" value="<?php echo (@$_REQUEST['action'] == 'update-emp') ? 'update-emp' : 'submit'; ?>"><?php echo (@$_REQUEST['action'] == 'update-emp') ? 'Update' : 'Submit'; ?></button>
        </form>

    </div>
    <?php
    $employee_list = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    $i = 1;
    if ($employee_list > 0) { ?>

        <section class="employe-management">
            <div class="container">
                <div class="row">
                    <div class="employe-management__main">
                        <table class="employe-management__table">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employee_list as $index =>  $employee) :  ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo $employee['ems_firstname'];  ?></td>
                                        <td><?php echo $employee['ems_lastname']; ?></td>
                                        <td><?php echo $employee['ems_contact'];; ?></td>
                                        <td>
                                            <span><?php echo $employee['ems_email'];; ?></span>
                                            <span class="icon">
                                                <a href="?action=update-emp&id=<?php echo $employee['id']; ?>">Update</a>
                                                <a href="?action=delete-emp&id=<?php echo $employee['id']; ?>" onclick="return confirm('Are you sure to remove this record?')">Delete</a>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
<?php }
}

?>