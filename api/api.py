import json
import flask
import flask_restful
import flask.ext.pymongo
import xml_printer

class Api(flask_restful.Api):
    def __init__(self, *args, **kwargs):
        super(Api, self).__init__(*args, **kwargs)
        self.representations = {
            'application/json': self.output_json,
            'application/xml': self.output_xml
        }

    @staticmethod
    def output_json(data, code, headers=None):
        resp = flask.make_response(json.dumps(data), code)
        resp.headers.extend(headers or {})
        return resp

    @staticmethod
    def output_xml(data, code, headers=None):
        text = str(xml_printer.XmlPrinter("Points", data))
        resp = flask.make_response(text, code)
        resp.headers.extend(headers or {})
        return resp

app = flask.Flask(__name__)
app.config.from_object('config')
mongo = flask.ext.pymongo.PyMongo(app)
api = Api(app)

class Test(flask_restful.Resource):
    def get(self):
        res = [row for row in mongo.db.warszawa.find(fields={"_id": 0})]
        res = [res[0]]
        return res

#response = flask.make_response(something)
#response.headers['content-type'] = 'application/octet-stream'
#return response

api.add_resource(Test, '/')

if __name__ == '__main__':
    app.run(debug=True)
