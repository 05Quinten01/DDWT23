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
    return sprintf("views/%s.php", $template);
}

/**
 * Creates breadcrumbs HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        } else {
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation bar HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the navigation bar
 */
function get_navigation($navigation){
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach ($navigation as $name => $info) {
        if ($info[1]){
            $navigation_exp .= '<li class="nav-item active">';
        } else {
            $navigation_exp .= '<li class="nav-item">';
        }
        $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
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
 * Creates HTML alert code with information about the success or failure
 * @param array $feedback Associative array with keys type and message
 * @return string
 */
function get_error($feedback){
    return '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
}

function connect_db($host, $db, $user, $pass){
    $charset = "utf8mb4";
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        echo sprintf("Failed to connect. %s", $e->getMessage());
    }
    return $pdo;
}

function count_series($pdo){
    $stmt = $pdo->prepare('SELECT COUNT(id) as number_of_series FROM series');
    $stmt->execute();
    $series_count = $stmt->fetch();
    return $series_count["number_of_series"];
}

function get_series(){
    $pdo = connect_db("localhost", "ddwt23_week1", "ddwt23", "ddwt23");
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();

    foreach($series as $key => $serie){
        $series[$key]["name"] = htmlspecialchars($serie["name"]);
    }

    return $series;
}

function get_series_info($pdo, $series_id){
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$series_id]);
    $series_info = $stmt->fetch();
    $type = Array();

    foreach ($series_info as $key => $value){
        $type[$key] = htmlspecialchars($value);
    }
    return $type;
}

function get_series_table($series){
        $table_exp =    '
        <table class="table table-hover">
        <thead
        <tr>
        <th scope="col">Series</th>
        <th scope="col"></th>
        </tr>
        </thead>
        <tbody>';
        foreach($series as $key => $value){
            $table_exp .=   '
            <tr>
            <th scope="row">'.$value['name'].'</th>
            <td><a href="/DDWT23/week1/series/?series_id='.$value['id'].'" role="button" class="btn btn-primary">More info</a></td>
            </tr>
            ';
        }
        $table_exp .=
        '
        </tbody>
        </table>
        ';
        return $table_exp;
}

function add_series($pdo, $series_post_info){
    $name = $series_post_info['Name'];
    $creator = $series_post_info['Creator'];
    $seasons = $series_post_info['Seasons'];
    $abstract = $series_post_info['Abstract'];

    if (
        empty($name) or
        empty($creator) or
        empty($seasons) or
        empty($abstract)

    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    if (!is_numeric($seasons)) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$name]);
    $series = $stmt->rowCount();
    if ($series){
        return [
            'type' => 'danger',
            'message' => 'This serie was already added.'
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $name, $creator, $seasons, $abstract
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Serie '%s' added to Series Overview.", $name)
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The serie was not added. Try it again.'
        ];
    }
}

function update_series($pdo, $series_info, $series_post_info){

    if (
        empty($series_post_info['Name']) or
        empty($series_post_info['Creator']) or
        empty($series_post_info['Seasons']) or
        empty($series_post_info['Abstract'])

    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    if (!is_numeric($series_post_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
    $stmt->execute([$series_info['id']]);
    $serie = $stmt->fetch();
    $current_name = $serie['name'];

    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$series_post_info['Name']]);
    $serie = $stmt->fetch();
    if ($series_post_info['Name'] == $serie['name'] and $serie['name'] != $current_name){
        return [
            'type' => 'danger',
            'message' => sprintf("The name of the serie cannot be changed. %s already exists.",
                $series_post_info['Name'])
        ];
    }

    $stmt = $pdo->prepare('UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?');
    $stmt->execute([
        $series_post_info['Name'],
        $series_post_info['Creator'],
        $series_post_info['Seasons'],
        $series_post_info['Abstract'],
        $series_post_info['Id']
    ]);
    $updated = $stmt->rowCount();
    if ($updated == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Serie '%s' was edited!", $series_info['name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' =>  sprintf("The serie '%s' was not edited. No changes were detected.", $series_info['name'])
        ];
    }
}

function remove_series($pdo, $series_id){
    $series_info = get_series_info($pdo, $series_id);

    $stmt = $pdo->prepare('DELETE FROM series WHERE id = ?');
    $stmt->execute([$series_id]);
    $deleted = $stmt->rowCount();
    if ($deleted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Book '%s' was removed!", $series_info['name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'An error occurred. The book was not removed.'
        ];
    }
}
