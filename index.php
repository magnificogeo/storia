<?php
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
require( 'lib/mongodb.php' );
require( 'lib/tokengenerator.php' );

$app = new \Slim\Slim();

/* GET ROUTES HERE */
$app->get(
    '/',
    function() use ( $app ) {
        echo "You are the chosen one";
   }
);

$app->get(
    '/api/profile/:token',
    function($token) use ( $app, $usermetadata_collection, $stories_collection ) {
        $user_meta_data = $usermetadata_collection->findOne( array(
            'token' => $token
            ) );

        if (!$user_meta_data){
            $response = array(
                "status" => "error",
                "message" => "User data not found"
            );
            $app->response->setStatus(400);

        } else {
            $stories = array();
            $user_name = $user_meta_data["user_name"];

            $user_stories = $stories_collection->find( array(
                "user_name" => $user_name
            ) );

            foreach ($user_stories as $story) {
                array_push( $stories, $story );
            }

            $response = array(
                "status" => "ok",
                "stories" => $stories
            );
        }

        echo json_encode( $response );
    }
);

$app->get(
    '/api/feeds/:token',
    function($token) use ( $app, $usermetadata_collection, $stories_collection ) {
        $user_meta_data = $usermetadata_collection->findOne( array(
            'token' => $token
            ) );

        if (!$user_meta_data){
            $response = array(
                "status" => "error",
                "message" => "User data not found"
            );
            $app->response->setStatus(400);

        } else {
            $stories = array();
            $user_name = $user_meta_data["user_name"];

            $user_stories = $stories_collection->find( array( 
                'user_name' => array( '$ne' => $user_name )
                ));


            foreach ($user_stories as $story) {
                array_push( $stories, $story );
            }

            $response = array(
                "status" => "ok",
                "stories" => $stories
            );
        }

        echo json_encode( $response );
    }
);
/* END OF POST ROUTES */


/* POST ROUTES HERE */
$app->post(
    '/api/login',
    function () use ( $app, $user_collection, $usermetadata_collection ) {

        // @TODO: write form validation using inbuilt php functions
        $user_name = $_POST['user_name'];
        $password = $_POST['password'];

        // Check with MongoDB database here
        $login_query = $user_collection->findOne( array(
            'user_name' => $user_name,
            'password' => $password
            ) );

        // If user is in the database
        if ( $login_query ) {

            $generated_token = generate_token();

            $new_data = array('$set' => array('token' => $generated_token));
            $usermetadata_collection->update(array( 'user_name' => $user_name  ), $new_data);

            $response = array(
                "status" => "ok",
                "token" => $generated_token // Need to generate token here @TODO: Write a token generator function
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
    function() use ($app, $user_collection, $usermetadata_collection){

        $user_name = $_POST["user_name"];

        // Check for existing user
        $existing_user = $user_collection->findOne( array(
            'user_name' => $user_name
            ) );

        if ( !empty($existing_user)) {

            $response = array(
                "status" => "error",
                "message" => "Username already exist",
            );

            $app->response->setStatus(400);

        } else {

            // Store to mongodb
            $token = generate_token();
            $password = $_POST["password"];
            $email = $_POST["email"];
            $new_account = array(
                "user_name" => $user_name,
                "password" => $password,
                "email" => $email
            );
            $meta_data = array(
                "user_name" => $user_name,
                "token" => $token,
                "latest_story_id" => 0
            );

            $user_object_id = $user_collection->insert($new_account);
            $user_meta_data = $usermetadata_collection->insert($meta_data);

            // Check to ensure new account is created in mongodb
            if ($user_object_id){

                $response = array(
                    "status" => "ok",
                    "token" => $token
                );

            } else {

                $response = array(
                    "status" => "error",
                    "message" => "Failed to create new account",
                );

                $app->response->setStatus(400);

            }
        }

        echo json_encode( $response );

    }

);

$app->post(
    '/api/upload',
    function () use ( $app, $user_collection, $stories_collection, $usermetadata_collection ) {

        $story_title = $_POST['story_title'];
        $story_caption = $_POST['story_caption'];
        $story_images = $_POST['story_images'];
        $token = $_POST['token'];
        $timestamp = $_POST['timestamp'];
        $user_name = $_POST['user_name'];

        // Check for existing user
        $existing_user = $usermetadata_collection->findOne( array(
            'user_name' => $user_name,
            'token' => $token
            ) );

        if ( !empty( $existing_user )) {

            $user_meta_data = $usermetadata_collection->findOne( array( 'user_name' => $user_name ));

            $latest_story_id = $user_meta_data['latest_story_id'];
           
            $new_data = array('$set' => array('latest_story_id' => $latest_story_id + 1 ));
            $usermetadata_collection->update(array( 'user_name' => $user_name  ), $new_data);

            $story_image_array = array();
    
            $new_story = array(
                'storyid' => $latest_story_id + 1,
                'user_name' => $user_name,
                'title' => $story_title,
                'caption' => $story_caption,
                'images' => $story_images,
                'timestamp' => $timestamp
            );

            $user_object_id = $stories_collection->insert($new_story);

            $response = array(
                'status' => 'ok',
                );

            echo json_encode( $response );

        } else {
            $response = array(
                'status' => 'error',
                'error_message' => 'not logged in'
                );

            echo json_encode( $response );

            $app->response->setStatus(400);
        }

        
    }
);


/* END OF POST ROUTES */

// Run the slim application
$app->run();
