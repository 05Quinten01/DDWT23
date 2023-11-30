<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt23_week2', 'ddwt23','ddwt23');

/* Redundant Code */
/* Get Number of Series */
$nbr_series = count_series($db);
$right_column = use_template('cards');
$template = Array(
    1 => Array(
        'name' => 'Home',
        'url' => '/DDWT23/week2/'
    ),
    2 => Array(
        'name' => 'Overview',
        'url' => '/DDWT23/week2/overview/'
    ),
    3 => Array(
        'name' => 'My Account',
        'url' => '/DDWT23/week2/myaccount/'
    ),
    4 => Array(
    'name' => 'Add',
    'url' => '/DDWT23/week2/add/'
    ),
    5 => Array(
        'name' => 'Register',
        'url' => '/DDWT23/week2/register/'
    )
);
$nbr_users = count_users($db);

/* Landing page */
if (new_route('/DDWT23/week2/', 'get')) {

    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Home' => na('/DDWT23/week2/', True)
    ]);
    $navigation = get_navigation($template, 1);

    /* Page content */

    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    /* Choose Template */
    include use_template('main');
}

/* Overview page */
elseif (new_route('/DDWT23/week2/overview/', 'get')) {


    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Overview' => na('/DDWT23/week2/overview', True)
    ]);
    $navigation = get_navigation($template,1);

    /* Page content */

    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_series_table(get_series($db), $db);

    /* Choose Template */
    include use_template('main');
}

/* Single Series */
elseif (new_route('/DDWT23/week2/series/', 'get')) {



    /* Get series from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);

    /* Page info */
    $page_title = $series_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Overview' => na('/DDWT23/week2/overview/', False),
        $series_info['name'] => na('/DDWT23/week2/series/?series_id='.$series_id, True)
    ]);
    $navigation = get_navigation($template, 1);

    /* Page content */

    $page_subtitle = sprintf("Information about %s", $series_info['name']);
    $page_content = $series_info['abstract'];
    $nbr_seasons = $series_info['seasons'];
    $creators = $series_info['creator'];
    $added_by = get_user_name($series_info['info'], $db);

    /* Choose Template */
    include use_template('series');
}

/* Add series GET */
elseif (new_route('/DDWT23/week2/add/', 'get')) {

    if ( !check_login() ) {
        redirect('/DDWT23/week2/login/');
    }

    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Add Series' => na('/DDWT23/week2/new/', True)
    ]);
    $navigation = get_navigation(template, 5);

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_btn = "Add Series";
    $form_action = '/DDWT23/week2/add/';

    if (isset($_GET['error_msg'])){
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('new');
}

/* Add series POST */
elseif (new_route('/DDWT23/week2/add/', 'post')) {

    if (!check_login() ) {
        redirect('/DDWT23/week2/login');
    }
    /* Add series to database */
    $feedback = add_series($db, $_POST);
    redirect(sprintf('/DDWT23/week2/add/?error_msg=%s',
        json_encode($feedback)));
    include use_template('new');
}

/* Edit series GET */
elseif (new_route('/DDWT23/week2/edit/', 'get')) {



    /* Get series info from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        sprintf("Edit Series %s", $series_info['name']) => na('/DDWT23/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 1);

    /* Page content */

    $page_subtitle = sprintf("Edit %s", $series_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/DDWT23/week2/edit/';

    /* Choose Template */
    include use_template('new');
}

/* Edit series POST */
elseif (new_route('/DDWT23/week2/edit/', 'post')) {
    /* Update series in database */
    if ( !check_login() ) {
        redirect('/DDWT23/week2/login/');
    }

    $feedback = update_series($db, $_POST);

    $series_id = $_POST['series_id'];
    $series_info = get_series_info($db, $series_id);

    if($feedback['type'] == 'danger' or $feedback['type'] == 'warning') {
        redirect(sprintf('/DDWT23/week2/edit/?error_msg=%s',
            json_encode($feedback)));
    } else {
        redirect(sprintf('/DDWT23/week2/series/?error_msg=%s',
            json_encode($feedback)));
    }

    /* Choose Template */
    include use_template('series');
}

/* Remove series */
elseif (new_route('/DDWT23/week2/remove/', 'post')) {
    /* Remove series in database */
    if (!check_login() ){
        redirect('/DDWT23/week2/login');
    }

    $series_id = $_POST['series_id'];
    $feedback = remove_series($db, $series_id);

    if($feedback['type'] == 'warning') {
        redirect(sprintf('/DDWT23/week2/edit/?error_msg=%s',
            json_encode($feedback)));
    } else {
        redirect(sprintf('/DDWT23/week2/overview/?error_msg=%s',
            json_encode($feedback)));
    }

    include use_template('new');
}

/* My Account GET */
elseif (new_route('/DDWT23/week2/myaccount/', 'get')) {
    if (!check_login() ){
        redirect('/DDWT23/week2/login/');
    }

    /* Page info */
    $page_title = 'My Account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'My Account' => na('/DDWT23/week2/myaccount/', True)
    ]);
    $navigation = get_navigation(template, 3);

    /* Page content */
    $page_subtitle = 'Overview of your account';
    $page_content = 'Information about your account';

    $user = get_user_name($_SESSION['user_id'], $db);

    if (isset($_GET['error_msg'])){
        $error_msg = get_error($_GET['error_msg']);
    }

    include use_template('account');
}

/* Register GET */
elseif (new_route('/DDWT23/week2/register/', 'get')) {
    /* Page info */
$page_title = 'Register';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Register' => na('/DDWT23/week2/register/', True)
    ]);
    $navigation = get_navigation($template, 4);
    /* Page content */
    $page_subtitle = 'Register on the Series Overview!';

    if (isset($_GET['error_msg'])){
        $error_msg = get_error($_GET['error_msg']);
    }


    include use_template('register');
}

/* Register POST */
elseif (new_route('/DDWT23/week2/register/', 'post')) {
$feedback = register_user($db, $_POST);

    if($feedback['type'] == 'danger') {
        redirect(sprintf('/DDWT23/week2/register/?error_msg=%s',
            json_encode($feedback)));
    } else {
        redirect(sprintf('/DDWT23/week2/myaccount/?error_msg=%s',
            json_encode($feedback)));
    }
}

/* Login GET */
elseif (new_route('/DDWT23/week2/login/', 'get')) {
    /* Page info */
    if ( check_login() ) {
        redirect('/DDWT23/week2/myaccount/');
    }
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
        'DDWT23' => na('/DDWT23/', False),
        'Week 2' => na('/DDWT23/week2/', False),
        'Login' => na('/DDWT23/week2/login/', True)
    ]);

    $navigation = get_navigation($template, 0);
    /* Page content */
    $page_subtitle = 'Login using your username and password';

    if (isset($_GET['error_msg'])){
        $error_msg = get_error($_GET['error_msg']);
    }
    include use_template('login');
}

/* Login POST */
elseif (new_route('/DDWT23/week2/login/', 'post')) {
$feedback = login_user($db, $_POST);
    if($feedback['type'] == 'danger') {
        /* Redirect to login screen */
        redirect(sprintf('/DDWT23/week2/login/?error_msg=%s',
            json_encode($feedback)));
    } else {
        redirect(sprintf('/DDWT23/week2/myaccount/?error_msg=%s',
            json_encode($feedback)));
    }
}
else {
    http_response_code(404);
    echo '404 Not Found';
}
