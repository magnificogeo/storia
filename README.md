Storia
=======

### Development Stacks
- Flask
- Bootstrap
- Jquery

### Local Environment Setup Guide
- pip install virtualenv
- pip install virtualenvwrapper
- mkvirtualenv storia
- put these into .bash_profile

        export WORKON_HOME=$HOME/.virtualenvs
        export PROJECT_HOME=$HOME/Development
        source /usr/local/share/python/virtualenvwrapper.sh

- source .bash_profile
- cd to storia directory
- pip install -r requirement.txt

### Start up local server
- python server.py
- access http://127.0.0.1:5000/ from browser!