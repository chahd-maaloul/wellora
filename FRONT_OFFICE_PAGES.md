# WellCare Connect - Front Office (Patient) Modules

Complete list of all pages accessible by the patient from their dashboard with correct navigation routes.

---

## 📋 TABLE OF CONTENTS

1. [🏠 HOME / AUTH MODULE](#home-auth-module)
2. [🏥 APPOINTMENTS MODULE](#appointments-module)
3. [🏥 HEALTH RECORDS MODULE](#health-records-module)
4. [🍎 NUTRITION MODULE](#nutrition-module)
5. [📹 TELECONSULTATION MODULE](#teleconsultation-module)
6. [🔐 ACCESSIBILITY](#accessibility)
7. [📊 NAVIGATION SIDEBAR STRUCTURE](#navigation-sidebar-structure)
8. [🔗 ROUTE REFERENCE](#route-reference)

---

## 🏠 HOME / AUTH MODULE

### Authentication Pages (Accessible without login)

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| Landing Page | `/` | `app_home` | `home/index.html.twig` | Direct URL |
| Login | `/login` | `app_login` | `auth/login.html.twig` | Landing Page → "Se connecter" |
| Register (Patient) | `/register/patient` | `app_register_patient` | `auth/register-patient.html.twig` | Login → "Créer un compte" |
| Register (Professional) | `/register/professional` | `app_register_professional` | `auth/register-professional.html.twig` | Login → "Médecin/Professionnel" |
| Forgot Password | `/forgot-password` | `app_forgot_password` | `auth/forgot-password.html.twig` | Login → "Mot de passe oublié" |
| Reset Password | `/reset-password` | `app_reset_password` | `auth/reset-password.html.twig` | Email link |
| Verify Email | `/verify-email` | `app_verify_email` | `auth/verify-email.html.twig` | After registration |
| Terms of Service | `/terms` | `app_terms` | `auth/terms-modal.html.twig` | Registration form |

---

## 🏥 APPOINTMENTS MODULE

### Patient Dashboard (Main Entry Point)

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| **Patient Dashboard** | `/appointment/patient-dashboard` | `appointment_patient_dashboard` | `appointment/patient-dashboard.html.twig` | **Main Sidebar: Appointments** |

### From Patient Dashboard - Navigation Flow

```
Patient Dashboard
├── "Mes Rendez-vous" → View Appointments List
├── "Trouver un Médecin" → Search Doctors Page
└── "Historique" → Appointment History
```

### Appointment Sub-pages

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| Search Doctors | `/appointment/search-doctors` | `appointment_search_doctors` | `appointment/search-doctors.html.twig` | Patient Dashboard → "Trouver un Médecin" |
| Doctor Profile | `/appointment/doctor-profile` | `appointment_doctor_profile` | `appointment/doctor-profile.html.twig` | Search Results → Click Doctor Card |
| Booking Flow | `/appointment/booking-flow` | `appointment_booking_flow` | `appointment/booking-flow.html.twig` | Doctor Profile → "Prendre RDV" |
| Booking Confirmation | `/appointment/confirmation` | `appointment_confirmation` | `appointment/confirmation.html.twig` | Booking Flow → "Confirmer" |
| Consultation Room | `/appointment/consultation-room` | `appointment_consultation_room` | `appointment/consultation-room.html.twig` | Confirmed Appointment → "Rejoindre" |

### Patient Dashboard Sidebar Navigation

```yaml
Sidebar - Appointments:
  - Dashboard: /appointment/patient-dashboard
    ├─ Mes Rendez-vous
    ├─ Trouver un Médecin → /appointment/search-doctors
    └─ Historique
```

---

## 🏥 HEALTH RECORDS MODULE

### Health Dashboard (Main Entry Point)

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| **Health Dashboard** | `/health/dashboard` | `health_index` | `health/dashboard.html.twig` | **Main Sidebar: Health Records** |

### From Health Dashboard - Navigation Flow

```
Health Dashboard
├── "Mes Signes Vitaux" → Vital Signs Display
├── "Journal de Santé" → Recent Entries
├── "Mes Médicaments" → Medications List
├── "Rendez-vous à venir" → Upcoming Appointments
└── "Insights IA" → AI Insights Section
```

### Health Records Sub-pages

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| Medical Records | `/health/records` | N/A | `health/records.html.twig` | Health Dashboard → "Dossier Médical" |
| Lab Results | `/health/lab-results` | N/A | `health/lab-results.html.twig` | Health Dashboard → "Résultats de Laboratoire" |
| Prescriptions | `/health/prescriptions` | N/A | `health/prescriptions.html.twig` | Health Dashboard → "Ordonnances" |
| Billing & Payments | `/health/billing` | N/A | `health/billing.html.twig` | Health Dashboard → "Facturation" |
| Health Analytics | `/health/analytics` | N/A | `health/analytics/patient-view.html.twig` | Health Dashboard → "Analyses" |

### Health Partials (Reusable Components)

| Partial | Template File | Usage |
|---------|---------------|-------|
| Health Metrics | `health/partials/health-metrics.html.twig` | Vital signs display cards |
| Health Charts | `health/partials/health-charts.html.twig` | Chart.js visualizations |
| Quick Entry | `health/partials/quick-entry.html.twig` | Quick health entry form |
| Recent Entries | `health/partials/recent-entries.html.twig` | Recent journal entries list |
| AI Insights | `health/partials/ai-insights.html.twig` | AI-generated health insights |

### AJAX/API Endpoints

| Endpoint | Route Name | Purpose |
|----------|------------|---------|
| POST `/health/quick-entry` | `health_quick_entry` | Quick health entry (AJAX) |
| GET `/health/metrics` | `health_get_metrics` | Get vital signs (AJAX) |
| GET `/health/charts` | `health_get_chart_data` | Get chart data (AJAX) |
| GET `/health/insights` | `health_get_insights` | Get AI insights (AJAX) |
| GET `/health/export` | `health_export` | Export health data |

### Health Dashboard Sidebar Navigation

```yaml
Sidebar - Health Records:
  - Dashboard: /health/dashboard
    ├─ Signes Vitaux
    ├─ Journal de Santé
    ├─ Médicaments
    └─ Analyses de Santé → /health/analytics
```

---

## 🍎 NUTRITION MODULE

### Nutrition Dashboard (Main Entry Point)

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| **Nutrition Dashboard** | `/nutrition/` | `nutrition_dashboard` | `nutrition/dashboard.html.twig` | **Main Sidebar: Nutrition** |

### From Nutrition Dashboard - Navigation Flow

```
Nutrition Dashboard
├── Calories & Macros → Today's Overview
├── Repas → Food Diary
├── Planification → Meal Planner
├── Recettes → Recipe Library
├── Objectifs → Goals
├── Progrès → Progress Tracking
├── Analyse → Nutrition Analysis
└── Mon Nutritionniste → Consultation/Messages
```

### Nutrition Core Pages

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| Food Diary | `/nutrition/diary` | `nutrition_food_diary` | `nutrition/food-diary.html.twig` | Dashboard → "Journal Alimentaire" |
| Quick Log | `/nutrition/quick-log` | `nutrition_quick_log` | `nutrition/quick-log.html.twig` | Dashboard → "Enregistrement Rapide" |
| Meal Planner | `/nutrition/planner` | `nutrition_meal_planner` | `nutrition/meal-planner.html.twig` | Dashboard → "Planificateur de Repas" |
| Recipe Library | `/nutrition/recipes` | `nutrition_recipes` | `nutrition/recipe-library.html.twig` | Dashboard → "Bibliothèque de Recettes" |
| Goals | `/nutrition/goals` | `nutrition_goals` | `nutrition/goals.html.twig` | Dashboard → "Objectifs" |
| Progress Tracking | `/nutrition/progress` | N/A | `nutrition/progress-tracking.html.twig` | Dashboard → "Suivi des Progrès" |
| Nutrition Analysis | `/nutrition/analysis` | N/A | `nutrition/nutrition-analysis.html.twig` | Dashboard → "Analyse Nutritionnelle" |
| Messages | `/nutrition/messages/{id}` | `nutrition_messages` | `nutrition/messages.html.twig` | Dashboard → "Messages" |
| Consultation | `/nutrition/consultation` | `nutrition_consultation` | `nutrition/consultation.html.twig` | Dashboard → "Consultation" |

### Nutrition Sub-pages

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| Goal Wizard | `/nutrition/goal/wizard` | N/A | `nutrition/goal-wizard.html.twig` | Goals → "Nouvel Objectif" |
| Goal Detail | `/nutrition/goal/{id}` | N/A | `nutrition/goal-detail.html.twig` | Goals → Click Goal Card |
| Goal Adjustment | `/nutrition/goal/{id}/adjust` | N/A | `nutrition/goal-adjustment.html.twig` | Goal Detail → "Ajuster" |
| Meal Details | `/nutrition/meal/{id}` | N/A | `nutrition/meal-details.html.twig` | Meal Planner → Click Meal |
| Food Logger | `/nutrition/food-log` | N/A | `nutrition/food-logger.html.twig` | Diary → "Ajouter Aliment" |
| Barcode Scanner | `/nutrition/barcode` | N/A | `nutrition/barcode-scanner.html.twig` | Food Logger → "Scanner Code-Barre" |
| Voice Input | `/nutrition/voice` | N/A | `nutrition/voice-input.html.twig` | Food Logger → "Entrée Vocale" |
| Recipe Importer | `/nutrition/recipe/import` | N/A | `nutrition/recipe-importer.html.twig` | Recipes → "Importer Recette" |
| Grocery List | `/nutrition/grocery` | N/A | `nutrition/grocery-list.html.twig` | Meal Planner → "Liste de Courses" |
| Pantry Management | `/nutrition/pantry` | N/A | `nutrition/pantry-management.html.twig` | Dashboard → "Garde-Manger" |
| Cooking Assistant | `/nutrition/cooking` | N/A | `nutrition/cooking-assistant.html.twig` | Recipes → "Assistant Cuisson" |
| Success Metrics | `/nutrition/success` | N/A | `nutrition/success-metrics.html.twig` | Goals → "Mes Réussites" |

### Nutrition Partials (Reusable Components)

| Partial | Template File | Usage |
|---------|---------------|-------|
| Quick Log Modal | `nutrition/partials/quick-log-modal.html.twig` | Quick add food modal dialog |
| Barcode Scanner Modal | `nutrition/partials/barcode-scanner-modal.html.twig` | Barcode scanning interface |

### Nutrition Dashboard Sidebar Navigation

```yaml
Sidebar - Nutrition:
  - Dashboard: /nutrition/
    ├─ Journal Alimentaire → /nutrition/diary
    ├─ Enregistrement Rapide → /nutrition/quick-log
    ├─ Planificateur → /nutrition/planner
    ├─ Recettes → /nutrition/recipes
    ├─ Objectifs → /nutrition/goals
    ├─ Progrès → /nutrition/progress
    ├─ Analyse → /nutrition/analysis
    └─ Mon Nutritionniste
        ├─ Messages → /nutrition/messages/1
        └─ Consultation → /nutrition/consultation
```

---

## 📹 TELECONSULTATION MODULE

### Teleconsultation Main Entry

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| **Waiting Room** | `/teleconsultation/waiting-room` | `teleconsultation_waiting_room` | `teleconsultation/waiting-room.html.twig` | **Main Sidebar: Teleconsultation** |

### From Waiting Room - Navigation Flow

```
Waiting Room
├── Vérification Système → System Check
├── Connexion Audio/Video → Test Connection
└── Rejoindre la Consultation → Enter Room
```

### Teleconsultation Sub-pages

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| Consultation Room | `/teleconsultation/consultation-room` | `teleconsultation_consultation_room` | `teleconsultation/consultation-room.html.twig` | Waiting Room → "Rejoindre" |
| SOAP Notes | `/teleconsultation/soap-notes` | `teleconsultation_soap_notes` | `teleconsultation/soap-notes.html.twig` | Consultation Room → "Notes SOAP" |
| Medical Tools | `/teleconsultation/medical-tools` | `teleconsultation_medical_tools` | `teleconsultation/medical-tools.html.twig` | Consultation Room → "Outils Médicaux" |
| Prescription Writer | `/teleconsultation/prescription-writer` | `teleconsultation_prescription_writer` | `teleconsultation/prescription-writer.html.twig` | Consultation Room → "Ordonnance" |

### Teleconsultation Sidebar Navigation

```yaml
Sidebar - Teleconsultation:
  - Waiting Room: /teleconsultation/waiting-room
    └─ Rejoindre la Consultation → /teleconsultation/consultation-room
        ├─ Notes SOAP → /teleconsultation/soap-notes
        ├─ Outils Médicaux → /teleconsultation/medical-tools
        └─ Ordonnance → /teleconsultation/prescription-writer
```

---

## 🔐 ACCESSIBILITY

### Accessible Health Pages

| Page | Route | Route Name | Template File | Navigation |
|------|-------|------------|---------------|------------|
| Body Map (Accessible) | `/health/accessible/body-map` | N/A | `health/accessible/body-map.html.twig` | Health Records → "Corps Humain" |
| Journal Entry (Accessible) | `/health/accessible/journal-entry` | `health_journal_entry_accessible` | `health/accessible/journal-entry.html.twig` | Health Dashboard → "Journal" |

### Accessibility Features

- **High Contrast Mode** - Enhanced visibility for visually impaired users
- **Keyboard Navigation** - Full keyboard accessibility
- **Screen Reader Support** - ARIA labels and semantic HTML
- **Large Touch Targets** - Easy tapping for motor impairments
- **Voice Commands** - Hands-free navigation option

---

## 📊 NAVIGATION SIDEBAR STRUCTURE

### Complete Patient Sidebar Navigation

```yaml
🏠 HOME
   └─ Dashboard → Landing Page

🏥 APPOINTMENTS
   └─ Patient Dashboard: /appointment/patient-dashboard
      ├─ Mes Rendez-vous
      ├─ Trouver un Médecin → /appointment/search-doctors
      │   └─ Doctor Profile → /appointment/doctor-profile
      │       └─ Book Appointment → /appointment/booking-flow
      │           └─ Confirmation → /appointment/confirmation
      └─ Historique

🏥 HEALTH RECORDS
   └─ Health Dashboard: /health/dashboard
      ├─ Signes Vitaux
      ├─ Journal de Santé → /health/records
      ├─ Résultats Labo → /health/lab-results
      ├─ Ordonnances → /health/prescriptions
      ├─ Facturation → /health/billing
      └─ Analyses → /health/analytics

🍎 NUTRITION
   └─ Nutrition Dashboard: /nutrition/
      ├─ Journal → /nutrition/diary
      ├─ Quick Log → /nutrition/quick-log
      ├─ Planifier → /nutrition/planner
      ├─ Recettes → /nutrition/recipes
      ├─ Objectifs → /nutrition/goals
      ├─ Progrès → /nutrition/progress
      ├─ Analyse → /nutrition/analysis
      └─ Nutritionniste
          ├─ Messages → /nutrition/messages/1
          └─ Consultation → /nutrition/consultation

📹 TELECONSULTATION
   └─ Waiting Room: /teleconsultation/waiting-room
      └─ Consultation Room → /teleconsultation/consultation-room
          ├─ Notes SOAP → /teleconsultation/soap-notes
          ├─ Outils → /teleconsultation/medical-tools
          └─ Ordonnance → /teleconsultation/prescription-writer

🔐 ACCESSIBILITY
   ├─ Body Map → /health/accessible/body-map
   └─ Journal Entry → /health/accessible/journal-entry

⚙️ PARAMÈTRES (Settings)
   └─ Profile, Notifications, Privacy, Theme
```

---

## 🔗 ROUTE REFERENCE

### Complete Route List with Controllers

| Route | Route Name | Controller | Method | Template |
|-------|------------|------------|--------|----------|
| `/` | `app_home` | HomeController | `index()` | `home/index.html.twig` |
| `/login` | `app_login` | AuthController | `login()` | `auth/login.html.twig` |
| `/register/patient` | `app_register_patient` | AuthController | `registerPatient()` | `auth/register-patient.html.twig` |
| `/register/professional` | `app_register_professional` | AuthController | `registerProfessional()` | `auth/register-professional.html.twig` |
| `/forgot-password` | `app_forgot_password` | AuthController | `forgotPassword()` | `auth/forgot-password.html.twig` |
| `/reset-password` | `app_reset_password` | AuthController | `resetPassword()` | `auth/reset-password.html.twig` |
| `/verify-email` | `app_verify_email` | AuthController | `verifyEmail()` | `auth/verify-email.html.twig` |
| `/terms` | `app_terms` | AuthController | `terms()` | `auth/terms-modal.html.twig` |
| `/appointment/patient-dashboard` | `appointment_patient_dashboard` | AppointmentController | `patientDashboard()` | `appointment/patient-dashboard.html.twig` |
| `/appointment/search-doctors` | `appointment_search_doctors` | AppointmentController | `searchDoctors()` | `appointment/search-doctors.html.twig` |
| `/appointment/doctor-profile` | `appointment_doctor_profile` | AppointmentController | `doctorProfile()` | `appointment/doctor-profile.html.twig` |
| `/appointment/booking-flow` | `appointment_booking_flow` | AppointmentController | `bookingFlow()` | `appointment/booking-flow.html.twig` |
| `/appointment/confirmation` | `appointment_confirmation` | AppointmentController | `confirmation()` | `appointment/confirmation.html.twig` |
| `/appointment/consultation-room` | `appointment_consultation_room` | AppointmentController | `consultationRoom()` | `appointment/consultation-room.html.twig` |
| `/health/dashboard` | `health_index` | HealthController | `index()` | `health/dashboard.html.twig` |
| `/health/accessible/journal-entry` | `health_journal_entry_accessible` | HealthController | `journalEntryAccessible()` | `health/accessible/journal-entry.html.twig` |
| `/health/quick-entry` | `health_quick_entry` | HealthController | `quickEntry()` | API Endpoint |
| `/health/metrics` | `health_get_metrics` | HealthController | `getMetrics()` | API Endpoint |
| `/health/charts` | `health_get_chart_data` | HealthController | `getChartData()` | API Endpoint |
| `/health/insights` | `health_get_insights` | HealthController | `getInsights()` | API Endpoint |
| `/health/export` | `health_export` | HealthController | `exportData()` | Export Handler |
| `/nutrition/` | `nutrition_dashboard` | NutritionController | `dashboard()` | `nutrition/dashboard.html.twig` |
| `/nutrition/diary` | `nutrition_food_diary` | NutritionController | `foodDiary()` | `nutrition/food-diary.html.twig` |
| `/nutrition/quick-log` | `nutrition_quick_log` | NutritionController | `quickLog()` | `nutrition/quick-log.html.twig` |
| `/nutrition/planner` | `nutrition_meal_planner` | NutritionController | `mealPlanner()` | `nutrition/meal-planner.html.twig` |
| `/nutrition/recipes` | `nutrition_recipes` | NutritionController | `recipes()` | `nutrition/recipe-library.html.twig` |
| `/nutrition/goals` | `nutrition_goals` | NutritionController | `goals()` | `nutrition/goals.html.twig` |
| `/nutrition/messages/{id}` | `nutrition_messages` | NutritionController | `messages()` | `nutrition/messages.html.twig` |
| `/nutrition/consultation` | `nutrition_consultation` | NutritionController | `consultation()` | `nutrition/consultation.html.twig` |
| `/teleconsultation/waiting-room` | `teleconsultation_waiting_room` | TeleconsultationController | `waitingRoom()` | `teleconsultation/waiting-room.html.twig` |
| `/teleconsultation/consultation-room` | `teleconsultation_consultation_room` | TeleconsultationController | `consultationRoom()` | `teleconsultation/consultation-room.html.twig` |
| `/teleconsultation/soap-notes` | `teleconsultation_soap_notes` | TeleconsultationController | `soapNotes()` | `teleconsultation/soap-notes.html.twig` |
| `/teleconsultation/medical-tools` | `teleconsultation_medical_tools` | TeleconsultationController | `medicalTools()` | `teleconsultation/medical-tools.html.twig` |
| `/teleconsultation/prescription-writer` | `teleconsultation_prescription_writer` | TeleconsultationController | `prescriptionWriter()` | `teleconsultation/prescription-writer.html.twig` |

---

## 📝 IMPLEMENTATION NOTES

### Template Inheritance

All patient-facing pages extend from one of these layouts:

```twig
{# For authenticated pages #}
{% extends 'layouts/app.html.twig' %}

{# For authentication pages #}
{% extends 'layouts/auth.html.twig' %}

{# For standalone pages #}
{% extends 'base.html.twig' %}
```

### Sidebar Integration

The sidebar navigation is typically included in `layouts/app.html.twig` and dynamically highlights the current page based on the route.

### Asset Management

All pages load assets through Webpack Encore:

```twig
{% block javascripts %}
    {{ encore_entry_script_tags('app') }}
    {{ encore_entry_script_tags('nutrition') }}
{% endblock %}

{% block stylesheets %}
    {{ encore_entry_link_tags('app') }}
    {{ encore_entry_link_tags('nutrition-css') }}
{% endblock %}
```

### AJAX/API Patterns

The application uses AJAX for dynamic content:

```javascript
// Example: Fetch health metrics
fetch('/health/metrics')
    .then(response => response.json())
    .then(data => {
        // Update UI with metrics
    });
```

---

## 🎯 QUICK REFERENCE

### Most Important Routes for Patients

| Purpose | Route | Quick Access |
|---------|-------|--------------|
| Main Dashboard | `/appointment/patient-dashboard` | After login |
| Find Doctor | `/appointment/search-doctors` | Sidebar → Appointments |
| Health Records | `/health/dashboard` | Sidebar → Health |
| Nutrition | `/nutrition/` | Sidebar → Nutrition |
| Teleconsultation | `/teleconsultation/waiting-room` | Sidebar → Teleconsultation |

---

*Last Updated: 2026-02-04*
*WellCare Connect - Patient Front Office Documentation*
