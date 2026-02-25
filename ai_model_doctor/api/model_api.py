# api/model_api.py

from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
import joblib
import json
from datetime import datetime, timedelta

app = Flask(__name__)
CORS(app)  # Permet les requêtes depuis Symfony

# Charger les modèles
print("🔄 Chargement des modèles...")
activity_predictor = joblib.load('models/saved_models/activity_predictor.pkl')
scaler = joblib.load('models/saved_models/scaler.pkl')
label_encoders = joblib.load('models/saved_models/label_encoders.pkl')
clustering_model = joblib.load('models/saved_models/clustering_model.pkl')
clustering_scaler = joblib.load('models/saved_models/clustering_scaler.pkl')

# Charger les profils des médecins
doctor_profiles = pd.read_csv('models/saved_models/doctor_profiles.csv')


def _predict_from_features(features, is_weekend=0):
    avg_time = float(features.get('avg_consultation_time', 30))
    popularity = float(features.get('popularity_score', 0.5))
    actual = float(features.get('actual_consultations', 0))
    values = [avg_time, popularity, actual, float(is_weekend)]
    features_scaled = scaler.transform([values])
    pred = activity_predictor.predict(features_scaled)[0]
    return int(pred)
print("✅ Modèles chargés avec succès!")

@app.route('/api/predict/doctor/<int:doctor_id>', methods=['GET'])
def predict_doctor_activity(doctor_id):
    """Prédit l'activité d'un médecin spécifique"""
    
    try:
        # Récupérer les données du médecin
        doctor_data = doctor_profiles[doctor_profiles['doctor_id'] == doctor_id]
        
        if doctor_data.empty:
            return jsonify({'error': 'Médecin non trouvé'}), 404
        
        # Préparer les features pour la prédiction
        # (À adapter selon vos features réelles)
        features = doctor_data[[
            'avg_consultation_time', 'popularity_score',
            'actual_consultations', 'is_weekend'
        ]].values[0]
        
        # Prédire pour les 7 prochains jours
        predictions = []
        for day in range(7):
            # Ajuster les features selon le jour
            day_features = features.copy()
            day_features[3] = 1 if day >= 5 else 0  # weekend
            
            # Scaling
            features_scaled = scaler.transform([day_features])
            
            # Prédiction
            pred = activity_predictor.predict(features_scaled)[0]
            
            predictions.append({
                'day': (datetime.now() + timedelta(days=day)).strftime('%Y-%m-%d'),
                'day_name': (datetime.now() + timedelta(days=day)).strftime('%A'),
                'predicted_consultations': int(pred)
            })
        
        return jsonify({
            'doctor_id': doctor_id,
            'predictions': predictions,
            'confidence': 0.85  # Score de confiance
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/api/predict/doctor-features', methods=['POST'])
def predict_doctor_activity_from_features():
    """Pr??dit l'activit?? ?? partir de features fournies par le backend."""
    try:
        data = request.get_json(silent=True) or {}
        doctor_id = data.get('doctor_id')
        features = data.get('features') or {}

        if not doctor_id:
            return jsonify({'error': 'doctor_id manquant'}), 400

        predictions = []
        for day in range(7):
            is_weekend = 1 if day >= 5 else 0
            pred = _predict_from_features(features, is_weekend=is_weekend)
            predictions.append({
                'day': (datetime.now() + timedelta(days=day)).strftime('%Y-%m-%d'),
                'day_name': (datetime.now() + timedelta(days=day)).strftime('%A'),
                'predicted_consultations': int(pred)
            })

        return jsonify({
            'doctor_id': doctor_id,
            'predictions': predictions,
            'confidence': 0.85
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/predict/all', methods=['GET'])
def predict_all_doctors():
    """Prédit l'activité de tous les médecins"""
    
    try:
        all_predictions = []
        
        for _, doctor in doctor_profiles.iterrows():
            # Prédiction simple
            features = doctor[[
                'avg_consultation_time', 'popularity_score',
                'actual_consultations', 'is_weekend'
            ]].values.reshape(1, -1)
            
            features_scaled = scaler.transform(features)
            pred = activity_predictor.predict(features_scaled)[0]
            
            all_predictions.append({
                'doctor_id': int(doctor['doctor_id']),
                'specialty': doctor['specialty'],
                'predicted_daily_avg': int(pred),
                'cluster': int(doctor.get('cluster', 0))
            })
        
        return jsonify({
            'total_doctors': len(all_predictions),
            'predictions': all_predictions,
            'generated_at': datetime.now().isoformat()
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/api/predict/batch', methods=['POST'])
def predict_batch():
    """Pr??dit l'activit?? journali??re moyenne pour une liste de m??decins."""
    try:
        data = request.get_json(silent=True) or {}
        doctors = data.get('doctors') or []

        all_predictions = []
        for doctor in doctors:
            doctor_id = doctor.get('doctor_id')
            features = doctor.get('features') or {}
            specialty = doctor.get('specialty') or ''

            pred = _predict_from_features(features, is_weekend=0)
            all_predictions.append({
                'doctor_id': doctor_id,
                'specialty': specialty,
                'predicted_daily_avg': int(pred),
                'cluster': 0
            })

        return jsonify({
            'total_doctors': len(all_predictions),
            'predictions': all_predictions,
            'generated_at': datetime.now().isoformat()
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/cluster/<int:cluster_id>', methods=['GET'])
def get_cluster_info(cluster_id):
    """Récupère les informations sur un cluster de médecins"""
    
    try:
        cluster_doctors = doctor_profiles[doctor_profiles['cluster'] == cluster_id]
        
        if cluster_doctors.empty:
            return jsonify({'error': 'Cluster non trouvé'}), 404
        
        specialties = cluster_doctors['specialty'].value_counts().to_dict()
        
        return jsonify({
            'cluster_id': cluster_id,
            'size': len(cluster_doctors),
            'specialties': specialties,
            'avg_consultations': float(cluster_doctors['actual_consultations'].mean()),
            'doctors': cluster_doctors[['doctor_id', 'specialty']].to_dict('records')
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/recommendations/doctor/<int:doctor_id>', methods=['GET'])
def get_doctor_recommendations(doctor_id):
    """Génère des recommandations pour un médecin"""
    
    try:
        doctor_data = doctor_profiles[doctor_profiles['doctor_id'] == doctor_id]
        
        if doctor_data.empty:
            return jsonify({'error': 'Médecin non trouvé'}), 404
        
        doctor = doctor_data.iloc[0]
        cluster = int(doctor['cluster'])
        
        # Recommandations basées sur le cluster
        recommendations = []
        
        if doctor['popularity_score'] < 0.5:
            recommendations.append({
                'type': 'popularity',
                'title': 'Augmenter la visibilité',
                'description': 'Envisagez d\'activer les consultations en ligne pour attirer plus de patients'
            })
        
        if doctor['emergencies'] > doctor_profiles['emergencies'].mean():
            recommendations.append({
                'type': 'emergency',
                'title': 'Optimiser les urgences',
                'description': 'Vous avez plus d\'urgences que la moyenne. Prévoyez des créneaux dédiés.'
            })
        
        cluster_avg = doctor_profiles[doctor_profiles['cluster'] == cluster]['actual_consultations'].mean()
        if doctor['actual_consultations'] < cluster_avg * 0.8:
            recommendations.append({
                'type': 'activity',
                'title': 'Augmenter l\'activité',
                'description': f'Votre activité est inférieure à la moyenne de votre cluster. Essayez d\'ouvrir plus de créneaux en soirée.'
            })
        
        # Ajouter une recommandation par défaut si nécessaire
        if not recommendations:
            recommendations.append({
                'type': 'general',
                'title': 'Maintenir le cap',
                'description': 'Votre activité est dans la moyenne. Continuez ainsi !'
            })
        
        return jsonify({
            'doctor_id': doctor_id,
            'cluster': cluster,
            'recommendations': recommendations
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    """Vérifie que l'API fonctionne"""
    return jsonify({
        'status': 'ok',
        'message': 'API de prédiction médicale opérationnelle',
        'models_loaded': True,
        'timestamp': datetime.now().isoformat()
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)