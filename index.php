<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
require( 'lib/mongodb.php' );
require( 'lib/tokengenerator.php' );

$collection_object = array(
    'user' => $user_collection,
    'stories' => $stories_collection
    );

$app = new \Slim\Slim();

/* POST ROUTES HERE */
$app->get(
    '/api/login',
    function () {



});
/* END OF POST ROUTES */


/* POST ROUTES HERE */
$app->post(
    '/api/login',
    function () use ( $app, $user_collection ) {

        // @TODO: write form validation using inbuilt php functions
        $user_name = $_POST['user_name'];
        $password = $_POST['password'];

        // Check with MongoDB database here
        $login_query = $user_collection->findOne( array(
            'user_name' => $user_name,
            'user_password' => $password
            ) );

        // If user is in the database
        if ( $login_query ) {

            $response = array(
                "status" => "ok",
                "token" => generate_token() // Need to generate token here @TODO: Write a token generator function
                );

            // Return JSON response
            echo json_encode( $response );

        } else {

            // Return JSON response
            $response = array(
                "status" => "error",
                "message" => "Your user/password combination is incorrect" // Need to generate token here @TODO: Write a token generator function
                );

            echo json_encode( $response );

            $app->response->setStatus(400);

        }
    }
);

$app->post(
    '/api/signup',
    function() use ($app, $user_collection){

        $user_name = $_POST["user_name"];

        // Check for existing user
        $existing_user = $user_collection->findOne( array(
            'user_name' => $user_name
            ) );

        if ($existing_user) {
            $response = array(
                "status" => "error",
                "message" => "Username already exist",
                $app->response->setStatus(400);
            )
        } else {
            $password = $_POST["passowrd"],
            $email = $_POST["email"],
            $new_account = array(
                "user_name" => $user_name,
                "password" => $password,
                "email" => $email
            );
            $user_object_id =  $user_collection->insert($new_account);
            // Check to ensure new account is created in mongodb
            if ($user_object_id){
                $response = array(
                    "status" => "ok",
                    "token" => generate_token()
                )
            } else {
                $response = array(
                    "status" => "error",
                    "message" => "Failed to create new account",
                    $app->response->setStatus(400);
                )
            }
        }
        echo json_encode( $response );
    }
);



/* END OF POST ROUTES */

// PUT route
$app->put(
    '/put',
    function () {
        echo 'This is a PUT route';
    }
);

// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});

// DELETE route
$app->delete(
    '/delete',
    function () {
        echo 'This is a DELETE route';
    }
);

// Run the slim application
$app->run();
