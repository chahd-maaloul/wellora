# models/train_patient_monitoring.py

import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier, GradientBoostingRegressor
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score, mean_absolute_error
import joblib
from datetime import datetime, timedelta   

class DoctorPatientMonitor:
    """
    Modèle de suivi des patients pour chaque médecin
    """
    
    def __init__(self):
        self.risk_model = None  # Prédiction des patients à risque
        self.adherence_model = None  # Prédiction de l'observance
        self.scaler = StandardScaler()
        
    def load_data(self):
        """Charge les données patients et consultations"""
        
        patients_df = pd.read_csv('data/patients.csv')
        vital_df = pd.read_csv('data/vital_signs.csv')
        consultations_df = pd.read_csv('data/synthetic_consultations.csv')
        
        return patients_df, vital_df, consultations_df
    
    def prepare_doctor_patient_features(self, patients_df, vital_df, consultations_df):
        """Prépare les features par médecin pour ses patients"""
        
        doctor_patients_data = []
        
        # Pour chaque médecin
        for doctor_id in consultations_df['doctor_id'].unique():
            
            # Ses patients
            doctor_patients = patients_df[patients_df['doctor_id'] == doctor_id]
            
            if doctor_patients.empty:
                continue
            
            # Statistiques globales sur ses patients
            total_patients = len(doctor_patients)
            avg_age = doctor_patients['age'].mean()
            avg_observance = doctor_patients['observance_habituelle'].mean()
            
            # Conditions les plus fréquentes chez ses patients
            all_conditions = []
            for conditions in doctor_patients['conditions']:
                all_conditions.extend(eval(conditions) if isinstance(conditions, str) else conditions)
            
            from collections import Counter
            top_conditions = Counter(all_conditions).most_common(3)
            
            # Données vitales récentes de ses patients
            patient_ids = doctor_patients['patient_id'].tolist()
            recent_vitals = vital_df[vital_df['patient_id'].isin(patient_ids)]
            
            if recent_vitals.empty:
                continue
            
            # Moyennes des signes vitaux
            avg_tension = recent_vitals['tension_systolique'].mean()
            avg_glycemie = recent_vitals['glycemie'].mean()
            avg_poids = recent_vitals['poids'].mean()
            
            # Identification des patients à risque
            patients_at_risk = []
            for _, patient in doctor_patients.iterrows():
                patient_vitals = vital_df[vital_df['patient_id'] == patient['patient_id']].sort_values('date_visite')
                
                if len(patient_vitals) >= 2:
                    last = patient_vitals.iloc[-1]
                    prev = patient_vitals.iloc[-2]
                    
                    # Détection de détérioration
                    risk_score = 0
                    if last['tension_systolique'] > prev['tension_systolique'] * 1.1:
                        risk_score += 1
                    if last['glycemie'] > prev['glycemie'] * 1.15:
                        risk_score += 1
                    if last['observance_rapportee'] < prev['observance_rapportee'] * 0.8:
                        risk_score += 1
                    
                    if risk_score >= 2:
                        patients_at_risk.append({
                            'patient_id': int(patient['patient_id']),
                            'risk_score': risk_score,
                            'name': f"Patient_{patient['patient_id']}",
                            'age': patient['age'],
                            'condition': top_conditions[0][0] if top_conditions else 'Non spécifié',
                            'last_visit': last['date_visite'],
                            'alert': self._generate_alert(risk_score, last)
                        })
            
            # Features pour le modèle
            feature_vector = [
                total_patients / 100,  # normalisé
                avg_age / 100,
                avg_observance / 100,
                avg_tension / 200,
                avg_glycemie / 200,
                avg_poids / 100,
                len(patients_at_risk) / max(1, total_patients),  # proportion à risque
                len([p for p in doctor_patients.itertuples() if 'diabète_type2' in str(p.conditions)]) / max(1, total_patients),
                len([p for p in doctor_patients.itertuples() if 'hypertension' in str(p.conditions)]) / max(1, total_patients),
            ]
            
            # Target: amélioration de l'état global des patients (0-100)
            target = 100 - (len(patients_at_risk) / max(1, total_patients) * 100)
            
            doctor_patients_data.append({
                'doctor_id': doctor_id,
                'features': feature_vector,
                'target': target,
                'total_patients': total_patients,
                'patients_at_risk': patients_at_risk,
                'avg_metrics': {
                    'avg_age': round(avg_age, 1),
                    'avg_observance': round(avg_observance, 1),
                    'avg_tension': round(avg_tension, 1),
                    'avg_glycemie': round(avg_glycemie, 1),
                },
                'top_conditions': top_conditions
            })
        
        return doctor_patients_data
    
    def _generate_alert(self, risk_score, last_vitals):
        """Génère une alerte basée sur le score de risque"""
        
        if risk_score >= 3:
            return {
                'level': 'high',
                'message': 'Détérioration significative détectée - Consultation urgente recommandée',
                'color': 'red'
            }
        elif risk_score == 2:
            return {
                'level': 'medium',
                'message': 'Surveillance renforcée nécessaire',
                'color': 'orange'
            }
        else:
            return {
                'level': 'low',
                'message': 'Légère variation à surveiller',
                'color': 'yellow'
            }
    
    def train_models(self, doctor_data):
        """Entraîne les modèles de prédiction pour le suivi patients"""
        
        X = np.array([d['features'] for d in doctor_data])
        y = np.array([d['target'] for d in doctor_data])
        
        print(f"📊 Données: {len(X)} médecins, {X.shape[1]} features")
        
        # Split
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42
        )
        
        # Scaling
        X_train_scaled = self.scaler.fit_transform(X_train)
        X_test_scaled = self.scaler.transform(X_test)
        
        # Modèle de prédiction de l'état global des patients
        print("\n🏥 Entraînement du modèle de suivi patients...")
        self.risk_model = GradientBoostingRegressor(
            n_estimators=100,
            max_depth=6,
            learning_rate=0.1,
            random_state=42
        )
        self.risk_model.fit(X_train_scaled, y_train)
        
        # Évaluation
        y_pred = self.risk_model.predict(X_test_scaled)
        mae = mean_absolute_error(y_test, y_pred)
        print(f"   MAE: {mae:.2f}% (erreur sur l'état de santé global)")
        
        # Feature importance
        feature_names = [
            'nb_patients', 'age_moyen', 'observance_moy', 
            'tension_moy', 'glycemie_moy', 'poids_moy',
            'prop_risque', 'prop_diabete', 'prop_hypertension'
        ]
        importances = self.risk_model.feature_importances_
        indices = np.argsort(importances)[::-1]
        
        print("\n📈 Facteurs influençant la santé des patients:")
        for i, idx in enumerate(indices[:5]):
            print(f"   {i+1}. {feature_names[idx]}: {importances[idx]:.3f}")
        
        return X_test, y_test
    
    def predict_doctor_patient_health(self, doctor_features):
        """Prédit l'évolution de la santé des patients pour un médecin"""
        
        features_scaled = self.scaler.transform([doctor_features])
        health_score = self.risk_model.predict(features_scaled)[0]
        
        return {
            'global_health_score': round(health_score, 1),
            'status': 'Bon' if health_score > 80 else 'Moyen' if health_score > 60 else 'À surveiller',
            'trend': 'Amélioration' if health_score > 75 else 'Stable' if health_score > 50 else 'Détérioration'
        }
    
    def save_models(self):
        """Sauvegarde les modèles"""
        
        joblib.dump(self.risk_model, 'models/saved_models/doctor_patient_monitor.pkl')
        joblib.dump(self.scaler, 'models/saved_models/doctor_patient_scaler.pkl')
        print("\n💾 Modèles de suivi patients sauvegardés")

# Entraînement
if __name__ == "__main__":
    print("=" * 50)
    print("👨‍⚕️ ENTRAÎNEMENT SUIVI PATIENTS PAR MÉDECIN")
    print("=" * 50)
    
    monitor = DoctorPatientMonitor()
    patients_df, vital_df, consultations_df = monitor.load_data()
    doctor_data = monitor.prepare_doctor_patient_features(patients_df, vital_df, consultations_df)
    monitor.train_models(doctor_data)
    monitor.save_models()