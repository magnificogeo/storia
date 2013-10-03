import tornado.web
import tornado.escape

import bcrypt
import urlparse
import datetime
import sys
# import httplib, httplib2
import json
import uuid
import urllib2

class BaseHandler(tornado.web.RequestHandler):
    @property
    def mysqldb(self):
        return self.application.mysqldb

    def prepare(self):

        if self.request.headers.get("Content-Type") == "application/json":
            self.json_args = tornado.escape.json_decode(self.request.body)

    def write_error(self, status_code, **kwargs):

        if 'chunk' in kwargs:
            self.write(kwargs['chunk'])

    def render_json(self, chunk):
        if isinstance(chunk, dict):
            dthandler = lambda obj: obj.isoformat() if isinstance(obj, datetime.datetime) else None
            chunk = json.dumps(chunk, default=dthandler)
            self.set_header("Content-Type", "application/json; charset=UTF-8")
        chunk = tornado.escape.utf8(chunk)
        self._write_buffer.append(chunk)

    def render(self, template_name, **kwargs):
        if (self.request.headers.get("Mime-Type") == "json"):
            self.render_json(kwargs)
        else:
            super(BaseHandler, self).render(template_name, **kwargs)



