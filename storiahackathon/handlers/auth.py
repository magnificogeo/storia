from handlers.base import BaseHandler

class LoginHandler(BaseHandler):

    def post(self):
        user_name = self.get_argument("user_name")
        password = self.get_argument("password")
        print user_name
        print password
        user = self.mysqldb.get("""SELECT * FROM user WHERE user_name=%s AND password=%s""", user_name, password)
        if user is None:
            errors = dict()
            errors['login_error'] = 'User name & Password do not match.'
            return self.send_error(400, chunk={'Status' : 'Error', 'Errors' : errors })
        else:
            return self.write({'Status': 'OK'})


class SignupHandler(BaseHandler):

    def post(self):
        pass


class HelloHandler(BaseHandler):

    def get(self):
        self.write('hello')