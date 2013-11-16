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
        $app->response->headers->set('Content-Type', 'application/json');
    }
);

$app->get(
    '/api/feeds/',
    function() use ( $app, $stories_collection ) {
        $stories = array();
        $req = $app->request();
        $start = $req->get('start');

        $stories_found = $stories_collection->find( array())
            ->sort(array('posted_time' => -1))
            ->skip($start)
            ->limit(10);

        foreach ($stories_found as $story){
            array_push($stories, $story);
        }

        $updated_start = $start + count($stories);
    
        $response = array(
            "status" => "ok",
            "stories" => $stories,
            "start" => $updated_start
        );        

        echo json_encode( $response );
        $app->response->headers->set('Content-Type', 'application/json');
    }
);


$app->get(
    '/api/story/:storyid/',
    function($storyid) use ( $app, $user_collection, $usermetadata_collection, $stories_collection ) {

        $stories_collection_find = $stories_collection->findOne( array(
            'storyid' => $storyid
            ));

        $user_collection_find = $user_collection->findOne(array(
            'user_id' => $stories_collection_find['user_id']
            ));

        if ( $stories_collection_find ) {

            $response = array(
            'user_name' => $user_collection_find['user_name'],
            'title' => $stories_collection_find['title'],
            'posted_time' => $stories_collection_find['posted_time'],
            'description' => $stories_collection_find['description'],
            'images' => $stories_collection_find['images']
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');
        } else {
            $response = array(
                "status" => "error",
                "message" => "The storyid you were trying to find seems to be missing!"
            );
            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setStatus(400);
        }
    }
);
/* END OF GET ROUTES */


/* POST ROUTES HERE */
$app->post(
    '/api/login',
    function () use ( $app, $user_collection, $usermetadata_collection ) {

        $slim_environment_vars = $app->environment;
        $slim_input = json_decode( $slim_environment_vars['slim.input'] );
        
        // @TODO: write form validation using inbuilt php functions
        $user_name = $slim_input->user_name;
        $password = $slim_input->password;

        // Check with MongoDB database here
        $login_query = $user_collection->findOne( array(
            'user_name' => $user_name,
            'password' => $password
            ) 
        );

        // If user is in the database
        if ( $login_query ) {

            $generated_token = generate_token();

            $new_data = array('$set' => array('token' => $generated_token));
            $usermetadata_collection->update(array( 'user_name' => $user_name  ), $new_data);
            $user_name = $login_query["user_name"];
            $user_id = $login_query["user_id"];

            $response = array(
                "status" => "ok",
                "token" => $generated_token, // Need to generate token here @TODO: Write a token generator function
                "user_id" => $user_id
            );

            // Return JSON response
            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');

        } else {

            // Return JSON response
            $response = array(
                "status" => "error",
                "message" => "Your user/password combination is incorrect" // Need to generate token here @TODO: Write a token generator function
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');

            $app->response->setStatus(400);
        } 
    }
);

$app->post(
    '/api/signup',
    function() use ($app, $user_collection, $usermetadata_collection) {

        $slim_environment_vars = $app->environment;
        $slim_input = json_decode( $slim_environment_vars['slim.input'] );

        $user_name = $slim_input->user_name;

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
            $password = $slim_input->password;
            $email = $slim_input->email;
            $user_id = $user_name."_".uniqid();
            $new_account = array(
                "user_name" => $user_name,
                "user_id" => $user_id,
                "password" => $password,
                "email" => $email
            );
            $meta_data = array(
                "user_name" => $user_name,
                "user_id" => $user_id,
                "token" => $token,
                "story_count" => 0
            );

            $user_object_id = $user_collection->insert($new_account);
            $user_meta_data = $usermetadata_collection->insert($meta_data);

            // Check to ensure new account is created in mongodb
            if ($user_object_id){

                $response = array(
                    "status" => "ok",
                    "token" => $token,
                    "user_id" => $user_id
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
        $app->response->headers->set('Content-Type', 'application/json');

    }

);

$app->post(
    '/api/story',
    function () use ( $app, $user_collection, $stories_collection, $usermetadata_collection ) {

        $slim_environment_vars = $app->environment;
        $slim_input = json_decode( $slim_environment_vars['slim.input'] );

        $user_id = $slim_input->user_id;
        $title = $slim_input->title;
        $posted_time = $slim_input->posted_time;
        $description = $slim_input->description;
        $images = $slim_input->images;

        // Check for existing user
        $existing_user = $user_collection->findOne( array(
            'user_id' => $user_id,
            ) );

        if ( !empty( $existing_user )) {

            $user_meta_data = $usermetadata_collection->findOne( array( 'user_id' => $user_id ));

            $story_count = $user_meta_data['story_count'];
           
            $new_data = array('$set' => array('story_count' => $story_count + 1 ));
            $usermetadata_collection->update(array( 'user_id' => $user_id  ), $new_data);
        
            $new_story = array(
                'storyid' => $user_id . '_' . uniqid(),
                'user_name' =>  $existing_user['user_name'],
                'user_id' => $user_id,
                'title' => $title,
                'posted_time' => (int) $posted_time,
                'description' => $description,
                'images' => $images
            );

            $user_object_id = $stories_collection->insert($new_story);

            $response = array(
                'status' => 'ok',
                );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');

        } else {
            $response = array(
                'status' => 'error',
                'error_message' => 'You are neither logged in or seem to be a registered user.'
                );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');

            $app->response->setStatus(400);
        } 
    }
);

$app->post(
    '/api/story/like',
    function () use ( $app, $user_collection, $stories_collection, $usermetadata_collection, $likes_collection ) {

        $slim_environment_vars = $app->environment;
        $slim_input = json_decode( $slim_environment_vars['slim.input'] );

        $user_id = $slim_input->user_id;
        $story_id = $slim_input->story_id;

        // Each like adds another entry to the likes collection table
        $new_story_like_data = array(
            'story_id' => $story_id,
            'likes' => $user_id
        );

        // Check if user already liked the story id
        $user_like_exist = $likes_collection->findOne( array( 'likes' => $user_id, 'story_id' => $story_id ));

        if ( empty( $user_like_exist ) ) {
            $new_story_like_object = $likes_collection->insert($new_story_like_data);
            $response = array(
                'status' => 'ok'
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');
        } else {
            $response = array(
                'status' => 'the like was not recorded due to an error or user has already liked the entry!'
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setStatus(400);
        }


    }
);

$app->post(
    '/api/story/unlike',
    function () use ( $app, $likes_collection ) {

        $slim_environment_vars = $app->environment;
        $slim_input = json_decode( $slim_environment_vars['slim.input'] );

        $user_id = $slim_input->user_id;
        $story_id = $slim_input->story_id;

        // Check if user already liked the story id
        $user_like_exist = $likes_collection->findOne( array( 'likes' => $user_id, 'story_id' => $story_id ));

        if ( !empty( $user_like_exist ) ) {

            $likes_collection->remove( array('likes' => $user_id, 'story_id' => $story_id), array('justOne' => true ));
            $response = array(
                'status' => 'ok'
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');
        } else {
            $response = array(
                'status' => 'the unlike was not recorded due to an error or user has already unliked the entry!'
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setStatus(400);
        }

    }
);

$app->post(
    '/api/story/comment',
    function() use ( $app, $comments_collection ) {

        $slim_environment_vars = $app->environment;
        $slim_input = json_decode( $slim_environment_vars['slim.input'] );

        $user_id = $slim_input->user_id;
        $story_id = $slim_input->story_id;
        $comment = $slim_input->comment;

        // Check if user has already submitted the same exact comment
        $user_comment_exist = $comments_collection->findOne( array( 'comment' => $comment, 'story_id' => $story_id, 'user_id' => $user_id ) ); 

        $new_user_comment_data = array(
            'story_id' => $story_id,
            'user_id' => $user_id,
            'comment' => $comment
            );

        if ( empty( $user_comment_exist ) ) {

            $comments_collection->insert($new_user_comment_data);
            $response = array(
                'status' => 'ok'
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');

        } else {
            $response = array(
                'status' => 'the comment was not recorded due to an error or a duplicate entry from the same user exists!'
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setStatus(400);
        }

    }
);

/* END OF POST ROUTES */

// Run the slim application
$app->run();
