import json
import flask
import flask_restful
import flask.ext.pymongo
import flask_restful.reqparse
import werkzeug.routing
from werkzeug.wrappers import Response as ResponseBase
import xml_printer

class ListConverter(werkzeug.routing.BaseConverter):
    def to_python(self, value):
        return value.split(',')
    def to_url(self, values):
        return ','.join(werkzeug.routing.BaseConverter.to_url(value) for value in values)


def geo(value):
    data = value.split(",")
    if len(data) != 2:
        raise ValueError("Geo argument requires two values seperated with coma")

    data = map(lambda x: float(x), data)
    return data


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

    @staticmethod
    def out(func):
        def inner(*args, **kwargs):
            response = func(*args, **kwargs)

            if isinstance(response, ResponseBase):
                return response

            if kwargs["mime"] == "xml":
                response = Api.output_xml(response, None)
                response.headers["Content-Type"] = "application/xml"
            elif kwargs["mime"] == "json":
                response = Api.output_json(response, None)
                response.headers["Content-Type"] = "application/json"
            else:
                response = flask.make_response("Error 404",404)
            return response
        return inner


class Points(flask_restful.Resource):
    @Api.out
    def get(self, city, types, mime):
        if city not in mongo.db.collection_names():
            return flask.make_response("Error 404",404)

        args = parser.parse_args()
        fields = {"_id": 0}
        query = {}

        if len(types)!=1 or types[0] != "all":
            query["category"] = {"$in" : types}

        if args["geo"]!=None and args["range"]!=None:
            query["locWgs"] = {"$near":{"$geometry":{"type":"Point", "coordinates" : args["geo"]}, "$maxDistance":args["range"]}}

        res = [row for row in mongo.db[city].find(spec=query, fields={"_id": 0})]
        return res


app = flask.Flask(__name__)
app.config.from_object('config')
app.url_map.converters['list'] = ListConverter
mongo = flask.ext.pymongo.PyMongo(app)
parser = flask_restful.reqparse.RequestParser()
parser.add_argument('geo',type=geo)
parser.add_argument('range',type=int)
api = Api(app)
api.add_resource(Points, '/<string:city>/<list:types>.<string:mime>')

if __name__ == '__main__':
    app.run(debug=False)
