<?php
error_reporting(E_STRICT | E_ALL);
ini_set("display_errors", "on");

require __DIR__."/../app/autoload.php";

//build tester
//$api = new Ayamel\ResourceApiBundle\ApiTester("http://localhost/AyamelAPI/web/index.php/api/v1/rest");
$api = new Ayamel\ResourceApiBundle\ApiTester("http://localhost/AyamelAPI/web/index_dev.php/api/v1/rest");

//check for path
if(!isset($_GET['path'])) {
	die("Provide a path to test in the 'path' query parameter.");
}

//decode post if any
$posted = isset($_POST['data']) ? $_POST['data'] : null;
$data = ($posted) ? json_decode($posted) : null;

//check for method
$method = isset($_GET['method']) ? strtolower($_GET['method']) : 'get';
if($posted) $method = isset($_POST['method']) ? $_POST['method'] : $method;

//call api with received path and method
$result = $api->$method($_GET['path'], $data);
//$result = $api->get("/resources/4f7a1db84c76553b66000001");
?>
<!doctype html>
<html>
<head></head>
<body>
    <h1>Api tester</h1>
    <h3>Optionally provide a JSON structure to send.</h3>
    <form id='form-data' method='post'>
        <textarea id='data' name='data' rows='20' cols='50'><?php echo $posted ?></textarea>
        <br />
        <small>Http method to use for sending.</small>
        <select name='method' id='method'>
            <option value="post">POST</option>
            <option value="put">PUT</option>
        </select>
        <input id='submit' type='submit' />
    </form>
    <?php echo $api->debugLastQuery(); ?>
    <h3>Query return:</h3><pre><?php echo print_r($result, true); ?></pre>
</body>
</html>
