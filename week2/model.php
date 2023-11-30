<?php
/**
 * Model
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Connects to the database using PDO
 * @param string $host Database host
 * @param string $db Database name
 * @param string $user Database user
 * @param string $pass Database password
 * @return PDO Database object
 */
function connect_db($host, $db, $user, $pass){
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        echo sprintf("Failed to connect. %s",$e->getMessage());
    }
    return $pdo;
}

/**
 * Check if the route exists
 * @param string $route_uri URI to be matched
 * @param string $request_type Request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    } else {
        return False;
    }
}

/**
 * Creates a new navigation array item using URL and active status
 * @param string $url The URL of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template Filename of the template without extension
 * @return string
 */
function use_template($template){
    $template_doc = sprintf("views/%s.php", $template);
    return $template_doc;
}

/**
 * Creates breadcrumb HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '
    <nav aria-label="breadcrumb">
    <ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        }else{
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '
    </ol>
    </nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation HTML code using given array
 * @param array $template Array with as Key the page name and as Value the corresponding URL
 * @param $active_id
 * @return string HTML code that represents the navigation
 */
function get_navigation($template, $active_id){
    $navigation_exp = '
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand">Series Overview</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">';
    foreach ($template as $id => $info) {
        if ($id === $active_id){
            $navigation_exp .= '<li class="nav-item active">';
            $navigation_exp .= '<a class="nav-link" href="'.$info['url'].'">'.$info['name'].'</a>';
        }else{
            $navigation_exp .= '<li class="nav-item">';
            $navigation_exp .= '<a class="nav-link" href="'.$info['url'].'">'.$info['name'].'</a>';
        }

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '
    </ul>
    </div>
    </nav>';
    return $navigation_exp;
}

/**
 * Creates a Bootstrap table with a list of series
 * @param array $series Associative array of series
 * @param $pdo
 * @return string
 */
function get_series_table($series, $pdo){
    $table_exp = '
    <table class="table table-hover">
    <thead
    <tr>
        <th scope="col">Series</th>
        <th scope="col"></th>
        <th scope="col"></th>
    </tr>
    </thead>
    <tbody>';
    foreach($series as $key => $value){
        $table_exp .= '
        <tr>
            <th scope="row">'.$value['name'].'</th>
            <td><a href="/DDWT23/week2/series/?series_id='.$value['id'].'" role="button" class="btn btn-primary">More info</a></td>
            <td>'.get_user_name($value['user'], $pdo).'</td>
        </tr>
        ';
    }
    $table_exp .= '
    </tbody>
    </table>
    ';
    return $table_exp;
}

/**
 * Pretty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Get array with all listed series from the database
 * @param PDO $pdo Database object
 * @return array Associative array with all series
 */
function get_series($pdo){
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_exp = Array();

    /* Create array with htmlspecialchars */
    foreach ($series as $key => $value){
        foreach ($value as $user_key => $user_input) {
            $series_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }
    return $series_exp;
}

/**
 * Generates an array with series information
 * @param PDO $pdo Database object
 * @param int $series_id ID from the series
 * @return mixed
 */
function get_series_info($pdo, $series_id){
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$series_id]);
    $series_info = $stmt->fetch();
    $series_info_exp = Array();

    /* Create array with htmlspecialchars */
    foreach ($series_info as $key => $value){
        $series_info_exp[$key] = htmlspecialchars($value);
    }
    return $series_info_exp;
}

/**
 * Creates HTML alert code with information about the success or failure
 * @param array $feedback Associative array with keys type and message
 * @return string
 */
function get_error($feedback){
    $feedback = json_decode($feedback, True);
    $error_exp = '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
    return $error_exp;
}

/**
 * Add series to the database
 * @param PDO $pdo Database object
 * @param array $series_info Associative array with series info
 * @return array Associative array with key type and message
 */
function add_series($pdo, $series_info){
    /* Check if all fields are set */
    if (
        empty($series_info['Name']) or
        empty($series_info['Creator']) or
        empty($series_info['Seasons']) or
        empty($series_info['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check data type */
    if (!is_numeric($series_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Check if series already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$series_info['Name']]);
    $series = $stmt->rowCount();
    if ($series){
        return [
            'type' => 'danger',
            'message' => 'This series was already added.'
        ];
    }

    /* Add Series */
    $stmt = $pdo->prepare("INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $series_info['Name'],
        $series_info['Creator'],
        $series_info['Seasons'],
        $series_info['Abstract']
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted ==  1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' added to Series Overview.", $series_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not added. Try it again.'
        ];
    }
}

/**
 * Updates a series in the database
 * @param PDO $pdo Database object
 * @param array $series_info Associative array with series info
 * @return array
 */
function update_series($pdo, $series_info){
    /* Check if all fields are set */
    if (
        empty($series_info['Name']) or
        empty($series_info['Creator']) or
        empty($series_info['Seasons']) or
        empty($series_info['Abstract']) or
        empty($series_info['series_id'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check data type */
    if (!is_numeric($series_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Get current series name */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$series_info['series_id']]);
    $series = $stmt->fetch();
    $current_name = $series['name'];

    /* Check if series already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$series_info['Name']]);
    $series = $stmt->fetch();
    if ($series_info['Name'] == $series['name'] and $series['name'] != $current_name){
        return [
            'type' => 'danger',
            'message' => sprintf("The name of the series cannot be changed. %s already exists.", $series_info['Name'])
        ];
    }

    /* Update Series */
    $stmt = $pdo->prepare("UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?");
    $stmt->execute([
        $series_info['Name'],
        $series_info['Creator'],
        $series_info['Seasons'],
        $series_info['Abstract'],
        $series_info['series_id']
    ]);
    $updated = $stmt->rowCount();
    if ($updated ==  1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was edited!", $series_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'The series was not edited. No changes were detected.'
        ];
    }
}

/**
 * Removes a series with a specific series ID
 * @param PDO $pdo Database object
 * @param int $series_id ID of the series
 * @return array
 */
function remove_series($pdo, $series_id){
    /* Get series info */
    $series_info = get_series_info($pdo, $series_id);

    /* Delete Series */
    $stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
    $stmt->execute([$series_id]);
    $deleted = $stmt->rowCount();
    if ($deleted ==  1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was removed!", $series_info['name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'An error occurred. The series was not removed.'
        ];
    }
}

/**
 * Count the number of series listed on Series Overview
 * @param PDO $pdo Database object
 * @return int
 */
function count_series($pdo){
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->rowCount();
    return $series;
}

/**
 * Changes the HTTP Header to a given location
 * @param string $location Location to redirect to
 */
function redirect($location){
    header(sprintf('Location: %s', $location));
    die();
}

/**
 * Get current user ID
 * @return bool Current user ID or False if not logged in
 */
function get_user_id(){
    session_start();
    if (isset($_SESSION['user_id'])){
        return $_SESSION['user_id'];
    } else {
        return False;
    }
}

/**
 * Gets the name of a user from the database.
 * @param $user_id
 * @param $pdo
 * @return string of first name and last name of user
 */
function get_user_name($user_id, $pdo) {
    $stmt = $pdo->prepare('SELECT firstname, lastname FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();
    $user_name_exp = Array();

    foreach ($user_info as $key => $value) {
        $user_name_exp[$key] = htmlspecialchars($value);
    }
    $first_name = $user_name_exp['firstname'];
    $last_name = $user_name_exp['lastname'];
    return "$first_name $last_name";
}

/**
 * Get total users
 * @param PDO database
 * @return int sum of users
 */
function count_users($pdo){
    $stmt = $pdo->prepare('SELECT * FROM users');
    $stmt->execute();
    $users = $stmt->rowCount();
    return $users;
}

/**
 * Add user to database
 * @param String $pdo databse connection
 * @param array $form_data formdata
 * @return array|string[] responds
 */
function register_user($pdo, $form_data){
    if (
        empty($form_data['username']) or
        empty($form_data['password']) or
        empty($form_data['firstname']) or
        empty($form_data['lastname'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'Enterusername, password, first- and last name.'
        ];
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$form_data['username']]);
        $user_exists = $stmt->rowCount();
    } catch (PDOException $e) {
        return [
            'type' => 'danger',
            'message' => sprintf('An error occured: %s', $e->getMessage())
        ];
    }

    if ( !empty($user_exists) ) {
        return [
            'type' => 'danger',
            'message' => 'The username you entered already exists!'
        ];
    }

    $password = password_hash($form_data['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, password, firstname, lastname) VALUES (?, ?, ?, ?)');
        $stmt->execute([$form_data['username'], $password, $form_data['firstname'],
            $form_data['lastname']]);
        $user_id = $pdo->lastInsertId();
    } catch (PDOException $e) {
        return [
            'type' => 'danger',
            'message' => sprintf('There was an error: %s', $e->getMessage())
        ];
    }

    session_start();
    $_SESSION['user_id'] = $user_id;
    return [
        'type' => 'success',
        'message' => sprintf('%s, account was created successfully!',
            get_user_name($_SESSION['user_id'], $pdo))
    ];
}

/**
 * Logs in user
 * @param String $pdo database
 * @param array $form_data
 * @return array|string[] responds
 */
function login_user($pdo, $form_data){
    if (
        empty($form_data['username']) or
        empty($form_data['password'])
    ){
        return [
            'type' => 'danger',
            'message' => 'You should enter a username and password.'
        ]; }
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$form_data['username']]);
        $user_info = $stmt->fetch();
    } catch (\PDOException $e) {
        return [
            'type' => 'danger',
            'message' => sprintf('There was an error: %s', $e->getMessage())
        ];
    }
    if ( empty($user_info) ) {
        return [
            'type' => 'danger',
            'message' => 'The username you entered does not exist!'
        ];
    }
    if ( !password_verify($form_data['password'], $user_info['password']) ){
        return [
            'type' => 'danger',
            'message' => 'The password you entered is incorrect!'
        ];
    } else {
        session_start();
        $_SESSION['user_id'] = $user_info['id'];
        $feedback = [
            'type' => 'success',
            'message' => sprintf('%s, you were logged in successfully!', get_user_name($_SESSION['user_id']), $pdo)
        ];
        redirect(sprintf('/DDWT23/week2/myaccount/?error_msg=%s',
            json_encode($feedback)));
    }
}

/**
 * Checks if user is logged in
 * @return bool
 */
function check_login(){
    session_start();
    if (isset($_SESSION['user_id'])){
        return True;
    }
    else{
        return False;
    }
}