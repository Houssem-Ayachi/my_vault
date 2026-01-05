<?php

use App\Controllers\UsersController;
use App\Engine\Router;

$router = new Router();

$router->get("/users/{id}", [UsersController::class, "index"]);
$router->post("/users", [UsersController::class, "signup"]);

$router->get("/users/{id}/message/{message}", function (int $id, string $message) {
    echo "id is : " . $id . " message: " . $message;
});
