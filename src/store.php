<?php
include_once './php/database.class.php';
include_once './php/query.class.php';
header('Content-Type: application/json; charset=UTF-8');
$parameters = array(
    'data' => null,
    'site' => null,
    'info' => null
);
$success = false;
if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
    if (isset($_POST['site']) && isset($_POST['data'])) {
        $parameters['site'] = trim($_POST['site']);
        $parameters['data'] = $_POST['data'];
        $parameters['info'] = isset($_POST['info']) ? trim($_POST['info']) : null; // optional
        $success = true;
    } else {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $data = @json_decode($raw);
            if ($data) {
                if (isset($data->site) && isset($data->data)) {
                    $parameters['site'] = trim(urldecode($data->site));
                    $parameters['data'] = urldecode($data->data);
                    $parameters['info'] = isset($data->info) ? trim(urldecode($data->info)) : null; // optional
                    $success = true;
                }
            }
        }
    }
} else if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'get') {
    if (isset($_GET['site']) && isset($_GET['data'])) {
        $parameters['site'] = trim($_GET['site']);
        $parameters['data'] = $_GET['data'];
        $parameters['info'] = isset($_GET['info']) ? trim($_GET['info']) : null; // optional
        $success = true;
    }
}
if ($success) {
    $response = array(
        'status' => 'ok',
        'message' => array()
    );
    mb_internal_encoding('UTF-8');
    $error = false;
    if (mb_strlen($parameters['site']) < 1) {
        $response['status'] = 'error';
        $response['message']['site'] = 'Site is required';
        $error = true;
    } else if (mb_strlen($parameters['site']) > 300) {
        $response['status'] = 'error';
        $response['message']['site'] = 'Site is exceeding 300 characters';
        $error = true;
    }
    if (mb_strlen($parameters['data']) < 1) {
        $response['status'] = 'error';
        $response['message']['site'] = 'Data is required';
        $error = true;
    } else if (mb_strlen($parameters['data']) > 1000) {
        $response['status'] = 'error';
        $response['message']['site'] = 'Data is exceeding 1000 characters';
        $error = true;
    }
    if (mb_strlen($parameters['info']) > 100) {
        $parameters['info'] = substr($parameters['info'], 0, 100);
    }
    if (!$error) {
        $params = array(
            'site' => $parameters['site'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'data' => $parameters['data'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'date' => date('Y-m-d H:i:s', time()),
            'info' => $parameters['info']
        );
        if (!Query::insert('INSERT INTO `data` (`site`, `method`, `data`, `ip`, `date`, `info`) VALUES (:site, :method, :data, :ip, :date, :info)', $params)) {
            $response['status'] = 'error';
            $response['message']['global'] = 'Database error';
        }
    }
    echo json_encode($response, JSON_PRETTY_PRINT);
}
?>
