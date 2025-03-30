import face_recognition
import os
from flask import Flask, jsonify, request
import uuid_utils as uuid
import json
import numpy as np
from marshmallow import Schema, fields, ValidationError

app = Flask(__name__)

UPLOAD_FOLDER = './uploads'
if not os.path.exists(UPLOAD_FOLDER):
    os.makedirs(UPLOAD_FOLDER)

app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER


class EncodingSchema(Schema):
    encodings = fields.List(fields.String(), required=True)
    encoding = fields.String(required=True)


@app.route('/api/v1/check', methods=['POST'])
def check():
    if 'photo' not in request.files:
        return 'Нет файла в запросе', 400

    file = request.files['photo']

    filename = os.path.join(app.config['UPLOAD_FOLDER'], str(uuid.uuid4()) + '.' + file.filename.split('.')[-1])

    file.save(filename)

    face = face_recognition.load_image_file(filename)
    encoding = face_recognition.face_encodings(face)

    found = bool(len(encoding))

    os.remove(filename)

    return jsonify({
        "data": {
            "found": found,
            "encoding": json.dumps(encoding[0].tolist()) if found else None,
        },
        "message": "ok"
    })


@app.route('/api/v1/identify', methods=['POST'])
def identify():
    try:
        data = request.json

        EncodingSchema().load(data)

        encodings = np.array(list(map(np.array, list(map(json.loads, data["encodings"])))))
        result = face_recognition.compare_faces(encodings, np.array(json.loads(data["encoding"])), 0.4)

        try:
            face = json.dumps(encodings[result.index(True)].tolist())
        except ValueError:
            face = None

        return jsonify({
            "data": {
                "found": face is not None,
                "encoding": face
            },
            "message": "ok"
        })
    except ValidationError as err:
        return jsonify({
            "error": err.messages,
            "message": "error"
        }), 400

    except Exception as e:
        return jsonify({
            "error": str(e),
            "message": "error"
        }), 400


if __name__ == '__main__':
    app.run(debug=True)
