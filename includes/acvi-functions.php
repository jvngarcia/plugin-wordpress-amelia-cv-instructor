<?php
/*
 * Add my new menu to the Admin Control Panel
 */
 
// Hook the 'admin_menu' action hook, run the function named 'acvi_admin_menu()'
add_action( 'admin_menu', 'acvi_admin_menu' );

// Add a new top level menu link to the ACP
function acvi_admin_menu()
{
      add_menu_page(
        'Amelia - CV Instructores', // Title of the page
        'ACVI', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        plugin_dir_path(__FILE__) . 'acvi-admin-page.php' // The 'slug' - file to display when clicking the link
    );
}

add_action( 'admin_enqueue_scripts', 'acvi_admin_style' );
function acvi_admin_style() {
    wp_enqueue_style( 'acvi-admin-style', plugins_url('/amelia-cv-instructors/assets/css/main.css'));
}

add_action( 'wp_enqueue_scripts', 'acvi_front_style' );
function acvi_front_style() {
    wp_enqueue_style( 'acvi-front-style', plugins_url('/amelia-cv-instructors/assets/css/front.css'));
    wp_enqueue_style( 'acvi-bootstrap-style', plugins_url('/amelia-cv-instructors/assets/css/bootstrap.min.css'));
}


add_shortcode('acvi-employees', 'shortcode_acvi_employees');

function shortcode_acvi_employees(){

    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM GPD_amelia_users WHERE type='provider'");

    $datos = '<div class="row">';

    foreach($results as $employee){

        $foto = ( $employee -> pictureFullPath ) ? $employee -> pictureFullPath : 'https://happyedupty.com/wp-content/uploads/2021/12/no-image.png';

        $datos .= '<div class="col-md-3">';
        $datos .= '<div class="card" style="width: 18rem;">';
        $datos .= '<div style="background-image: url(' . $foto . ')" class="employee-img" alt="' . $employee -> firstName . ' ' . $employee -> lastName . '"></div>';
        $datos .= '<div class="card-body">';
        $datos .= '<h3 class="card-title">' . $employee -> firstName . ' ' . $employee -> lastName . '</h3>';
        $datos .= '<a href="' . get_site_url() . '/perfil-del-tutor?id=' . $employee -> id .'" class="tutor-btn mt-2">Ver tutor</a>';
        $datos .= '</div>';
        $datos .= '</div></div>';
    }

    $datos .= '</div>';

    return $datos;
}

add_shortcode('acvi-employee', 'shortcode_acvi_employee');

function shortcode_acvi_employee(){

    
    if (isset ($_GET['id'])) {
        $tutorid = $_GET['id'];
    } 
    global $wpdb;
    $user = $wpdb->get_row( "SELECT * FROM GPD_amelia_users WHERE id=$tutorid"); 

    $userServices = $wpdb->get_results(" 
        SELECT *
        FROM GPD_amelia_users
        INNER JOIN GPD_amelia_providers_to_services ON GPD_amelia_users.id = GPD_amelia_providers_to_services.userId
        INNER JOIN GPD_amelia_services ON GPD_amelia_providers_to_services.serviceId = GPD_amelia_services.id
        WHERE GPD_amelia_users.id=$tutorid");

    $userDays = $wpdb->get_results(" 
        SELECT *
        FROM GPD_amelia_users
        INNER JOIN GPD_amelia_providers_to_weekdays ON GPD_amelia_users.id = GPD_amelia_providers_to_weekdays.userId
        WHERE GPD_amelia_users.id=$tutorid");   
        
    $weekDays = array( a, Lunes, Martes, Miercoles, Jueves, Viernes, Sábado, Domingo);

    $foto = ( $user -> pictureFullPath ) ? $user -> pictureFullPath : 'https://happyedupty.com/wp-content/uploads/2021/12/no-image.png';

    $datos = '<div class="container">';
    $datos .= '<div class="row">';
    $datos .= '<div class="col-md-4">';
    $datos .= '<div class="employee-left">';
    $datos .= '<div style="background-image: url(' . $foto . ')" class="employee-img" alt="' . $user -> firstName . ' ' . $user -> lastName . '"></div>';
    $datos .= '<h2>' . $user -> firstName . ' ' . $user -> lastName . '</h2>';
    $datos .= '</div>';
    $datos .= '</div>';

    $datos .= '<div class="col-md-8">';
    $datos .= '<h3>Descripción</h3>';
    $datos .= '<p>' . $user -> note . '</p>';
    $datos .= '<h3>Listado de materias</h3>';
    $datos .= '<ul>';

    foreach($userServices as $a) { 
        $datos .= '<li>' . $a -> name . '</li>';
    }

    $datos .= '</ul>';


    $datos .= '<h3>Disponibilidad del tutor</h3>';
    
    $datos .= '<ul>';

    foreach($userDays as $b) { 
        $datos .= '<li><b>' . $weekDays[$b -> dayIndex] . '</b> ' . date('g:i a',strtotime($b -> startTime)) . ' - ' . date('g:i a',strtotime($b -> endTime)) . '</li>';
    }

    $datos .= '</ul>';

    $datos .= do_shortcode( ' [ameliabooking employee='.$user -> id.'] ' );

    
    $datos .= '</div>';
    $datos .= '</div>';
    $datos .= '</div>';

    return $datos;


}