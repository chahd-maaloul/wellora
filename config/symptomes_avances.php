<?php
// config/symptomes_avances.php - Version enrichie

return [
    // NIVEAU ROUGE - URGENCES
    'chest_pain' => [
        'nom' => 'douleur thoracique',
        'mots_clefs' => ['poitrine', 'thorax', 'coeur', 'oppression', 'serrement', 'sternum', 'douleur thoracique'],
        'specialite' => 'cardiologue',
        'niveau' => 'rouge',
        'urgence' => 1,
        'questions' => [
            'La douleur est-elle intense (1 a 10) ?',
            'Avez-vous du mal a respirer ?',
            'La douleur s etend-elle au bras, machoire ou dos ?',
            'Avez-vous pris un medicament sans avis medical ?'
        ],
        'conseil' => "⚠️ **URGENCE ABSOLUE**\n\n• Ne prenez pas de medicament sans avis\n• Allongez-vous et ne faites pas d effort\n• Appelez le 15 immédiatement\n• Ne conduisez pas"
    ],
    'shortness_of_breath' => [
        'nom' => 'essoufflement',
        'mots_clefs' => ['respirer', 'souffle court', 'essoufflement', 'etouffe', 'asthme', 'difficulte a respirer'],
        'specialite' => 'pneumologue',
        'niveau' => 'rouge',
        'urgence' => 1,
        'questions' => [
            'Depuis combien de temps ?',
            'Est-ce arrive brutalement ?',
            'Avez-vous des sifflements ?',
            'Avez-vous pris un medicament sans avis ?'
        ],
        'conseil' => "⚠️ **URGENCE**\n\n• Asseyez-vous, ne vous allongez pas\n• Si vous avez un traitement d asthme, prenez-le\n• Appelez le 15 si ca ne passe pas\n• Ne prenez pas d autres medicaments"
    ],
    'stroke_signs' => [
        'nom' => 'signes d AVC',
        'mots_clefs' => ['paralysie', 'bouche de travers', 'difficulte a parler', 'avc', 'faiblesse soudaine'],
        'specialite' => 'urgences neurologiques',
        'niveau' => 'rouge',
        'urgence' => 1,
        'questions' => [
            'Les symptomes sont-ils apparus soudainement ?',
            'Un bras ou une jambe est-il faible ?',
            'Avez-vous des troubles de la parole ?',
            'Depuis combien de temps ?'
        ],
        'conseil' => "⚠️ **URGENCE**\n\n• Appelez le 15 immediatement\n• Notez l heure de debut\n• Ne donnez rien a boire ou manger"
    ],
    'severe_bleeding' => [
        'nom' => 'saignement important',
        'mots_clefs' => ['saigne beaucoup', 'hemorragie', 'saignement abondant', 'plaie profonde'],
        'specialite' => 'urgences',
        'niveau' => 'rouge',
        'urgence' => 1,
        'questions' => [
            'Le saignement est-il continu ?',
            'La plaie est-elle profonde ?',
            'Avez-vous des vertiges ?',
            'Depuis combien de temps ?'
        ],
        'conseil' => "⚠️ **URGENCE**\n\n• Compressez avec un tissu propre\n• Allongez-vous\n• Appelez le 15"
    ],
    'anaphylaxis' => [
        'nom' => 'reaction allergique severe',
        'mots_clefs' => ['gonflement visage', 'langue gonflee', 'choc allergique', 'anaphylaxie'],
        'specialite' => 'urgences',
        'niveau' => 'rouge',
        'urgence' => 1,
        'questions' => [
            'Avez-vous des difficultes a respirer ?',
            'Y a-t-il un gonflement du visage ou de la gorge ?',
            'Avez-vous un stylo d adrenaline ?',
            'Depuis combien de temps ?'
        ],
        'conseil' => "⚠️ **URGENCE**\n\n• Appelez le 15\n• Utilisez votre stylo d adrenaline si prescrit"
    ],
    'severe_abdominal_pain' => [
        'nom' => 'douleur abdominale intense',
        'mots_clefs' => ['douleur abdominale intense', 'mal de ventre violent', 'douleur a droite', 'appendicite'],
        'specialite' => 'urgences digestives',
        'niveau' => 'rouge',
        'urgence' => 1,
        'questions' => [
            'La douleur est-elle localisee ?',
            'Avez-vous vomi ?',
            'Avez-vous de la fievre ?',
            'Depuis combien de temps ?'
        ],
        'conseil' => "⚠️ **URGENCE**\n\n• Ne mangez pas\n• Consultez immediatement"
    ],
    'loss_of_consciousness' => [
        'nom' => 'perte de connaissance',
        'mots_clefs' => ['inconscient', 'perte de connaissance', 'evanouissement long'],
        'specialite' => 'urgences',
        'niveau' => 'rouge',
        'urgence' => 1,
        'questions' => [
            'La personne est-elle reveillee ?',
            'A-t-elle respire normalement ?',
            'A-t-elle eu un choc ?',
            'Depuis combien de temps ?'
        ],
        'conseil' => "⚠️ **URGENCE**\n\n• Appelez le 15\n• Placez en position laterale de securite"
    ],
    'severe_headache' => [
        'nom' => 'mal de tete violent',
        'mots_clefs' => ['mal de tete violent', 'pire mal de tete', 'raideur nuque', 'photophobie'],
        'specialite' => 'urgences neurologiques',
        'niveau' => 'rouge',
        'urgence' => 1,
        'questions' => [
            'La douleur est-elle apparue brutalement ?',
            'Avez-vous de la fievre ?',
            'Avez-vous une raideur de nuque ?',
            'Avez-vous des vomissements ?'
        ],
        'conseil' => "⚠️ **URGENCE**\n\n• Consultez immédiatement\n• Evitez la lumiere forte"
    ],

    // NIVEAU ORANGE - CONSULTATION RAPIDE
    'high_fever' => [
        'nom' => 'forte fievre',
        'mots_clefs' => ['fievre', 'temperature', '39', '40', 'frissons', 'hyperthermie'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Quelle est votre temperature exacte ?',
            'Depuis combien de temps ?',
            'Avez-vous pris un medicament ?',
            'Avez-vous d autres symptomes ?'
        ],
        'conseil' => "🌡️ **FORTE FIEVRE**\n\n• Buvez beaucoup d eau\n• Paracetamol si pas contre-indique\n• Tisane: thym ou camomille\n• Consultez si la fievre persiste"
    ],
    'persistent_headache' => [
        'nom' => 'mal de tete persistant',
        'mots_clefs' => ['mal de tete', 'maux de tete', 'migraine', 'cephalee', 'pulsatile'],
        'specialite' => 'neurologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous pris des medicaments ?',
            'Avez-vous des nausees ?',
            'La douleur est-elle pulsatile ?'
        ],
        'conseil' => "🤕 **SOULAGEMENT**\n\n• Repos dans le calme\n• Hydratez-vous\n• Tisane: menthe ou gingembre"
    ],
    'dizziness' => [
        'nom' => 'vertiges',
        'mots_clefs' => ['vertige', 'tete qui tourne', 'etourdissement', 'dizzy'],
        'specialite' => 'neurologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'La piece tourne-t-elle ?',
            'Depuis combien de temps ?',
            'Avez-vous mange aujourd hui ?',
            'Avez-vous des nausees ?'
        ],
        'conseil' => "🌀 **VERTIGES**\n\n• Asseyez-vous\n• Hydratez-vous\n• Evitez de conduire"
    ],
    'urinary_pain' => [
        'nom' => 'brulures urinaires',
        'mots_clefs' => ['brulures urinaires', 'douleur en urinant', 'cystite', 'uriner mal'],
        'specialite' => 'urologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Avez-vous de la fievre ?',
            'Depuis combien de temps ?',
            'Avez-vous des douleurs au dos ?',
            'Avez-vous du sang dans les urines ?'
        ],
        'conseil' => "🚽 **BRULURES URINAIRES**\n\n• Buvez de l eau\n• Consultez rapidement"
    ],
    'ear_infection' => [
        'nom' => 'douleur oreille',
        'mots_clefs' => ['oreille', 'otite', 'douleur oreille', 'bouchon oreille'],
        'specialite' => 'ORL',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous de la fievre ?',
            'Y a-t-il un ecoulement ?',
            'Avez-vous mal en avalant ?'
        ],
        'conseil' => "👂 **OREILLE**\n\n• Ne mettez rien dans l oreille\n• Consultez rapidement"
    ],
    'eye_pain' => [
        'nom' => 'douleur oculaire',
        'mots_clefs' => ['oeil', 'douleur oeil', 'oeil rouge', 'vision trouble'],
        'specialite' => 'ophtalmologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Avez-vous une baisse de vision ?',
            'L oeil est-il rouge ?',
            'Depuis combien de temps ?',
            'Avez-vous une sensibilite a la lumiere ?'
        ],
        'conseil' => "👁️ **OEIL**\n\n• Evitez de frotter\n• Consultez rapidement"
    ],
    'persistent_vomiting' => [
        'nom' => 'vomissements persistants',
        'mots_clefs' => ['vomissement', 'vomir', 'vomi plusieurs fois', 'vomit sans arret'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Combien de fois avez-vous vomi ?',
            'Avez-vous de la fievre ?',
            'Pouvez-vous boire ?',
            'Depuis combien de temps ?'
        ],
        'conseil' => "🤢 **VOMISSEMENTS**\n\n• Hydratez-vous par petites gorgées\n• Consultez si persistant"
    ],
    'rash_fever' => [
        'nom' => 'eruption avec fievre',
        'mots_clefs' => ['eruption', 'boutons + fievre', 'plaques rouges', 'rash'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis quand ?',
            'Avez-vous de la fievre ?',
            'Avez-vous pris un medicament recent ?',
            'L eruption demange-t-elle ?'
        ],
        'conseil' => "🌡️ **ERUPTION**\n\n• Evitez de gratter\n• Consultez rapidement"
    ],
    'back_pain_severe' => [
        'nom' => 'mal de dos intense',
        'mots_clefs' => ['lumbago', 'sciatique', 'douleur dos intense', 'dos bloque'],
        'specialite' => 'rhumatologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'La douleur descend-elle dans la jambe ?',
            'Depuis combien de temps ?',
            'Avez-vous pris un anti-inflammatoire ?',
            'Avez-vous un engourdissement ?'
        ],
        'conseil' => "🧘 **DOS**\n\n• Repos relatif\n• Evitez les efforts\n• Consultez rapidement"
    ],
    // NIVEAU VERT - CONSULTATION NORMALE
    'fatigue' => [
        'nom' => 'fatigue',
        'mots_clefs' => ['fatigue', 'fatiguee', 'epuise', 'lassitude'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Dormez-vous suffisamment ?',
            'Avez-vous change d alimentation ?',
            'Avez-vous pris des medicaments ?'
        ],
        'conseil' => "😴 **FATIGUE**\n\n• Repos\n• Hydratation\n• Tisane: verveine ou tilleul"
    ],
    'cough' => [
        'nom' => 'toux',
        'mots_clefs' => ['toux', 'tousser', 'gorge qui gratte', 'toux seche', 'toux grasse'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Toux seche ou grasse ?',
            'Depuis combien de temps ?',
            'Avez-vous de la fievre ?',
            'Avez-vous pris un sirop ?'
        ],
        'conseil' => "🍯 **TOUX**\n\n• Tisane thym + miel\n• Hydratation"
    ],
    'stomach_pain' => [
        'nom' => 'mal de ventre',
        'mots_clefs' => ['ventre', 'estomac', 'abdomen', 'mal au ventre', 'bassin'],
        'specialite' => 'gastro-enterologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Ou exactement ?',
            'Depuis combien de temps ?',
            'Avez-vous mange quelque chose d inhabituel ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🌿 **DIGESTION**\n\n• Tisane menthe ou camomille\n• Mangez leger"
    ],
    'nausea' => [
        'nom' => 'nausees',
        'mots_clefs' => ['nausee', 'mal au coeur', 'envie de vomir'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Avez-vous vomi ?',
            'Depuis combien de temps ?',
            'Avez-vous de la fievre ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🍵 **NAUSEES**\n\n• Tisane gingembre\n• Petites gorgées d eau"
    ],
    'allergy' => [
        'nom' => 'allergie',
        'mots_clefs' => ['allergie', 'eternuement', 'nez qui coule', 'yeux rouges', 'urticaire'],
        'specialite' => 'allergologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Avez-vous des difficultes a respirer ?',
            'Avez-vous pris un antihistaminique ?',
            'Savez-vous l allergene ?',
            'Y a-t-il un gonflement ?'
        ],
        'conseil' => "🌸 **ALLERGIE**\n\n• Evitez l allergene\n• Rincez le nez"
    ],
    'back_pain' => [
        'nom' => 'mal de dos',
        'mots_clefs' => ['dos', 'lombaire', 'colonne', 'douleur lombaire'],
        'specialite' => 'rhumatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Position soulageante ?',
            'Avez-vous pris un medicament ?',
            'La douleur descend-elle ?'
        ],
        'conseil' => "🧘 **DOS**\n\n• Bouillotte tiede\n• Bougez doucement"
    ],
    'sore_throat' => [
        'nom' => 'mal de gorge',
        'mots_clefs' => ['gorge', 'mal de gorge', 'angine', 'mal en avalant'],
        'specialite' => 'ORL',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous de la fievre ?',
            'Avez-vous des ganglions ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🫖 **GORGES**\n\n• Gargarismes eau salee\n• Tisane miel-citron"
    ],
    'runny_nose' => [
        'nom' => 'rhume',
        'mots_clefs' => ['nez qui coule', 'rhume', 'congestion', 'nez bouche'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous de la fievre ?',
            'Avez-vous des douleurs faciales ?',
            'Avez-vous pris un spray ?'
        ],
        'conseil' => "🤧 **RHUME**\n\n• Lavage de nez\n• Hydratation"
    ],
    'diarrhea' => [
        'nom' => 'diarrhee',
        'mots_clefs' => ['diarrhee', 'selles liquides', 'gastro'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous de la fievre ?',
            'Avez-vous bu suffisamment ?',
            'Avez-vous mangé recent ?'
        ],
        'conseil' => "💧 **DIARRHEE**\n\n• Hydratation\n• Alimentation legere"
    ],
    'constipation' => [
        'nom' => 'constipation',
        'mots_clefs' => ['constipation', 'pas de selles', 'ventre gonfle'],
        'specialite' => 'gastro-enterologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous des douleurs ?',
            'Buvez-vous assez d eau ?',
            'Avez-vous change d alimentation ?'
        ],
        'conseil' => "🥗 **CONSTIPATION**\n\n• Fibres\n• Hydratation"
    ],
    'heartburn' => [
        'nom' => 'brulures d estomac',
        'mots_clefs' => ['reflux', 'brulures estomac', 'acidite', 'remontees acides'],
        'specialite' => 'gastro-enterologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Apres les repas ?',
            'Depuis combien de temps ?',
            'Avez-vous pris un antiacide ?',
            'Avez-vous des aliments declencheurs ?'
        ],
        'conseil' => "🔥 **REUX**\n\n• Mangez léger\n• Evitez gras et epice"
    ],
    'skin_rash' => [
        'nom' => 'eruption cutanee',
        'mots_clefs' => ['boutons', 'eruption', 'plaques rouges', 'demangeaisons'],
        'specialite' => 'dermatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Cela demange-t-il ?',
            'Avez-vous change de produit ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🌿 **PEAU**\n\n• Evitez de gratter\n• Hydratez la peau"
    ],
    'eczema' => [
        'nom' => 'eczema',
        'mots_clefs' => ['eczema', 'peau seche', 'plaques seches'],
        'specialite' => 'dermatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Avez-vous une allergie connue ?',
            'Avez-vous change de savon ?',
            'Cela gratte-t-il ?'
        ],
        'conseil' => "🧴 **ECZEMA**\n\n• Hydratez la peau\n• Evitez savon agressif"
    ],
    'acne' => [
        'nom' => 'acne',
        'mots_clefs' => ['acne', 'boutons visage', 'points noirs'],
        'specialite' => 'dermatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Avez-vous change de produits ?',
            'Avez-vous des douleurs ?',
            'Age approximatif ?'
        ],
        'conseil' => "🧼 **ACNE**\n\n• Nettoyage doux\n• Evitez de percer"
    ],
    'joint_pain' => [
        'nom' => 'douleur articulaire',
        'mots_clefs' => ['articulation', 'douleur genou', 'douleur poignet', 'douleur epaule'],
        'specialite' => 'rhumatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Gonflement ?',
            'Douleur au repos ou mouvement ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🦴 **ARTICULATIONS**\n\n• Repos\n• Froid si gonflement"
    ],
    'muscle_pain' => [
        'nom' => 'douleurs musculaires',
        'mots_clefs' => ['courbatures', 'douleur musculaire', 'contracture'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Apres effort ?',
            'Depuis combien de temps ?',
            'Avez-vous de la fievre ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "💪 **MUSCLES**\n\n• Repos\n• Hydratation"
    ],
    'insomnia' => [
        'nom' => 'insomnie',
        'mots_clefs' => ['insomnie', 'pas dormir', 'sommeil difficile'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Stress recent ?',
            'Cafe le soir ?',
            'Avez-vous pris un somnifere ?'
        ],
        'conseil' => "🌙 **SOMMEIL**\n\n• Evitez ecrans le soir\n• Tisane tilleul"
    ],
    'anxiety' => [
        'nom' => 'anxiete',
        'mots_clefs' => ['anxiete', 'angoisse', 'stress', 'crise d angoisse'],
        'specialite' => 'psychologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous des palpitations ?',
            'Un evenement declencheur ?',
            'Avez-vous deja consulte ?'
        ],
        'conseil' => "🧘 **ANXIETE**\n\n• Respiration lente\n• Repos"
    ],
    'depression' => [
        'nom' => 'humeur triste',
        'mots_clefs' => ['deprime', 'triste', 'depression', 'sans energie'],
        'specialite' => 'psychologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Sommeil perturbe ?',
            'Perte d appetit ?',
            'Souhaitez-vous parler a un professionnel ?'
        ],
        'conseil' => "💬 **SOUTIEN**\n\n• Parlez a un proche\n• Consultez si cela persiste"
    ],
    'sinusitis' => [
        'nom' => 'sinusite',
        'mots_clefs' => ['sinus', 'douleur faciale', 'pression visage'],
        'specialite' => 'ORL',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous de la fievre ?',
            'Nez bouche ?',
            'Douleur aux pommettes ?'
        ],
        'conseil' => "🧼 **SINUS**\n\n• Lavage de nez\n• Hydratation"
    ],
    'dental_pain' => [
        'nom' => 'douleur dentaire',
        'mots_clefs' => ['dent', 'mal aux dents', 'carie', 'gingivite'],
        'specialite' => 'dentiste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Douleur au froid/chaud ?',
            'Gonflement ?',
            'Avez-vous pris un antalgique ?'
        ],
        'conseil' => "🦷 **DENT**\n\n• Rince-bouche\n• Consultez un dentiste"
    ],
    'menstrual_pain' => [
        'nom' => 'douleurs menstruelles',
        'mots_clefs' => ['regles douloureuses', 'crampes', 'douleur regles'],
        'specialite' => 'gynecologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Douleur habituelle ?',
            'Saignements abondants ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🌸 **REGLES**\n\n• Chaleur locale\n• Repos"
    ],
    'pregnancy_nausea' => [
        'nom' => 'nausees grossesse',
        'mots_clefs' => ['nausee grossesse', 'enceinte', 'grossesse', 'vomissements matinaux'],
        'specialite' => 'sage-femme',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Pouvez-vous boire ?',
            'Vomissements frequents ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🤰 **GROSSESSE**\n\n• Petits repas\n• Hydratation"
    ],
    'eye_dry' => [
        'nom' => 'yeux secs',
        'mots_clefs' => ['yeux secs', 'brulure oeil', 'picotement oeil'],
        'specialite' => 'ophtalmologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Travail sur ecrans ?',
            'Depuis combien de temps ?',
            'Utilisez-vous des lentilles ?',
            'Avez-vous pris des gouttes ?'
        ],
        'conseil' => "👁️ **YEUX SECS**\n\n• Pauses ecran\n• Larmes artificielles"
    ],
    'nasal_bleed' => [
        'nom' => 'saignement de nez',
        'mots_clefs' => ['saignement nez', 'nez qui saigne', 'epistaxis'],
        'specialite' => 'ORL',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Le saignement s arrete-t-il ?',
            'Depuis combien de temps ?',
            'Avez-vous un choc ?',
            'Prenez-vous un traitement ?'
        ],
        'conseil' => "🩸 **NEZ**\n\n• Penchez la tete en avant\n• Pincez le nez"
    ],
    'palpitations' => [
        'nom' => 'palpitations',
        'mots_clefs' => ['palpitations', 'coeur qui bat vite', 'tachycardie'],
        'specialite' => 'cardiologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis combien de temps ?',
            'Avez-vous des douleurs thoraciques ?',
            'Avez-vous bu du cafe ou alcool ?',
            'Avez-vous des vertiges ?'
        ],
        'conseil' => "💓 **PALPITATIONS**\n\n• Reposez-vous\n• Consultez si persistant"
    ],
    'leg_swelling' => [
        'nom' => 'jambe gonflee',
        'mots_clefs' => ['jambe gonflee', 'oedeme', 'gonflement jambe'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Une seule jambe ?',
            'Depuis combien de temps ?',
            'Douleur ?',
            'Avez-vous voyage recemment ?'
        ],
        'conseil' => "🦵 **GONFLEMENT**\n\n• Surveillez\n• Consultez rapidement"
    ],
    'skin_infection' => [
        'nom' => 'infection cutanee',
        'mots_clefs' => ['rougeur', 'chaleur locale', 'pus', 'infection peau'],
        'specialite' => 'dermatologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis quand ?',
            'Douleur ?',
            'Fievre ?',
            'Avez-vous une plaie ?'
        ],
        'conseil' => "🩹 **INFECTION**\n\n• Nettoyez la zone\n• Consultez"
    ],
    'burn' => [
        'nom' => 'brulure',
        'mots_clefs' => ['brulure', 'brule', 'cloque', 'brulure cuisine'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Surface touchee ?',
            'Profondeur ?',
            'Douleur ?',
            'Depuis quand ?'
        ],
        'conseil' => "🔥 **BRULURE**\n\n• Rincez a l eau froide\n• Ne percez pas les cloques"
    ],
    'sprain' => [
        'nom' => 'entorse',
        'mots_clefs' => ['entorse', 'cheville tordue', 'poignet tordu'],
        'specialite' => 'orthopediste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Gonflement ?',
            'Pouvez-vous poser le pied ?',
            'Avez-vous glace ?'
        ],
        'conseil' => "🧊 **ENTORSE**\n\n• Glace\n• Sur-elevation"
    ],
    'neck_pain' => [
        'nom' => 'douleur cervicale',
        'mots_clefs' => ['cou', 'cervicales', 'torticolis'],
        'specialite' => 'rhumatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Mouvement limite ?',
            'Apres un faux mouvement ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🧣 **CERVICALES**\n\n• Chaleur locale\n• Etirements doux"
    ],
    'rash_itchy' => [
        'nom' => 'demangeaisons',
        'mots_clefs' => ['demangeaisons', 'gratte', 'prurit'],
        'specialite' => 'dermatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Zone localisee ?',
            'Nouveau produit ?',
            'Avez-vous une allergie ?'
        ],
        'conseil' => "🧴 **DEMANGEAISONS**\n\n• Hydratation\n• Evitez les irritants"
    ],
    'hemorrhoids' => [
        'nom' => 'hemorroides',
        'mots_clefs' => ['hemorroides', 'douleur anale', 'sang selles'],
        'specialite' => 'gastro-enterologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Douleur ou saignement ?',
            'Depuis quand ?',
            'Constipation ?',
            'Avez-vous pris un traitement local ?'
        ],
        'conseil' => "🪑 **HEMORROIDES**\n\n• Alimentation riche en fibres\n• Hydratation"
    ],
    'hair_loss' => [
        'nom' => 'chute de cheveux',
        'mots_clefs' => ['chute cheveux', 'perte cheveux', 'cheveux qui tombent'],
        'specialite' => 'dermatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Stress recent ?',
            'Changement d alimentation ?',
            'Traitement medicamenteux ?'
        ],
        'conseil' => "💇 **CHEVEUX**\n\n• Alimentation equilibree\n• Consultez si important"
    ],
    'weight_loss' => [
        'nom' => 'perte de poids',
        'mots_clefs' => ['perte de poids', 'maigrir', 'amaigrissement'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis combien de temps ?',
            'Appetit diminue ?',
            'Fatigue associee ?',
            'Avez-vous change d alimentation ?'
        ],
        'conseil' => "⚖️ **POIDS**\n\n• Surveillez\n• Consultez"
    ],
    'weight_gain' => [
        'nom' => 'prise de poids',
        'mots_clefs' => ['prise de poids', 'grossir', 'poids augmente'],
        'specialite' => 'endocrinologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Changements alimentaires ?',
            'Activite physique ?',
            'Fatigue associee ?'
        ],
        'conseil' => "🥗 **POIDS**\n\n• Equilibre alimentaire\n• Activite reguliere"
    ],
    'thirst' => [
        'nom' => 'soif excessive',
        'mots_clefs' => ['soif', 'soif excessive', 'boire beaucoup'],
        'specialite' => 'endocrinologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis combien de temps ?',
            'Urinez-vous souvent ?',
            'Perte de poids ?',
            'Fatigue ?'
        ],
        'conseil' => "💧 **SOIF**\n\n• Hydratation\n• Consultez pour bilan"
    ],
    'skin_bruise' => [
        'nom' => 'bleus inexpliques',
        'mots_clefs' => ['bleus', 'hematome', 'ecchymose'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis combien de temps ?',
            'Choc recent ?',
            'Autres saignements ?',
            'Traitement anticoagulant ?'
        ],
        'conseil' => "🩹 **BLEUS**\n\n• Surveillez\n• Consultez si frequents"
    ],
    'constipation_child' => [
        'nom' => 'constipation enfant',
        'mots_clefs' => ['enfant constipe', 'constipation enfant'],
        'specialite' => 'pediatre',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Douleur abdominale ?',
            'Hydratation suffisante ?',
            'Changement d alimentation ?'
        ],
        'conseil' => "👶 **ENFANT**\n\n• Hydratation\n• Fibres"
    ],
    'child_fever' => [
        'nom' => 'fievre enfant',
        'mots_clefs' => ['fievre enfant', 'bebe fievre', 'temperature enfant'],
        'specialite' => 'pediatre',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Temperature exacte ?',
            'Age de l enfant ?',
            'Depuis combien de temps ?',
            'Autres symptomes ?'
        ],
        'conseil' => "🍼 **ENFANT**\n\n• Hydratation\n• Consultez rapidement"
    ],
    'flu_like' => [
        'nom' => 'etat grippal',
        'mots_clefs' => ['grippe', 'etat grippal', 'courbatures + fievre'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Fievre ?',
            'Toux ?',
            'Douleurs musculaires ?'
        ],
        'conseil' => "🤒 **GRIPPE**\n\n• Repos\n• Hydratation"
    ],
    'covid_like' => [
        'nom' => 'symptomes respiratoires',
        'mots_clefs' => ['perte gout', 'perte odorat', 'covid', 'symptomes respiratoires'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Fievre ?',
            'Toux ?',
            'Contact recent ?'
        ],
        'conseil' => "🫁 **RESPIRATOIRE**\n\n• Repos\n• Hydratation"
    ],
    'sore_muscle' => [
        'nom' => 'courbatures',
        'mots_clefs' => ['courbatures', 'douleurs corps', 'muscles douloureux'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Apres effort ?',
            'Fievre ?',
            'Hydratation ?'
        ],
        'conseil' => "💪 **COURBATURES**\n\n• Repos\n• Hydratation"
    ],
    'itchy_eyes' => [
        'nom' => 'yeux qui grattent',
        'mots_clefs' => ['yeux qui grattent', 'yeux rouges', 'conjonctivite'],
        'specialite' => 'ophtalmologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Un oeil ou les deux ?',
            'Ecoulement ?',
            'Lentilles ?',
            'Depuis quand ?'
        ],
        'conseil' => "👁️ **YEUX**\n\n• Lavez les mains\n• Evitez de frotter"
    ],
    'mouth_ulcer' => [
        'nom' => 'aphtes',
        'mots_clefs' => ['aphte', 'ulcere bouche', 'douleur bouche'],
        'specialite' => 'dentiste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Douleur importante ?',
            'Difficulte a manger ?',
            'Avez-vous change d alimentation ?'
        ],
        'conseil' => "😬 **APHTES**\n\n• Rince-bouche\n• Evitez aliments acides"
    ],
    'hoarseness' => [
        'nom' => 'enrouement',
        'mots_clefs' => ['voix enrouee', 'enrouement', 'perte voix'],
        'specialite' => 'ORL',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Toux ?',
            'Fievre ?',
            'Surmenage vocal ?'
        ],
        'conseil' => "🎤 **VOIX**\n\n• Repos vocal\n• Hydratation"
    ],
    'indigestion' => [
        'nom' => 'indigestion',
        'mots_clefs' => ['indigestion', 'ballonnements', 'digestion difficile'],
        'specialite' => 'gastro-enterologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Apres quel repas ?',
            'Douleur ?',
            'Ballonnements ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🥣 **DIGESTION**\n\n• Repas leger\n• Tisane menthe"
    ],
    'cold_hands' => [
        'nom' => 'mains froides',
        'mots_clefs' => ['mains froides', 'extremites froides'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Stress ?',
            'Fumez-vous ?',
            'Autres symptomes ?'
        ],
        'conseil' => "🧤 **CIRCULATION**\n\n• Rechauffez-vous\n• Bougez"
    ],
    'leg_cramp' => [
        'nom' => 'crampes',
        'mots_clefs' => ['crampe', 'crampes jambe', 'crampe nocturne'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'La nuit ?',
            'Hydratation ?',
            'Activite physique ?',
            'Depuis quand ?'
        ],
        'conseil' => "🦵 **CRAMPES**\n\n• Etirements\n• Hydratation"
    ],
    'constipation_bloating' => [
        'nom' => 'ballonnements',
        'mots_clefs' => ['ballonnements', 'ventre gonfle', 'gaz'],
        'specialite' => 'gastro-enterologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Apres repas ?',
            'Aliments declencheurs ?',
            'Transit ?'
        ],
        'conseil' => "💨 **BALLONNEMENTS**\n\n• Mangez lentement\n• Evitez boissons gazeuses"
    ],
    'urine_frequency' => [
        'nom' => 'urines frequentes',
        'mots_clefs' => ['uriner souvent', 'envie frequente', 'pollakiurie'],
        'specialite' => 'urologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis quand ?',
            'Brulures ?',
            'Soif ?',
            'Douleur bas ventre ?'
        ],
        'conseil' => "🚽 **URINES**\n\n• Hydratation\n• Consultez"
    ],
    'rash_hives' => [
        'nom' => 'urticaire',
        'mots_clefs' => ['urticaire', 'plaques rouges qui grattent'],
        'specialite' => 'allergologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Avez-vous un allergene ?',
            'Avez-vous pris un medicament ?',
            'Gonflement visage ?'
        ],
        'conseil' => "🌸 **URTICAIRE**\n\n• Evitez l allergene\n• Consultez si persiste"
    ],
    'dry_cough' => [
        'nom' => 'toux seche',
        'mots_clefs' => ['toux seche', 'toux irritative'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Irritation gorge ?',
            'Fievre ?',
            'Traitement ?'
        ],
        'conseil' => "🍯 **TOUX SECHE**\n\n• Miel\n• Hydratation"
    ],
    'productive_cough' => [
        'nom' => 'toux grasse',
        'mots_clefs' => ['toux grasse', 'crachat', 'expectoration'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Couleur des crachats ?',
            'Fievre ?',
            'Douleur thoracique ?'
        ],
        'conseil' => "🫁 **TOUX GRASSE**\n\n• Hydratation\n• Consultez si persiste"
    ],
    'itchy_throat' => [
        'nom' => 'gorge qui gratte',
        'mots_clefs' => ['gorge qui gratte', 'irritation gorge'],
        'specialite' => 'ORL',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis combien de temps ?',
            'Toux ?',
            'Fievre ?',
            'Allergie connue ?'
        ],
        'conseil' => "🍵 **GORGES**\n\n• Hydratation\n• Tisane douce"
    ],
    'low_back_pain' => [
        'nom' => 'douleur lombaire',
        'mots_clefs' => ['lombaire', 'bas du dos', 'douleur lombaire'],
        'specialite' => 'rhumatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Apres effort ?',
            'Douleur a la jambe ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🧘 **LOMBAIRES**\n\n• Chaleur locale\n• Etirements"
    ],
    'cold' => [
        'nom' => 'rhino-pharyngite',
        'mots_clefs' => ['rhino', 'rhino-pharyngite', 'nez bouche + gorge'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Fievre ?',
            'Toux ?',
            'Maux de tete ?'
        ],
        'conseil' => "🤧 **RHINO**\n\n• Repos\n• Hydratation"
    ],
    'nasal_congestion' => [
        'nom' => 'nez bouche',
        'mots_clefs' => ['nez bouche', 'congestion nasale'],
        'specialite' => 'ORL',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Douleur faciale ?',
            'Fievre ?',
            'Utilisez-vous un spray ?'
        ],
        'conseil' => "🌬️ **NEZ**\n\n• Lavage de nez\n• Hydratation"
    ],
    'itchy_skin' => [
        'nom' => 'peau qui gratte',
        'mots_clefs' => ['peau qui gratte', 'prurit', 'demangeaison peau'],
        'specialite' => 'dermatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Zone localisee ?',
            'Nouveau produit ?',
            'Avez-vous une allergie ?'
        ],
        'conseil' => "🧴 **PEAU**\n\n• Hydratez\n• Evitez irritants"
    ],
    'eye_redness' => [
        'nom' => 'oeil rouge',
        'mots_clefs' => ['oeil rouge', 'conjonctivite', 'yeux rouges'],
        'specialite' => 'ophtalmologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Douleur ?',
            'Ecoulement ?',
            'Depuis quand ?',
            'Lentilles ?'
        ],
        'conseil' => "👁️ **OEIL ROUGE**\n\n• Hygiène\n• Evitez frottements"
    ],
    'ear_ringing' => [
        'nom' => 'acouphenes',
        'mots_clefs' => ['acouphenes', 'sifflement oreille', 'bourdonnement'],
        'specialite' => 'ORL',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Exposition au bruit ?',
            'Douleur ?',
            'Perte d audition ?'
        ],
        'conseil' => "🔔 **ACOUphènes**\n\n• Evitez le bruit\n• Consultez si persiste"
    ],
    'mouth_pain' => [
        'nom' => 'douleur bouche',
        'mots_clefs' => ['douleur bouche', 'gingivite', 'gencive douloureuse'],
        'specialite' => 'dentiste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Saignement ?',
            'Mauvaise haleine ?',
            'Avez-vous consulte ?'
        ],
        'conseil' => "🪥 **BOUCHE**\n\n• Hygiène buccale\n• Consultez"
    ],
    'shoulder_pain' => [
        'nom' => 'douleur epaule',
        'mots_clefs' => ['epaule', 'douleur epaule'],
        'specialite' => 'orthopediste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Apres effort ?',
            'Mouvement limite ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🏋️ **EPAULE**\n\n• Repos\n• Froid si douleur"
    ],
    'knee_pain' => [
        'nom' => 'douleur genou',
        'mots_clefs' => ['genou', 'douleur genou'],
        'specialite' => 'orthopediste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Gonflement ?',
            'Traumatisme ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🦵 **GENOU**\n\n• Repos\n• Glace"
    ],
    'ankle_pain' => [
        'nom' => 'douleur cheville',
        'mots_clefs' => ['cheville', 'douleur cheville'],
        'specialite' => 'orthopediste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Entorse ?',
            'Gonflement ?',
            'Douleur en appui ?',
            'Depuis quand ?'
        ],
        'conseil' => "🧊 **CHEVILLE**\n\n• Glace\n• Sur-elever"
    ],
    'hand_pain' => [
        'nom' => 'douleur main',
        'mots_clefs' => ['main', 'douleur main', 'doigt douloureux'],
        'specialite' => 'orthopediste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Traumatisme ?',
            'Gonflement ?',
            'Depuis quand ?',
            'Mouvement limite ?'
        ],
        'conseil' => "✋ **MAIN**\n\n• Repos\n• Glace"
    ],
    'foot_pain' => [
        'nom' => 'douleur pied',
        'mots_clefs' => ['pied', 'douleur pied', 'talon douloureux'],
        'specialite' => 'orthopediste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Apres marche ?',
            'Douleur au talon ?',
            'Chaussures recentes ?'
        ],
        'conseil' => "🦶 **PIED**\n\n• Repos\n• Chaussures adaptees"
    ],
    'hip_pain' => [
        'nom' => 'douleur hanche',
        'mots_clefs' => ['hanche', 'douleur hanche'],
        'specialite' => 'rhumatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Douleur a la marche ?',
            'Raideur ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🦴 **HANCHE**\n\n• Repos relatif\n• Consultez si persiste"
    ],
    'elbow_pain' => [
        'nom' => 'douleur coude',
        'mots_clefs' => ['coude', 'douleur coude'],
        'specialite' => 'orthopediste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Apres effort ?',
            'Gonflement ?',
            'Mouvement limite ?'
        ],
        'conseil' => "💪 **COUDE**\n\n• Repos\n• Glace"
    ],
    'wrist_pain' => [
        'nom' => 'douleur poignet',
        'mots_clefs' => ['poignet', 'douleur poignet'],
        'specialite' => 'orthopediste',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Traumatisme ?',
            'Gonflement ?',
            'Depuis quand ?',
            'Mouvement limite ?'
        ],
        'conseil' => "🖐️ **POIGNET**\n\n• Repos\n• Glace"
    ],
    'shoulder_stiffness' => [
        'nom' => 'raideur epaule',
        'mots_clefs' => ['raideur epaule', 'epaule bloquee'],
        'specialite' => 'rhumatologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Mouvement limite ?',
            'Douleur la nuit ?',
            'Avez-vous pris un medicament ?'
        ],
        'conseil' => "🧘 **EPAULE**\n\n• Repos\n• Etirements doux"
    ],
    'low_mood' => [
        'nom' => 'baisse de moral',
        'mots_clefs' => ['baisse moral', 'moral bas', 'tristesse'],
        'specialite' => 'psychologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Sommeil ?',
            'Appetit ?',
            'Souhaitez-vous parler ?'
        ],
        'conseil' => "💬 **MORAL**\n\n• Parlez a un proche\n• Consultez si persiste"
    ],
    'burning_urination' => [
        'nom' => 'brulure en urinant',
        'mots_clefs' => ['brulure urinant', 'uriner brule', 'douleur pipi'],
        'specialite' => 'urologue',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Fievre ?',
            'Sang dans les urines ?',
            'Douleur dos ?',
            'Depuis quand ?'
        ],
        'conseil' => "🚽 **URINES**\n\n• Hydratation\n• Consultez"
    ],
    'sore_throat_child' => [
        'nom' => 'mal de gorge enfant',
        'mots_clefs' => ['enfant mal gorge', 'gorge enfant'],
        'specialite' => 'pediatre',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Age de l enfant ?',
            'Fievre ?',
            'Depuis quand ?',
            'Difficulte a avaler ?'
        ],
        'conseil' => "👶 **ENFANT**\n\n• Hydratation\n• Consultez si fievre"
    ],
    'itchy_nose' => [
        'nom' => 'nez qui gratte',
        'mots_clefs' => ['nez qui gratte', 'demangeaisons nez'],
        'specialite' => 'allergologue',
        'niveau' => 'vert',
        'urgence' => 3,
        'questions' => [
            'Depuis quand ?',
            'Allergie connue ?',
            'Eternuements ?',
            'Yeux qui grattent ?'
        ],
        'conseil' => "🌼 **ALLERGIE**\n\n• Evitez allergenes\n• Lavage de nez"
    ],
    'sweating_night' => [
        'nom' => 'sueurs nocturnes',
        'mots_clefs' => ['sueurs nocturnes', 'transpiration nuit'],
        'specialite' => 'medecin generaliste',
        'niveau' => 'orange',
        'urgence' => 2,
        'questions' => [
            'Depuis quand ?',
            'Fievre ?',
            'Perte de poids ?',
            'Fatigue ?'
        ],
        'conseil' => "🌙 **SUEURS**\n\n• Surveillez\n• Consultez"
    ]
];
?>

