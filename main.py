#!/usr/bin/env python
import os
import flask
from flask_debugtoolbar import DebugToolbarExtension
import logging

SECRET_KEY = os.urandom(16)
app = flask.Flask('storia')

# ::TODO:: this only works if there is one instance
SECRET_KEY = os.urandom(16)
app.config['SECRET_KEY'] = SECRET_KEY

app.debug = True
if app.debug:
    toolbar = DebugToolbarExtension(app)

@app.route('/')
def index():
    return flask.render_template("index.html")

if __name__ == '__main__':
    app.run()

