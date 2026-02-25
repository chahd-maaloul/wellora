# models/train_activity_model.py

import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, cross_val_score, GridSearchCV
from sklearn.ensemble import RandomForestRegressor, GradientBoostingRegressor
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
import xgboost as xgb
import joblib
import warnings
warnings.filterwarnings('ignore')

class MedicalActivityPredictor:
    """
    Modèle de prédiction de l'activité médicale
    """
    
    def __init__(self):
        self.models = {}
        self.scaler = StandardScaler()
        self.label_encoders = {}
        self.feature_importance = {}
        self.best_model = None
        
    def load_and_prepare_data(self, filepath='data/synthetic_consultations.csv'):
        """Charge et prépare les données pour l'entraînement"""
        
        df = pd.read_csv(filepath)
        print(f"📊 Données chargées: {len(df)} enregistrements")
        
        # Créer des features temporelles
        df['date'] = pd.to_datetime(df['date'])
        df['day_of_month'] = df['date'].dt.day
        df['week_of_year'] = df['date'].dt.isocalendar().week
        df['quarter'] = df['date'].dt.quarter
        df['season'] = df['month'].apply(self._get_season)
        
        # Features cycliques pour le jour de la semaine
        df['day_sin'] = np.sin(2 * np.pi * df['day_of_week'] / 7)
        df['day_cos'] = np.cos(2 * np.pi * df['day_of_week'] / 7)
        
        # Features cycliques pour le mois
        df['month_sin'] = np.sin(2 * np.pi * df['month'] / 12)
        df['month_cos'] = np.cos(2 * np.pi * df['month'] / 12)
        
        # Moyennes mobiles
        df = self._add_rolling_features(df)
        
        return df
    
    def _get_season(self, month):
        """Détermine la saison"""
        if month in [12, 1, 2]:
            return 'hiver'
        elif month in [3, 4, 5]:
            return 'printemps'
        elif month in [6, 7, 8]:
            return 'ete'
        else:
            return 'automne'
    
    def _add_rolling_features(self, df):
        """Ajoute des moyennes mobiles pour capturer les tendances"""
        
        for doctor_id in df['doctor_id'].unique():
            mask = df['doctor_id'] == doctor_id
            
            # Moyenne mobile sur 7 jours
            df.loc[mask, 'rolling_7d'] = df.loc[mask, 'actual_consultations']\
                .rolling(window=7, min_periods=1).mean()
            
            # Moyenne mobile sur 30 jours
            df.loc[mask, 'rolling_30d'] = df.loc[mask, 'actual_consultations']\
                .rolling(window=30, min_periods=1).mean()
            
            # Tendance (différence avec la moyenne)
            df.loc[mask, 'trend'] = df.loc[mask, 'actual_consultations'] - \
                                    df.loc[mask, 'rolling_7d']
        
        return df
    
    def prepare_features(self, df):
        """Prépare les features pour l'entraînement"""
        
        # Features numériques
        numeric_features = [
            'day_of_week', 'month', 'is_weekend', 'is_holiday',
            'avg_consultation_time', 'popularity_score',
            'has_online_consultations', 'day_of_month', 'week_of_year',
            'quarter', 'day_sin', 'day_cos', 'month_sin', 'month_cos',
            'rolling_7d', 'rolling_30d', 'trend'
        ]
        
        # Features catégorielles
        categorical_features = ['specialty', 'season']
        
        # Encoder les features catégorielles
        for col in categorical_features:
            if col not in self.label_encoders:
                self.label_encoders[col] = LabelEncoder()
                df[col + '_encoded'] = self.label_encoders[col].fit_transform(df[col].astype(str))
            else:
                df[col + '_encoded'] = self.label_encoders[col].transform(df[col].astype(str))
            
            numeric_features.append(col + '_encoded')
        
        # Features pour la distribution horaire
        hourly_features = ['hour_8_10', 'hour_10_12', 'hour_14_16', 'hour_16_18']
        numeric_features.extend(hourly_features)
        
        # Sélectionner les features
        X = df[numeric_features].fillna(0)
        
        # Target
        y = df['actual_consultations']
        
        return X, y
    
    def train_models(self, X, y):
        """Entraîne plusieurs modèles et sélectionne le meilleur"""
        
        # Split des données
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42
        )
        
        # Scaling
        X_train_scaled = self.scaler.fit_transform(X_train)
        X_test_scaled = self.scaler.transform(X_test)
        
        # Modèle 1: Random Forest
        print("\n🌲 Entraînement Random Forest...")
        rf_model = RandomForestRegressor(
            n_estimators=100,
            max_depth=15,
            min_samples_split=5,
            random_state=42,
            n_jobs=-1
        )
        rf_model.fit(X_train_scaled, y_train)
        rf_pred = rf_model.predict(X_test_scaled)
        rf_score = r2_score(y_test, rf_pred)
        rf_mae = mean_absolute_error(y_test, rf_pred)
        self.models['random_forest'] = {
            'model': rf_model,
            'r2': rf_score,
            'mae': rf_mae
        }
        print(f"   R²: {rf_score:.3f}, MAE: {rf_mae:.2f}")
        
        # Modèle 2: XGBoost
        print("\n🚀 Entraînement XGBoost...")
        xgb_model = xgb.XGBRegressor(
            n_estimators=100,
            max_depth=8,
            learning_rate=0.1,
            random_state=42,
            n_jobs=-1
        )
        xgb_model.fit(X_train_scaled, y_train)
        xgb_pred = xgb_model.predict(X_test_scaled)
        xgb_score = r2_score(y_test, xgb_pred)
        xgb_mae = mean_absolute_error(y_test, xgb_pred)
        self.models['xgboost'] = {
            'model': xgb_model,
            'r2': xgb_score,
            'mae': xgb_mae
        }
        print(f"   R²: {xgb_score:.3f}, MAE: {xgb_mae:.2f}")
        
        # Modèle 3: Gradient Boosting
        print("\n📈 Entraînement Gradient Boosting...")
        gb_model = GradientBoostingRegressor(
            n_estimators=100,
            max_depth=6,
            learning_rate=0.1,
            random_state=42
        )
        gb_model.fit(X_train_scaled, y_train)
        gb_pred = gb_model.predict(X_test_scaled)
        gb_score = r2_score(y_test, gb_pred)
        gb_mae = mean_absolute_error(y_test, gb_pred)
        self.models['gradient_boosting'] = {
            'model': gb_model,
            'r2': gb_score,
            'mae': gb_mae
        }
        print(f"   R²: {gb_score:.3f}, MAE: {gb_mae:.2f}")
        
        # Sélectionner le meilleur modèle
        best_model_name = max(self.models, key=lambda x: self.models[x]['r2'])
        self.best_model = self.models[best_model_name]['model']
        
        print(f"\n🏆 Meilleur modèle: {best_model_name}")
        print(f"   R²: {self.models[best_model_name]['r2']:.3f}")
        print(f"   MAE: {self.models[best_model_name]['mae']:.2f}")
        
        # Feature importance
        self._analyze_feature_importance(X.columns)
        
        return X_test, y_test
    
    def _analyze_feature_importance(self, feature_names):
        """Analyse l'importance des features"""
        
        if hasattr(self.best_model, 'feature_importances_'):
            importances = self.best_model.feature_importances_
            indices = np.argsort(importances)[::-1]
            
            print("\n📊 Top 10 features les plus importantes:")
            for i in range(min(10, len(feature_names))):
                print(f"   {i+1}. {feature_names[indices[i]]}: {importances[indices[i]]:.3f}")
            
            self.feature_importance = {
                feature_names[i]: importances[i] 
                for i in indices[:10]
            }
    
    def optimize_hyperparameters(self, X, y):
        """Optimisation des hyperparamètres pour XGBoost"""
        
        print("\n🔧 Optimisation des hyperparamètres...")
        
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42
        )
        
        X_train_scaled = self.scaler.fit_transform(X_train)
        
        param_grid = {
            'n_estimators': [50, 100, 200],
            'max_depth': [4, 6, 8, 10],
            'learning_rate': [0.01, 0.05, 0.1],
            'subsample': [0.8, 0.9, 1.0],
            'colsample_bytree': [0.8, 0.9, 1.0]
        }
        
        xgb_model = xgb.XGBRegressor(random_state=42, n_jobs=-1)
        
        grid_search = GridSearchCV(
            xgb_model, 
            param_grid, 
            cv=3, 
            scoring='r2',
            n_jobs=-1,
            verbose=1
        )
        
        grid_search.fit(X_train_scaled, y_train)
        
        print(f"\n✅ Meilleurs paramètres trouvés:")
        for param, value in grid_search.best_params_.items():
            print(f"   {param}: {value}")
        
        print(f"   Score: {grid_search.best_score_:.3f}")
        
        self.best_model = grid_search.best_estimator_
        return grid_search.best_params_
    
    def save_models(self):
        """Sauvegarde les modèles entraînés"""
        
        # Sauvegarder le meilleur modèle
        joblib.dump(self.best_model, 'models/saved_models/activity_predictor.pkl')
        
        # Sauvegarder le scaler
        joblib.dump(self.scaler, 'models/saved_models/scaler.pkl')
        
        # Sauvegarder les encoders
        joblib.dump(self.label_encoders, 'models/saved_models/label_encoders.pkl')
        
        print("\n💾 Modèles sauvegardés dans 'models/saved_models/'")
    
    def predict_doctor_activity(self, doctor_features):
        """Prédit l'activité pour un médecin spécifique"""
        
        features_scaled = self.scaler.transform([doctor_features])
        prediction = self.best_model.predict(features_scaled)
        
        return prediction[0]

# Entraînement principal
if __name__ == "__main__":
    print("=" * 50)
    print("🤖 ENTRAÎNEMENT DU MODÈLE DE PRÉDICTION MÉDICALE")
    print("=" * 50)
    
    # Initialiser le prédicteur
    predictor = MedicalActivityPredictor()
    
    # Charger les données
    df = predictor.load_and_prepare_data()
    
    # Préparer les features
    X, y = predictor.prepare_features(df)
    print(f"\n🔍 Features: {X.shape[1]}, Échantillons: {X.shape[0]}")
    
    # Entraîner les modèles
    X_test, y_test = predictor.train_models(X, y)
    
    # Option: Optimiser les hyperparamètres
    # predictor.optimize_hyperparameters(X, y)
    
    # Sauvegarder
    predictor.save_models()
    
    print("\n✅ Entraînement terminé avec succès!")