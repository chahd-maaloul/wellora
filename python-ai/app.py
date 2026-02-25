from flask import Flask, request, jsonify
from flask_cors import CORS
from ai_engine import AIExercisePlanner
import json

app = Flask(__name__)
CORS(app)  # Permet les requêtes depuis Symfony

# Initialiser le moteur IA
planner = AIExercisePlanner()

@app.route('/health', methods=['GET'])
def health():
    """Endpoint de vérification"""
    return jsonify({'status': 'ok', 'message': 'AI Service is running'})

@app.route('/api/generate-program', methods=['POST'])
def generate_program():
    """
    Génère un programme complet basé sur la demande utilisateur
    Body: {"user_request": "Je veux perdre du poids en 3 mois"}
    """
    try:
        data = request.json
        user_request = data.get('user_request', '')
        
        if not user_request:
            return jsonify({'error': 'user_request is required'}), 400
        
        # Générer le programme
        program = planner.generate_complete_program(user_request)
        
        return jsonify({
            'success': True,
            'program': program
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/analyze-request', methods=['POST'])
def analyze_request():
    """Analyse seulement la demande"""
    try:
        data = request.json
        user_request = data.get('user_request', '')
        
        if not user_request:
            return jsonify({'error': 'user_request is required'}), 400
        
        analysis = planner.analyze_user_request(user_request)
        
        return jsonify({
            'success': True,
            'analysis': analysis
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/exercises', methods=['GET'])
def get_exercises():
    """Retourne la bibliothèque d'exercices"""
    return jsonify(planner.exercises_db)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)