import json
import random
import re
from datetime import datetime, timedelta
from typing import Dict, List, Any, Optional

class AIExercisePlanner:
    def __init__(self, exercises_file='data/exercises.json'):
        with open(exercises_file, 'r', encoding='utf-8') as f:
            self.exercises_db = json.load(f)
        
        # Mapping des catégories d'objectifs vers les catégories d'exercices
        self.goal_category_mapping = {
            'Weight Loss': ['Cardio', 'Strength'],
            'Muscle Gain': ['Strength', 'Core'],
            'Endurance': ['Cardio'],
            'Flexibility': ['Flexibility'],
            'Rehabilitation': ['Flexibility', 'Core'],
            'General': ['Cardio', 'Strength', 'Flexibility', 'Core']
        }

    def analyze_user_request(self, user_input: str) -> Dict[str, Any]:
        """Analyse la demande en langage naturel"""
        user_input = user_input.lower()
        
        # Détection de l'objectif
        goal_type = self._detect_goal_type(user_input)
        
        # Détection du niveau
        difficulty_level = self._detect_difficulty(user_input)
        
        # Détection de la durée en semaines
        duration_weeks = self._detect_duration_weeks(user_input)
        
        # Détection des séances par semaine
        sessions_per_week = self._detect_sessions_per_week(user_input)
        
        # Détection des contraintes
        constraints = self._detect_constraints(user_input)
        
        # Générer un titre
        title = self._generate_goal_title(goal_type, difficulty_level, duration_weeks)
        
        # Générer une description
        description = self._generate_goal_description(goal_type, difficulty_level, duration_weeks, sessions_per_week)
        
        return {
            'title': title,
            'description': description,
            'category': goal_type,
            'difficultyLevel': difficulty_level,
            'durationWeeks': duration_weeks,
            'sessionsPerWeek': sessions_per_week,
            'constraints': constraints,
            'raw_input': user_input
        }

    def _detect_goal_type(self, text: str) -> str:
        """Détecte le type d'objectif"""
        if any(word in text for word in ['perdre', 'poids', 'maigrir', 'weight', 'fat', 'gross']):
            return 'Weight Loss'
        elif any(word in text for word in ['muscle', 'prendre', 'masse', 'gain', 'strength']):
            return 'Muscle Gain'
        elif any(word in text for word in ['endurance', 'cardio', 'course', 'run']):
            return 'Endurance'
        elif any(word in text for word in ['flexible', 'étirement', 'yoga', 'stretch']):
            return 'Flexibility'
        elif any(word in text for word in ['rééducation', 'rehab', 'blessure', 'injury']):
            return 'Rehabilitation'
        else:
            return 'General'

    def _detect_difficulty(self, text: str) -> str:
        """Détecte le niveau de difficulté"""
        if any(word in text for word in ['débutant', 'beginner', 'jamais', 'first', 'debutant']):
            return 'Beginner'
        elif any(word in text for word in ['avancé', 'advanced', 'expert', 'confirmé']):
            return 'Advanced'
        else:
            return 'Intermediate'

    def _detect_duration_weeks(self, text: str) -> int:
        """Détecte la durée en semaines"""
        # Cherche des patterns comme "3 mois", "12 semaines", etc.
        months_match = re.search(r'(\d+)\s*(mois|month)', text)
        if months_match:
            return int(months_match.group(1)) * 4
        
        weeks_match = re.search(r'(\d+)\s*(semaine|week)', text)
        if weeks_match:
            return int(weeks_match.group(1))
        
        # Par défaut
        return 8

    def _detect_sessions_per_week(self, text: str) -> int:
        """Détecte le nombre de séances par semaine"""
        sessions_match = re.search(r'(\d+)\s*(séance|session|fois)\s*par\s*(semaine|week)', text)
        if sessions_match:
            return int(sessions_match.group(1))
        
        # Par défaut
        return 3

    def _detect_constraints(self, text: str) -> List[str]:
        """Détecte les contraintes"""
        constraints = []
        
        if any(word in text for word in ['genou', 'knee', 'articulation']):
            constraints.append('knee_pain')
        if any(word in text for word in ['dos', 'back', 'lombaires']):
            constraints.append('back_pain')
        if any(word in text for word in ['épaule', 'shoulder']):
            constraints.append('shoulder_pain')
        
        return constraints

    def _generate_goal_title(self, goal_type: str, difficulty: str, weeks: int) -> str:
        """Génère un titre pour l'objectif"""
        titles = {
            'Weight Loss': f"Programme perte de poids {weeks} semaines - Niveau {difficulty}",
            'Muscle Gain': f"Programme prise de muscle {weeks} semaines - Niveau {difficulty}",
            'Endurance': f"Programme endurance {weeks} semaines - Niveau {difficulty}",
            'Flexibility': f"Programme flexibilité {weeks} semaines - Niveau {difficulty}",
            'Rehabilitation': f"Programme rééducation {weeks} semaines - Niveau {difficulty}",
            'General': f"Programme fitness {weeks} semaines - Niveau {difficulty}"
        }
        return titles.get(goal_type, f"Programme personnalisé {weeks} semaines")

    def _generate_goal_description(self, goal_type: str, difficulty: str, weeks: int, sessions: int) -> str:
        """Génère une description pour l'objectif"""
        descriptions = {
            'Weight Loss': f"Objectif perte de poids sur {weeks} semaines. Programme adapté aux {difficulty} avec {sessions} séances par semaine.",
            'Muscle Gain': f"Objectif prise de muscle sur {weeks} semaines. Programme adapté aux {difficulty} avec {sessions} séances par semaine.",
            'Endurance': f"Objectif amélioration endurance sur {weeks} semaines. Programme adapté aux {difficulty} avec {sessions} séances par semaine.",
            'Flexibility': f"Objectif amélioration flexibilité sur {weeks} semaines. Programme adapté aux {difficulty} avec {sessions} séances par semaine.",
            'Rehabilitation': f"Objectif rééducation sur {weeks} semaines. Programme adapté aux {difficulty} avec {sessions} séances par semaine.",
            'General': f"Objectif fitness général sur {weeks} semaines. Programme adapté aux {difficulty} avec {sessions} séances par semaine."
        }
        return descriptions.get(goal_type, f"Programme personnalisé de {weeks} semaines, {sessions} séances/semaine, niveau {difficulty}")

    def generate_complete_program(self, user_request: str) -> Dict[str, Any]:
        """Génère un programme complet basé sur la demande"""
        # Analyser la demande
        analysis = self.analyze_user_request(user_request)
        
        # Générer les plans quotidiens
        daily_plans = self._generate_daily_plans(analysis)
        
        return {
            'goal': {
                'title': analysis['title'],
                'description': analysis['description'],
                'category': analysis['category'],
                'status': 'PENDING',
                'startDate': datetime.now().isoformat(),
                'endDate': (datetime.now() + timedelta(weeks=analysis['durationWeeks'])).isoformat(),
                'difficultyLevel': analysis['difficultyLevel'],
                'sessionsPerWeek': analysis['sessionsPerWeek'],
                'durationWeeks': analysis['durationWeeks'],
                'progress': 0,
                'targetAudience': analysis['category']
            },
            'daily_plans': daily_plans,
            'analysis': analysis
        }

    def _generate_daily_plans(self, analysis: Dict[str, Any]) -> List[Dict[str, Any]]:
        """Génère tous les plans quotidiens"""
        daily_plans = []
        
        # Types d'exercices pour cette catégorie
        exercise_categories = self.goal_category_mapping.get(
            analysis['category'], 
            ['Cardio', 'Strength']
        )
        
        # Niveau
        level = analysis['difficultyLevel']
        
        # Date de début
        current_date = datetime.now()
        
        # Générer semaine par semaine
        for week in range(1, analysis['durationWeeks'] + 1):
            # Adapter l'intensité selon la semaine
            week_intensity = self._calculate_week_intensity(week, analysis['durationWeeks'])
            
            # Générer les séances de la semaine
            for session_num in range(1, analysis['sessionsPerWeek'] + 1):
                # Date de la séance (espacée dans la semaine)
                session_date = current_date + timedelta(
                    weeks=week-1, 
                    days=(session_num * 2) % 7  # Espace les séances
                )
                
                # Sélectionner les exercices
                selected_exercises = self._select_exercises(
                    exercise_categories, 
                    level, 
                    week_intensity,
                    analysis.get('constraints', [])
                )
                
                # Calculer la durée totale et calories
                total_duration = sum(ex.get('duration', 0) for ex in selected_exercises)
                total_calories = sum(ex.get('calories', 0) * ex.get('duration', 0) for ex in selected_exercises)
                
                # Créer le plan
                daily_plan = {
                    'date': session_date.strftime('%Y-%m-%d'),
                    'status': 'planned',
                    'notes': '',
                    'titre': f"Semaine {week} - Séance {session_num}",
                    'calories': total_calories,
                    'duree_min': total_duration,
                    'exercices': selected_exercises,
                    'week_number': week,
                    'session_number': session_num
                }
                
                daily_plans.append(daily_plan)
        
        return daily_plans

    def _calculate_week_intensity(self, week: int, total_weeks: int) -> str:
        """Calcule l'intensité pour une semaine donnée"""
        if week <= 2:
            return 'light'
        elif week >= total_weeks - 2:
            return 'peak'
        else:
            return 'normal'

    def _select_exercises(self, categories: List[str], level: str, intensity: str, constraints: List[str]) -> List[Dict[str, Any]]:
        """Sélectionne des exercices appropriés"""
        selected = []
        
        # Adapter le nombre d'exercices selon l'intensité
        if intensity == 'light':
            num_exercises = 4
        elif intensity == 'peak':
            num_exercises = 8
        else:
            num_exercises = 6
        
        # Répartir entre les catégories
        per_category = max(1, num_exercises // len(categories))
        
        for category in categories:
            if category in self.exercises_db:
                category_exercises = self.exercises_db[category]
                
                if level in category_exercises:
                    available = category_exercises[level].copy()
                    
                    # Filtrer selon les contraintes
                    available = self._filter_by_constraints(available, constraints)
                    
                    # Sélectionner aléatoirement
                    if available:
                        num_to_take = min(len(available), per_category)
                        selected.extend(random.sample(available, num_to_take))
        
        return selected

    def _filter_by_constraints(self, exercises: List[Dict], constraints: List[str]) -> List[Dict]:
        """Filtre les exercices selon les contraintes"""
        if 'knee_pain' in constraints:
            filtered = []
            for ex in exercises:
                name = ex.get('name', '').lower()
                if not any(word in name for word in ['squat', 'fente', 'lunge']):
                    filtered.append(ex)
            return filtered
        
        return exercises