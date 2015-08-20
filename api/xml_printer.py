import xml.dom.minidom

class XmlPrinter:
    def __init__(self, tagName, data):
        self.doc = xml.dom.minidom.Document()
        root = self.doc.createElement(tagName)
        self.doc.appendChild(root)
        self.build(root, data)

    def build(self, father, data):
        if type(data) == dict:
            for k in data:
                tag = self.doc.createElement(k)
                father.appendChild(tag)
                self.build(tag, data[k])
        elif type(data) == list:
            tagName = father.tagName[:-1]
            for l in data:
                tag = self.doc.createElement(tagName)
                self.build(tag, l)
                father.appendChild(tag)
        elif type(data) == unicode:
            tag = self.doc.createTextNode(data)
            father.appendChild(tag)
        else:
            text = str(data)
            tag = self.doc.createTextNode(text)
            father.appendChild(tag)

    def __str__(self):
        return self.doc.toprettyxml(indent="    ", encoding="utf-8")
