<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt23_week3', 'ddwt23', 'ddwt23');

/* Create Router instance */
$router = new \Bramus\Router\Router();

$cred = set_cred('ddwt23', 'ddwt23');

// Add routes here
$router->mount('/api', function() use ($router, $db, $cred) {
    $router->before('GET|POST|PUT|DELETE', '/api/.*', function() use($cred){
        if(!check_cred($cred)) {
            $feedback = [
                'type' => 'danger',
                'message' => 'Authentication failed. Please check the credentials.'
            ];
            echo json_encode($feedback);
            exit();
        }
    });

    http_content_type('application/json');

    $router->get('/series', function() use($db) {
        $series = get_series($db);
        echo json_encode($series);
    });

    $router->get('/series/(\d+)', function ($id) use($db){
        $series_info = get_series_info($db, $id);
        echo json_encode($series_info);
    });

    $router->get('/series/(\d+)', function ($id) use($db){
       $delete_series = remove_series($db, $id);
       echo json_encode($delete_series);
    });

    $router->post('/series/add', function () use($db){
       $add_series = add_series($db, $_POST);
       echo json_encode($add_series);
    });

    $router->put('/series/(\d+)', function ($id) use($db){
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];
        $update_series = update_series($db, $serie_info);
        echo json_encode($update_series);
    });

    $router->set404(function() {
        header('HTTP/1.1 404 Not Found');
        echo 'Error 404: page not found';
    });

});

/* Run the router */
$router->run();