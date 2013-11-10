<?php
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
require( 'lib/mongodb.php' );
require( 'lib/tokengenerator.php' );

$app = new \Slim\Slim();

// Enable Logging //
$app->config( 'debug', true );


/* GET ROUTES HERE */
$app->get(
    '/',
    function() use ( $app ) {
        echo "You are the chosen one";
   }
);

$app->get(
    '/test',
    function() use ( $app ) {
        echo "test successful!";
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
            $user_id = $user_meta_data["user_id"];

            $user_stories = $stories_collection->find( array(
                "user_id" => $user_id
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
    '/api/feeds/',
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
            $user_id = $user_meta_data["user_id"];

            $user_stories = $stories_collection->find( array( 
                'user_id' => array( '$ne' => $user_id )
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

$app->get(
    '/api/story/:storyid/',
    function($storyid) use ( $app, $usermetadata_collection, $stories_collection ) {

        var_dump( $storyid );


    }
);
/* END OF GET ROUTES */


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
            $user_id = $login_query["user_id"];

            $response = array(
                "status" => "ok",
                "token" => $generated_token, // Need to generate token here @TODO: Write a token generator function
                "user_id" => $user_id
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


        $user_name = $_POST["user_name"]

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
            $user_id = $user_name."_".uniqid();
            $new_account = array(
                "user_name" => $user_name,
                "user_id" => $user_id,
                "password" => $password,
                "email" => $email
            );
            $meta_data = array(
                "user_name" => $user_name
                "user_id" => $user_id,
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
    '/api/story',
    function () use ( $app, $user_collection, $stories_collection, $usermetadata_collection ) {

        $user_id = $_POST['user_id'];
        $title = $_POST['title'];
        $posted_time = $_POST['posted_time'];
        $description = $_POST['description'];
        $images = $_POST['images'];

        var_dump( $images );

        // Check for existing user
        $existing_user = $usermetadata_collection->findOne( array(
            'user_id' => $user_id,
            ) );

        if ( !empty( $existing_user )) {

            $user_meta_data = $usermetadata_collection->findOne( array( 'user_id' => $user_id ));

            $latest_story_id = $user_meta_data['latest_story_id'];
           
            $new_data = array('$set' => array('latest_story_id' => $latest_story_id + 1 ));
            $usermetadata_collection->update(array( 'user_id' => $user_id  ), $new_data);
        
            $new_story = array(
                'storyid' => $latest_story_id + 1,
                'user_id' => $user_id,
                'unique_id' => $user_id . '_' . uniqid();
                'title' => $title,
                'posted_time' => $posted_time,
                'description' => $description,
                'images' => $images
            );

            $user_object_id = $stories_collection->insert($new_story);

            $response = array(
                'status' => 'ok',
                );

            echo json_encode( $response );

        } else {
            $response = array(
                'status' => 'error',
                'error_message' => 'You are neither logged in or seem to be a registered user.'
                );

            echo json_encode( $response );

            $app->response->setStatus(400);
        }
    }
);

/* END OF POST ROUTES */

// Run the slim application
$app->run();
