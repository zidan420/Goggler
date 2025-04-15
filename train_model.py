import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.pipeline import make_pipeline
from sklearn.naive_bayes import MultinomialNB
import joblib
from urllib.parse import urlparse, parse_qs, unquote

# Load CSV
df = pd.read_csv('better_malicious_dataset.csv')

# Extract just the query part of the URL and decode it
def extract_features(url):
    query = urlparse(url).query
    decoded = unquote(unquote(query))  # decode twice
    return decoded

df['features'] = df['URL'].apply(extract_features)
X = df['features']
y = df['Attack_Type']

# Train model
pipeline = make_pipeline(
    TfidfVectorizer(analyzer='char_wb', ngram_range=(2,4)),
    MultinomialNB()
)

pipeline.fit(X, y)

# Save model
joblib.dump(pipeline, 'attack_detector_model.joblib')
print("Model trained and saved.")
