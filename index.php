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
    function() use ( $app, $user_collection, $usermetadata_collection, $stories_collection, $likes_collection, $comments_collection ) {
        $stories = array();
        $req = $app->request();
        $start = $req->get('start');

        $stories_found = $stories_collection->find( array())
            ->sort(array('posted_time' => -1))
            ->skip($start)
            ->limit(10);

        $counter = 0;
        foreach ($stories_found as $story){
            $counter += 1;
            if (count($story["images"]) == 0)
                continue;
            $user_name = get_user_name( $story["user_id"], $usermetadata_collection );
            $number_of_likes = get_number_of_likes($story["story_id"], $likes_collection);
            # Find comments for the story
            $comments = array();
            $comments_retrieved = $comments_collection->find( array( 'story_id' => $story["story_id"] ) );
            foreach ($comments_retrieved as $comment) {
                // $commenter_user_name = $usermetadata_collection->findOne( array("user_id" => $comment["user_id"]) )["user_name"];
                $commenter_user_name = get_user_name($comment["user_id"], $usermetadata_collection);
                $formatted_comment = array(
                    "comment" => $comment["comment"],
                    "posted_time" => $comment["posted_time"],
                    "user_name" => $commenter_user_name
                    );
                array_push($comments, $formatted_comment);
            }

            $response = array(
                'user_name' => $user_name,
                'title' => $story['title'],
                'posted_time' => $story['posted_time'],
                'description' => $story['description'],
                'images' => $story['images'],
                'likes_count' => $number_of_likes,
                'comments' => $comments
            );


            array_push($stories, $response);
        }

        $updated_start = $start + $counter;
    
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
    '/api/story/:story_id/',
    function($story_id) use ( $app, $user_collection, $usermetadata_collection, $stories_collection, $likes_collection, $comments_collection ) {

        $story = $stories_collection->findOne( array( "story_id" => $story_id ) );
        
        if ( $story ) {
            $user_name = get_user_name( $story["user_id"], $usermetadata_collection );
            # Find number of lights in a story
            $number_of_likes = get_number_of_likes( $story_id, $likes_collection );
            # Find comments for the story
            $comments = array();
            $comments_retrieved = $comments_collection->find( array( 'story_id' => $story_id ) );
            foreach ($comments_retrieved as $comment) {
                $commenter = $usermetadata_collection->findOne( array("user_id" => $comment["user_id"]) );
                $commenter_user_name = $commenter["user_name"];
                $formatted_comment = array(
                    "comment" => $comment["comment"],
                    "posted_time" => $comment["posted_time"],
                    "user_name" => $commenter_user_name
                    );
                array_push($comments, $formatted_comment);
            }

            $response = array(
                'user_name' => $user_name,
                'title' => $story['title'],
                'posted_time' => $story['posted_time'],
                'description' => $story['description'],
                'images' => $story['images'],
                'likes_count' => $number_of_likes,
                'comments' => $comments
            );

            echo json_encode( $response );
            $app->response->headers->set('Content-Type', 'application/json');
        } else {
            $response = array(
                "status" => "error",
                "message" => "The story_id you were trying to find seems to be missing!"
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
        $slim_input = json_decode( $slim_environment_vars['slim.input'], FALSE );

        $token = $slim_input->token;
        $title = $slim_input->title;
        $posted_time = time();
        $description = $slim_input->description;
        $images = $slim_input->images;

        // Check for existing user
        $user_metadata = $usermetadata_collection->findOne( array(
            'token' => $token,
            ) );
        
        $user_id = $user_metadata['user_id'];

        if ( !empty( $user_metadata )) {

            $story_count = $user_metadata['story_count'];
           
            $new_data = array('$set' => array('story_count' => $story_count + 1 ));
            $usermetadata_collection->update(array( 'user_id' => $user_id  ), $new_data);
        
            $new_story = array(
                'story_id' => $user_id . '_' . uniqid(),
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

        $token = $slim_input->token;
        $story_id = $slim_input->story_id;

        $user_metadata = $usermetadata_collection->findOne( array('token' => $token ) );
        if ( empty($user_metadata) ) {
            $response = array(
                'status' => 'the user token cannot be authenticated at this time.'
                );
            $app->response->headers->set('Content-Type', 'application/json' );
            $app->response->setStatus(400);
            return;
        }
        
        $user_id = $user_metadata['user_id'];

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
    function () use ( $app, $likes_collection, $usermetadata_collection ) {

        $slim_environment_vars = $app->environment;
        $slim_input = json_decode( $slim_environment_vars['slim.input'] );

        $token = $slim_input->token;
        $story_id = $slim_input->story_id;

        $user_metadata = $usermetadata_collection->findOne( array('token' => $token ) );
        if ( empty($user_metadata) ) {
            $response = array(
                'status' => 'the user token cannot be authenticated at this time.'
                );
            $app->response->headers->set('Content-Type', 'application/json' );
            $app->response->setStatus(400);
            return;
        }
        
        $user_id = $user_metadata['user_id'];

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
    function() use ( $app, $comments_collection, $usermetadata_collection ) {

        $slim_environment_vars = $app->environment;
        $slim_input = json_decode( $slim_environment_vars['slim.input'] );

        $token = $slim_input->token;
        $story_id = $slim_input->story_id;
        $comment = $slim_input->comment;

        $user_metadata = $usermetadata_collection->findOne( array('token' => $token ) );
        if ( empty($user_metadata) ) {
            $response = array(
                'status' => 'the user token cannot be authenticated at this time.'
                );
            $app->response->headers->set('Content-Type', 'application/json' );
            $app->response->setStatus(400);
            return;
        }
        
        $user_id = $user_metadata['user_id'];

        // Check if user has already submitted the same exact comment
        $user_comment_exist = $comments_collection->findOne( array( 'comment' => $comment, 'story_id' => $story_id, 'user_id' => $user_id ) ); 

        $new_user_comment_data = array(
            'story_id' => $story_id,
            'user_id' => $user_id,
            'comment' => $comment,
            'posted_time' => time()
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

function get_user_name($user_id, $usermetadata_collection) {
    $user_data = $usermetadata_collection->findOne(array( "user_id" => $user_id ) );
    return $user_data["user_name"];
}

function get_number_of_likes($story_id, $likes_collection){
    return $likes_collection->count(array("story_id" => $story_id));
}

// Run the slim application
$app->run();
