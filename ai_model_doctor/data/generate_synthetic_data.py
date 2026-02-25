# data/generate_synthetic_data.py

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

class SyntheticMedicalDataGenerator:
    """
    Générateur de données synthétiques pour l'entraînement du modèle
    """
    
    def __init__(self, n_doctors=10, n_days=730):  # 2 ans de données
        self.n_doctors = n_doctors
        self.n_days = n_days
        self.doctors = []
        self.data = []
        
        # Configurations des médecins
        self.specialties = ['Généraliste', 'Cardiologue', 'Dermatologue', 
                           'Pédiatre', 'Gynécologue', 'Neurologue',
                           'Ophtalmologue', 'ORL', 'Rhumatologue', 'Psychiatre']
        
        # Jours fériés en Tunisie
        self.holidays = [
            '01-01',  # Nouvel an
            '14-01',  # Fête de la révolution
            '20-03',  # Fête de l'indépendance
            '09-04',  # Fête des martyrs
            '01-05',  # Fête du travail
            '25-07',  # Fête de la république
            '13-08',  # Fête de la femme
            '15-10',  # Fête de l'évacuation
        ]
        
    def generate_doctors(self):
        """Génère les profils des médecins"""
        for i in range(self.n_doctors):
            doctor = {
                'doctor_id': i + 1,
                'specialty': random.choice(self.specialties),
                'experience_years': random.randint(3, 25),
                'avg_consultation_time': random.randint(15, 30),
                'popularity_score': random.uniform(0.3, 1.0),
                'city': random.choice(['Tunis', 'Sfax', 'Sousse', 'Nabeul', 'Bizerte']),
                'has_online_consultations': random.choice([True, False]),
                'works_weekend': random.choice([True, False]),
                'max_patients_per_day': random.randint(15, 35),
            }
            self.doctors.append(doctor)
        return self.doctors
    
    def generate_consultations(self):
        """Génère les consultations pour chaque médecin"""
        
        start_date = datetime.now() - timedelta(days=self.n_days)
        
        for day in range(self.n_days):
            current_date = start_date + timedelta(days=day)
            date_str = current_date.strftime('%Y-%m-%d')
            day_of_week = current_date.weekday()  # 0 = Lundi, 6 = Dimanche
            month = current_date.month
            is_weekend = day_of_week >= 5
            is_holiday = current_date.strftime('%m-%d') in self.holidays
            
            for doctor in self.doctors:
                # Facteurs influençant le nombre de consultations
                base_consults = doctor['max_patients_per_day'] * doctor['popularity_score']
                
                # Variation selon le jour
                if is_weekend:
                    if doctor['works_weekend']:
                        day_factor = 0.6  # 60% des patients le weekend
                    else:
                        continue  # Pas de consultation le weekend
                else:
                    day_factor = 1.0
                
                # Variation selon le mois (saisonalité)
                month_factors = {
                    1: 0.9,   # Janvier (froid, plus de malades)
                    2: 0.95,
                    3: 1.0,
                    4: 1.0,
                    5: 1.05,
                    6: 1.1,   # Été, plus d'activité
                    7: 1.15,
                    8: 1.1,
                    9: 1.0,
                    10: 0.95,
                    11: 0.9,
                    12: 0.85, # Décembre (vacances)
                }
                month_factor = month_factors.get(month, 1.0)
                
                # Jours fériés
                holiday_factor = 0.3 if is_holiday else 1.0
                
                # Bruit aléatoire
                noise = random.uniform(0.8, 1.2)
                
                # Calcul final
                expected_consults = base_consults * day_factor * month_factor * holiday_factor * noise
                
                # Générer la distribution horaire
                hourly_distribution = self.generate_hourly_distribution(
                    doctor['avg_consultation_time']
                )
                
                # Générer les types de consultations
                consultation_types = self.generate_consultation_types(
                    doctor['specialty']
                )
                
                # Sauvegarder
                self.data.append({
                    'date': date_str,
                    'doctor_id': doctor['doctor_id'],
                    'specialty': doctor['specialty'],
                    'day_of_week': day_of_week,
                    'month': month,
                    'is_weekend': int(is_weekend),
                    'is_holiday': int(is_holiday),
                    'expected_consultations': int(expected_consults),
                    'actual_consultations': int(expected_consults * random.uniform(0.9, 1.1)),
                    'avg_consultation_time': doctor['avg_consultation_time'],
                    'popularity_score': doctor['popularity_score'],
                    'has_online_consultations': int(doctor['has_online_consultations']),
                    'hour_8_10': hourly_distribution['morning'],
                    'hour_10_12': hourly_distribution['mid_morning'],
                    'hour_14_16': hourly_distribution['afternoon'],
                    'hour_16_18': hourly_distribution['late_afternoon'],
                    'new_patients': consultation_types['new'],
                    'follow_up': consultation_types['follow_up'],
                    'emergencies': consultation_types['emergency'],
                })
        
        return pd.DataFrame(self.data)
    
    def generate_hourly_distribution(self, avg_time):
        """Génère la distribution horaire des consultations"""
        total = 20  # Total approximatif de créneaux
        
        return {
            'morning': random.randint(4, 8),      # 8h-10h
            'mid_morning': random.randint(5, 9),   # 10h-12h
            'afternoon': random.randint(4, 7),     # 14h-16h
            'late_afternoon': random.randint(2, 5), # 16h-18h
        }
    
    def generate_consultation_types(self, specialty):
        """Génère les types de consultations"""
        return {
            'new': random.randint(3, 8),
            'follow_up': random.randint(5, 12),
            'emergency': random.randint(0, 3),
        }
    
    def save_data(self, filename='synthetic_consultations.csv'):
        """Sauvegarde les données en CSV"""
        df = pd.DataFrame(self.data)
        df.to_csv(f'data/{filename}', index=False)
        print(f"✅ Données sauvegardées: {len(df)} enregistrements")
        print(f"📊 Statistiques:")
        print(f"   - Médecins: {self.n_doctors}")
        print(f"   - Période: {self.n_days} jours")
        print(f"   - Consultations totales: {df['actual_consultations'].sum():,.0f}")
        return df

# Génération des données
if __name__ == "__main__":
    generator = SyntheticMedicalDataGenerator(n_doctors=15, n_days=730)
    generator.generate_doctors()
    df = generator.generate_consultations()
    generator.save_data()
    
    # Afficher un aperçu
    print("\n🔍 Aperçu des données:")
    print(df.head())
    print("\n📈 Statistiques descriptives:")
    print(df.describe())