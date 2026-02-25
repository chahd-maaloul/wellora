# models/train_profit_model.py

import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestRegressor, GradientBoostingRegressor
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import mean_absolute_error, r2_score
import joblib
from datetime import datetime, timedelta

class DoctorProfitPredictor:
    """
    Modèle de prédiction des revenus pour chaque médecin
    """
    
    def __init__(self):
        self.revenue_model = None
        self.scaler = StandardScaler()
        
        # Tarifs par spécialité (en TND)
        self.pricing = {
            'Généraliste': 50,
            'Cardiologue': 80,
            'Dermatologue': 70,
            'Pédiatre': 60,
            'Gynécologue': 75,
            'Neurologue': 90,
            'Ophtalmologue': 70,
            'ORL': 65,
            'Rhumatologue': 80,
            'Psychiatre': 85
        }
        
    def load_data(self):
        """Charge les données de consultations et patients"""
        
        consultations_df = pd.read_csv('data/synthetic_consultations.csv')
        patients_df = pd.read_csv('data/patients.csv')
        
        return consultations_df, patients_df
    
    def prepare_doctor_profit_features(self, consultations_df, patients_df):
        """Prépare les features de profit pour chaque médecin"""
        
        doctor_profits = []
        
        for doctor_id in consultations_df['doctor_id'].unique():
            doctor_consults = consultations_df[consultations_df['doctor_id'] == doctor_id]
            specialty = doctor_consults['specialty'].iloc[0]
            price_per_consult = self.pricing.get(specialty, 60)
            
            # Agrégation par mois
            consultations_df['date'] = pd.to_datetime(consultations_df['date'])
            doctor_consults['date'] = pd.to_datetime(doctor_consults['date'])
            
            # Mois disponibles
            months = doctor_consults['date'].dt.to_period('M').unique()
            
            monthly_revenues = []
            for month in months:
                month_data = doctor_consults[doctor_consults['date'].dt.to_period('M') == month]
                n_consults = month_data['actual_consultations'].sum()
                revenue = n_consults * price_per_consult
                monthly_revenues.append(revenue)
            
            # Calcul des métriques
            avg_monthly_revenue = np.mean(monthly_revenues) if monthly_revenues else 0
            revenue_std = np.std(monthly_revenues) if monthly_revenues else 0
            revenue_trend = self._calculate_trend(monthly_revenues)
            
            # Types de consultations
            avg_new = doctor_consults['new_patients'].mean()
            avg_follow = doctor_consults['follow_up'].mean()
            avg_emergency = doctor_consults['emergencies'].mean()
            
            # Distribution horaire
            morning_pct = (doctor_consults['hour_8_10'].mean() + doctor_consults['hour_10_12'].mean()) / 16
            evening_pct = (doctor_consults['hour_14_16'].mean() + doctor_consults['hour_16_18'].mean()) / 16
            
            # Patients de ce médecin
            doctor_patients = patients_df[patients_df['doctor_id'] == doctor_id]
            n_patients = len(doctor_patients)
            
            # Features pour le modèle
            feature_vector = [
                avg_monthly_revenue / 1000,  # normalisé
                revenue_std / 1000,
                revenue_trend,
                avg_new,
                avg_follow,
                avg_emergency,
                morning_pct,
                evening_pct,
                n_patients / 100,
                price_per_consult / 100,
            ]
            
            # Target: revenu du prochain mois
            next_month_revenue = self._predict_next_month(monthly_revenues) if monthly_revenues else avg_monthly_revenue
            
            doctor_profits.append({
                'doctor_id': doctor_id,
                'specialty': specialty,
                'features': feature_vector,
                'current_monthly': avg_monthly_revenue,
                'next_month': next_month_revenue,
                'stats': {
                    'avg_monthly': round(avg_monthly_revenue, 0),
                    'min_monthly': round(min(monthly_revenues), 0) if monthly_revenues else 0,
                    'max_monthly': round(max(monthly_revenues), 0) if monthly_revenues else 0,
                    'volatility': round(revenue_std / avg_monthly_revenue * 100, 1) if avg_monthly_revenue > 0 else 0
                }
            })
        
        return doctor_profits
    
    def _calculate_trend(self, values):
        """Calcule la tendance sur les valeurs"""
        if len(values) < 2:
            return 0
        x = np.arange(len(values))
        z = np.polyfit(x, values, 1)
        return z[0] / np.mean(values) if np.mean(values) > 0 else 0
    
    def _predict_next_month(self, monthly_revenues):
        """Prédiction simple du prochain mois"""
        if len(monthly_revenues) < 3:
            return monthly_revenues[-1] if monthly_revenues else 0
        
        # Moyenne pondérée (plus de poids aux mois récents)
        weights = np.exp(np.linspace(0, 1, len(monthly_revenues)))
        weights /= weights.sum()
        weighted_avg = np.average(monthly_revenues, weights=weights)
        
        # Ajustement par la tendance
        trend = self._calculate_trend(monthly_revenues)
        prediction = weighted_avg * (1 + trend)
        
        return max(0, prediction)
    
    def train_model(self, doctor_data):
        """Entraîne le modèle de prédiction de profit"""
        
        X = np.array([d['features'] for d in doctor_data])
        y = np.array([d['next_month'] for d in doctor_data])
        
        print(f"📊 Données: {len(X)} médecins, {X.shape[1]} features")
        print(f"   Revenu moyen: {np.mean(y):.0f} TND/mois")
        print(f"   Min: {np.min(y):.0f}, Max: {np.max(y):.0f}")
        
        # Split
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42
        )
        
        # Scaling
        X_train_scaled = self.scaler.fit_transform(X_train)
        X_test_scaled = self.scaler.transform(X_test)
        
        # Modèle
        print("\n💰 Entraînement du modèle de profit...")
        self.revenue_model = RandomForestRegressor(
            n_estimators=200,
            max_depth=10,
            random_state=42,
            n_jobs=-1
        )
        self.revenue_model.fit(X_train_scaled, y_train)
        
        # Évaluation
        y_pred = self.revenue_model.predict(X_test_scaled)
        mae = mean_absolute_error(y_test, y_pred)
        r2 = r2_score(y_test, y_pred)
        
        print(f"\n📊 PERFORMANCE:")
        print(f"   MAE: {mae:.0f} TND/mois")
        print(f"   R²: {r2:.3f}")
        print(f"   Erreur relative: {mae/np.mean(y_test)*100:.1f}%")
        
        # Validation croisée
        cv_scores = cross_val_score(self.revenue_model, X_train_scaled, y_train, 
                                    cv=5, scoring='r2')
        print(f"   CV R²: {cv_scores.mean():.3f}")
        
        # Feature importance
        feature_names = [
            'revenu_moyen', 'volatilité', 'tendance',
            'nouveaux_patients', 'suivis', 'urgences',
            'matin_pct', 'soir_pct', 'nb_patients', 'prix_consult'
        ]
        importances = self.revenue_model.feature_importances_
        indices = np.argsort(importances)[::-1]
        
        print("\n📈 Facteurs influençant le revenu:")
        for i, idx in enumerate(indices[:5]):
            print(f"   {i+1}. {feature_names[idx]}: {importances[idx]:.3f}")
        
        return X_test, y_test
    
    def predict_doctor_profit(self, doctor_id, doctor_data):
        """Prédit le profit pour un médecin spécifique"""
        
        doctor_info = next((d for d in doctor_data if d['doctor_id'] == doctor_id), None)
        
        if not doctor_info:
            return None
        
        features = doctor_info['features']
        features_scaled = self.scaler.transform([features])
        
        # Prédiction du mois prochain
        next_month_pred = self.revenue_model.predict(features_scaled)[0]
        
        # Projections
        current = doctor_info['current_monthly']
        
        # Recommandations personnalisées
        recommendations = self._generate_profit_recommendations(doctor_info, doctor_data)
        
        # Comparaison avec la moyenne des confrères
        same_specialty = [d for d in doctor_data if d['specialty'] == doctor_info['specialty']]
        avg_specialty = np.mean([d['current_monthly'] for d in same_specialty]) if same_specialty else current
        
        return {
            'doctor_id': doctor_id,
            'specialty': doctor_info['specialty'],
            'current': {
                'monthly': round(current, 0),
                'yearly': round(current * 12, 0),
                'stats': doctor_info['stats']
            },
            'predictions': {
                'next_month': round(next_month_pred, 0),
                'next_quarter': round(next_month_pred * 3, 0),
                'next_year': round(next_month_pred * 12, 0),
                'growth': round((next_month_pred - current) / current * 100, 1) if current > 0 else 0
            },
            'comparison': {
                'vs_specialty': round((current - avg_specialty) / avg_specialty * 100, 1) if avg_specialty > 0 else 0,
                'specialty_avg': round(avg_specialty, 0)
            },
            'recommendations': recommendations
        }
    
    def _generate_profit_recommendations(self, doctor_info, all_doctors):
        """Génère des recommandations personnalisées pour augmenter le profit"""
        
        recommendations = []
        
        # Analyser les faiblesses
        if doctor_info['stats']['volatility'] > 30:
            recommendations.append({
                'type': 'stability',
                'title': 'Stabiliser vos revenus',
                'description': 'Vos revenus sont très variables. Essayez de fidéliser vos patients avec des abonnements de suivi.',
                'potential_gain': int(doctor_info['current_monthly'] * 0.1)
            })
        
        # Vérifier la proportion de nouveaux patients
        features = doctor_info['features']
        if features[3] < 3:  # nouveaux patients
            recommendations.append({
                'type': 'acquisition',
                'title': 'Attirer de nouveaux patients',
                'description': 'Vous avez peu de nouveaux patients. Activez les consultations en ligne pour plus de visibilité.',
                'potential_gain': int(doctor_info['current_monthly'] * 0.15)
            })
        
        # Optimisation des horaires
        if features[6] < 0.4:  # matin
            recommendations.append({
                'type': 'schedule',
                'title': 'Optimiser vos horaires du matin',
                'description': 'Ouvrez plus de créneaux entre 8h et 10h (forte demande).',
                'potential_gain': int(doctor_info['current_monthly'] * 0.08)
            })
        
        if features[7] < 0.3:  # soir
            recommendations.append({
                'type': 'schedule',
                'title': 'Développer les consultations en soirée',
                'description': 'Proposez des créneaux après 17h pour les actifs.',
                'potential_gain': int(doctor_info['current_monthly'] * 0.12)
            })
        
        # Comparaison avec la moyenne
        same_specialty = [d for d in all_doctors if d['specialty'] == doctor_info['specialty']]
        avg_new_patients = np.mean([d['features'][3] for d in same_specialty])
        
        if features[3] < avg_new_patients * 0.7:
            recommendations.append({
                'type': 'marketing',
                'title': 'Augmenter votre visibilité',
                'description': 'Vous attirez moins de nouveaux patients que la moyenne. Mettez à jour votre profil.',
                'potential_gain': int(doctor_info['current_monthly'] * 0.1)
            })
        
        # Ajouter une recommandation par défaut
        if not recommendations:
            recommendations.append({
                'type': 'general',
                'title': 'Maintenir le cap',
                'description': 'Votre activité est optimale. Continuez ainsi !',
                'potential_gain': 0
            })
        
        return recommendations
    
    def save_models(self):
        """Sauvegarde les modèles"""
        
        joblib.dump(self.revenue_model, 'models/saved_models/doctor_profit_model.pkl')
        joblib.dump(self.scaler, 'models/saved_models/doctor_profit_scaler.pkl')
        joblib.dump(self.pricing, 'models/saved_models/doctor_pricing.pkl')
        
        print("\n💾 Modèles de profit sauvegardés")

# Entraînement
if __name__ == "__main__":
    print("=" * 50)
    print("💰 ENTRAÎNEMENT MODÈLE DE PROFIT PAR MÉDECIN")
    print("=" * 50)
    
    predictor = DoctorProfitPredictor()
    consultations_df, patients_df = predictor.load_data()
    doctor_data = predictor.prepare_doctor_profit_features(consultations_df, patients_df)
    predictor.train_model(doctor_data)
    predictor.save_models()
    
    # Exemple pour un médecin
    print("\n🔍 EXEMPLE pour le médecin 1:")
    result = predictor.predict_doctor_profit(1, doctor_data)
    if result:
        print(f"   Revenu actuel: {result['current']['monthly']} TND/mois")
        print(f"   Prédiction mois prochain: {result['predictions']['next_month']} TND")
        print(f"   Croissance: {result['predictions']['growth']}%")
        print(f"   Recommandations:")
        for rec in result['recommendations'][:2]:
            print(f"     - {rec['title']}")