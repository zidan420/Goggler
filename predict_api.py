from flask import Flask, request, jsonify
import joblib
from urllib.parse import urlparse, parse_qs, unquote

app = Flask(__name__)
model = joblib.load('attack_detector_model.joblib')

def extract_features(url):
    query = urlparse(url).query
    decoded = unquote(unquote(query))  # decode twice
    return decoded

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    url = data.get('url')
    if not url:
        return jsonify({'error': 'URL missing'}), 400

    features = extract_features(url)
    prediction = model.predict([features])[0]
    return jsonify({
        'malicious': True,
        'attack_type': prediction
    })

if __name__ == '__main__':
    app.run(port=5000)
