from handlers.base import BaseHandler

class PostsHandler(BaseHandler):

    def get(self):
        pass

    def post(self):
        url1 = self.get_argument("url1", None)
        url2 = self.get_argument("url2", None)
        url3 = self.get_argument("url3", None)
        url4 = self.get_argument("url4", None)
        url5 = self.get_argument("url5", None)
        caption1 = self.get_argument("caption1", None)
        caption2 = self.get_argument("caption2", None)
        caption3 = self.get_argument("caption3", None)
        caption4 = self.get_argument("caption4", None)
        caption5 = self.get_argument("caption5", None)

        ids = list()
        story_id = 0
        if url1 is not None:
            story_id = self.mysqldb.execute("INSERT INTO story(main_image_url, title, user_id) VALUES (%s, %s, %s)", url1, caption1, 1)
        if url2 is not None:
            ids.append(self.mysqldb.execute("INSERT INTO image(url, caption) VALUES (%s, %s)", url2, caption2))
        if url3 is not None:
            ids.append(self.mysqldb.execute("INSERT INTO image(url, caption) VALUES (%s, %s)", url3, caption3))
        if url4 is not None:
            ids.append(self.mysqldb.execute("INSERT INTO image(url, caption) VALUES (%s, %s)", url4, caption4))
        if url5 is not None:
            ids.append(self.mysqldb.execute("INSERT INTO image(url, caption) VALUES (%s, %s)", url5, caption5))

        for iid in ids:
            self.mysqldb.execute("INSERT INTO story_image(story_id, image_id) VALUES (%s, %s)", story_id, iid)
        print story_id
        print ids

        return self.write({"Status": "OK"})
