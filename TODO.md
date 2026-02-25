# Task: Create Nutritionist Templates

## Problem
The templates/nutritioniste/ directory is empty but the NutritionController has routes that reference templates from this directory.

## Solution
Create Twig templates for the nutritionist module based on the Alpine.js data in assets/js/nutritioniste-dashboard.js

## Tasks
- [ ] 1. Create templates/nutritioniste/dashboard.html.twig
- [ ] 2. Create templates/nutritioniste/patient-list.html.twig
- [ ] 3. Create templates/nutritioniste/patient-detail.html.twig
- [ ] 4. Create templates/nutritioniste/meal-plan-builder.html.twig
- [ ] 5. Create templates/nutritioniste/nutrition-analysis.html.twig
- [ ] 6. Create templates/nutritioniste/communication.html.twig
- [ ] 7. Create templates/nutritioniste/reporting.html.twig
# TODO: Fix Goal Update Validation Errors

## Steps to Complete
- [ ] Update difficultyLevel choices in GoalType.php to match Goal.php entity (Beginner, Intermediate, Advanced)
- [ ] Update targetAudience choices in GoalType.php to match Goal.php entity (General, Weight Loss, Muscle Gain, Endurance, Flexibility, Rehabilitation)
- [ ] Update frequency choices in GoalType.php to match Goal.php entity (Daily, Weekly, Monthly, Custom)
- [ ] Test the update functionality in GoalController to ensure no validation errors
