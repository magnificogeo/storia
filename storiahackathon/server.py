#!/usr/bin/env python

import tornado.auth
import torndb
import tornado.httpserver
import tornado.ioloop
import tornado.options
import tornado.web
from tornado.options import define, options
import tornado.gen
import tornado.wsgi
from tornado import autoreload
import os.path


# app specific settings
import urls

from settings import *

define("mysql_database", default=DB_NAME, help="database name")
define("mysql_user", default=DB_USER, help="database user")
define("mysql_password", default=DB_PASSWORD, help="database password")
define("mysql_host", default=DB_HOST + ":" + DB_PORT, help="database host:port")
define("port", default=None, help="run on the given port", type=int)

# class Application(tornado.web.Application):
class Application(tornado.wsgi.WSGIApplication):
    def __init__(self):

        template_path = "resources/www/templates"
        static_path = "resources/www/static"
        if options.port is None:
            options.port = WWW_PORT

        handlers = urls.handlers

        settings = dict(
            title=u"Storia",
            template_path=os.path.join(os.path.dirname(__file__), template_path),
            static_path=os.path.join(os.path.dirname(__file__), static_path),
            xsrf_cookies=False,
            cookie_secret=WWW_COOKIE_SECRET,
            login_url="/login",
            autoescape=None,
            debug=DEBUG_VALUE
        )

        tornado.wsgi.WSGIApplication.__init__(self,handlers,**settings)

        # Global connections accessible to all handlers
        self.mysqldb = torndb.Connection(
            host=options.mysql_host, database=options.mysql_database,
            user=options.mysql_user, password=options.mysql_password)


def main_wsgi():
    from tornado.httpserver import HTTPServer
    from tornado.wsgi import WSGIContainer
    tornado.options.parse_command_line()
    http_server = HTTPServer(WSGIContainer(Application()))
    http_server.listen(options.port)
    print('Listening at port %s (WSGIContainer)' % options.port)
    ioloop = tornado.ioloop.IOLoop.instance()
    autoreload.start(ioloop)
    ioloop.start()

if __name__ == "__main__":
    main_wsgi()



