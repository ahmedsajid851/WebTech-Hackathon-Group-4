<?php
session_start();
$controller = isset($_GET ["controller"]) ? $_GET["controller"] :"home";
$action = isset($_GET ["action"]) ? $_GET ["action"] :"index";

//controller class file name
$controllerName = ucfirst($controller) ."Controller";
$controllerFile = __DIR__ ."/controller/". $controllerName .".php";

if (!file_exists($controllerFile)) {
    die ("Controller $controllerName not found");
}
require_once $controllerFile;
$controllerInstance = new $controllerName();
if(!method_exists($controllerInstance, $action)) {
    die ("Action $action not found in controller $controllerName");
}
//calling action
$controllerInstance->$action();
?>