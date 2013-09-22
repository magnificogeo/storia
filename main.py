#!/usr/bin/env python
import os
from flask import Flask
import logging


SECRET_KEY = os.urandom(16)
app = Flask('storia')

from dashboard import *
logging.info("Debug is %s", app.debug)
# the toolbar is only enabled in debug mode:
app.debug = True

# set a 'SECRET_KEY' to enable the Flask session cookies
app.config['SECRET_KEY'] = SECRET_KEY

if app.debug:
    import flask_debugtoolbar # pip install --user flask_debugtoolbar
    # toolbar = flask_debugtoolbar.DebugToolbarExtension(app)

if __name__ == '__main__':
    app.run()