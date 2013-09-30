#!/usr/bin/env python
from main import app
import flask
print "hi there"


@app.route('/')
def hello_world():
    return flask.render_template("hello.html", name="you")