from handlers.base import BaseHandler

class ProfileHandler(BaseHandler):

    def get(self):
        user = self.mysqldb.get("""SELECT * FROM user WHERE id = 1""")
        stories = self.mysqldb.query("SELECT * FROM story WHERE user_id = 1")
        for story in stories:
            images = self.mysqldb.query("""SELECT image.* FROM image, story_image
                                        WHERE image.id=story_image.id AND story_image.story_id = %s""", story.id)
            story['images'] = images
        user['stories'] = stories
        return self.write(user)

    def post(self):
        pass
