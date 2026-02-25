# data/generate_patient_data.py

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

class PatientDataGenerator:
    """
    Générateur de données patients pour l'entraînement
    """
    
    def __init__(self, n_patients=1000, n_doctors=15):
        self.n_patients = n_patients
        self.n_doctors = n_doctors
        self.patients = []
        self.consultations = []
        self.vital_signs = []
        
        # Conditions médicales courantes
        self.conditions = [
            'hypertension', 'diabète_type2', 'asthme', 
            'arthrose', 'dépression', 'migraine',
            'hypercholestérolémie', 'hypothyroïdie',
            'allergie_saisonnière', 'reflux_gastro'
        ]
        
        # Médicaments courants
        self.medications = {
            'hypertension': ['Lisinopril', 'Amlodipine', 'Losartan'],
            'diabète_type2': ['Metformine', 'Gliclazide', 'Sitagliptine'],
            'asthme': ['Ventoline', 'Pulmicort', 'Singulair'],
            'arthrose': ['Paracétamol', 'Ibuprofène', 'Diclofénac'],
            'dépression': ['Sertraline', 'Escitalopram', 'Fluoxétine'],
            'migraine': ['Sumatriptan', 'Propranolol', 'Amitriptyline'],
            'hypercholestérolémie': ['Atorvastatine', 'Rosuvastatine', 'Simvastatine'],
            'hypothyroïdie': ['Lévothyrox', 'Euthyrox'],
            'allergie_saisonnière': ['Cétirizine', 'Loratadine', 'Desloratadine'],
            'reflux_gastro': ['Oméprazole', 'Pantoprazole', 'Ranitidine']
        }
    
    def generate_patients(self):
        """Génère les profils patients"""
        
        for i in range(self.n_patients):
            age = random.randint(18, 85)
            gender = random.choice(['M', 'F'])
            
            # Conditions selon l'âge
            n_conditions = random.choices(
                [0, 1, 2, 3], 
                weights=[30, 40, 20, 10] if age < 40 else [10, 30, 40, 20]
            )[0]
            
            patient_conditions = random.sample(self.conditions, 
                                              min(n_conditions, len(self.conditions)))
            
            # Signes vitaux de base
            if age < 40:
                tension_base = random.randint(110, 130)
            elif age < 60:
                tension_base = random.randint(120, 140)
            else:
                tension_base = random.randint(130, 150)
            
            poids_base = random.randint(55, 95)
            taille = random.randint(150, 185)
            
            patient = {
                'patient_id': i + 1000,
                'age': age,
                'gender': gender,
                'conditions': patient_conditions,
                'medications': self._get_medications(patient_conditions),
                'tension_base': tension_base,
                'poids_base': poids_base,
                'taille': taille,
                'imc_base': round(poids_base / ((taille/100) ** 2), 1),
                'doctor_id': random.randint(1, self.n_doctors),
                'date_inscription': datetime.now() - timedelta(days=random.randint(30, 730)),
                'frequence_visites': random.choice(['mensuel', 'trimestriel', 'semestriel', 'annuel']),
                'observance_habituelle': random.randint(60, 100),  # %
            }
            
            self.patients.append(patient)
        
        return pd.DataFrame(self.patients)
    
    def _get_medications(self, conditions):
        """Associe des médicaments aux conditions"""
        meds = []
        for condition in conditions:
            if condition in self.medications:
                meds.append(random.choice(self.medications[condition]))
        return meds
    
    def generate_vital_signs_history(self, patients_df):
        """Génère l'historique des signes vitaux pour chaque patient"""
        
        vital_records = []
        
        for _, patient in patients_df.iterrows():
            patient_id = patient['patient_id']
            date_inscription = patient['date_inscription']
            doctor_id = patient['doctor_id']
            
            # Nombre de visites selon la fréquence
            freq = patient['frequence_visites']
            if freq == 'mensuel':
                n_visits = random.randint(6, 24)
            elif freq == 'trimestriel':
                n_visits = random.randint(2, 8)
            elif freq == 'semestriel':
                n_visits = random.randint(1, 4)
            else:
                n_visits = random.randint(1, 2)
            
            # Générer les visites
            for v in range(n_visits):
                visit_date = date_inscription + timedelta(days=random.randint(30, 365 * 2))
                
                # Évolution des signes (avec tendance)
                time_factor = v / max(1, n_visits)
                
                # Amélioration ou détérioration selon observance
                if patient['observance_habituelle'] > 80:
                    # Bonne observance → amélioration
                    tension = patient['tension_base'] * (1 - 0.1 * time_factor)
                    poids = patient['poids_base'] * (1 - 0.05 * time_factor)
                else:
                    # Mauvaise observance → détérioration
                    tension = patient['tension_base'] * (1 + 0.15 * time_factor)
                    poids = patient['poids_base'] * (1 + 0.1 * time_factor)
                
                # Ajouter du bruit
                tension += random.randint(-5, 5)
                poids += random.uniform(-2, 2)
                
                # Glycémie (si diabétique)
                if 'diabète_type2' in patient['conditions']:
                    glycemie = random.randint(90, 180)
                else:
                    glycemie = random.randint(70, 110)
                
                vital_records.append({
                    'patient_id': patient_id,
                    'doctor_id': doctor_id,
                    'date_visite': visit_date,
                    'tension_systolique': int(tension),
                    'tension_diastolique': int(tension * 0.6 + random.randint(-5, 5)),
                    'poids': round(poids, 1),
                    'glycemie': glycemie,
                    'saturation_oxygene': random.randint(95, 100),
                    'temperature': round(random.uniform(36.1, 37.5), 1),
                    'observance_rapportee': random.randint(50, 100),
                    'symptomes_signales': random.choice(['aucun', 'léger', 'modéré', 'grave']),
                    'satisfaction': random.randint(1, 5)
                })
        
        return pd.DataFrame(vital_records)
    
    def save_data(self):
        """Sauvegarde toutes les données générées"""
        
        print("👥 Génération des profils patients...")
        patients_df = self.generate_patients()
        patients_df.to_csv('data/patients.csv', index=False)
        print(f"   {len(patients_df)} patients générés")
        
        print("📊 Génération de l'historique des signes vitaux...")
        vital_df = self.generate_vital_signs_history(patients_df)
        vital_df.to_csv('data/vital_signs.csv', index=False)
        print(f"   {len(vital_df)} enregistrements de signes vitaux")
        
        # Statistiques
        print("\n📈 STATISTIQUES PATIENTS:")
        print(f"   Âge moyen: {patients_df['age'].mean():.1f} ans")
        print(f"   Répartition: {patients_df['gender'].value_counts().to_dict()}")
        print(f"   Conditions les plus fréquentes:")
        all_conditions = []
        for conditions in patients_df['conditions']:
            all_conditions.extend(conditions)
        from collections import Counter
        top_conditions = Counter(all_conditions).most_common(5)
        for cond, count in top_conditions:
            print(f"     - {cond}: {count}")
        
        return patients_df, vital_df

if __name__ == "__main__":
    generator = PatientDataGenerator(n_patients=1000, n_doctors=15)
    patients_df, vital_df = generator.save_data()