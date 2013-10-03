from handlers.base import BaseHandler

class FeedsHandler(BaseHandler):

    def get(self):
        stories = self.mysqldb.query("SELECT * FROM story")
        for story in stories:
            images = self.mysqldb.query("""SELECT image.*, user.user_name, user.real_name, user.profile_picture_url FROM image
                                            LEFT JOIN story_image ON image.id = story_image.image_id
                                            LEFT JOIN story ON story.id = story_image.story_id
                                            LEFT JOIN user ON user.id = story.user_id
                                            WHERE story.id = %s""", story.id)
            story['images'] = images
        posts = dict()
        posts['feeds'] = stories
        return self.write(posts)

    def post(self):
        pass
