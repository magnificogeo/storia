from handlers.auth import LoginHandler, SignupHandler, HelloHandler
from handlers.feeds import FeedsHandler
from handlers.profile import ProfileHandler
from handlers.posts import PostsHandler

handlers = [
      # landing pages
      (r"/api/login", LoginHandler),
      (r"/api/signup", SignupHandler),
      (r"/api/feeds", FeedsHandler),
      (r"/api/profile", ProfileHandler),
      (r"/api/posts", PostsHandler),
      (r"/", HelloHandler),
]

