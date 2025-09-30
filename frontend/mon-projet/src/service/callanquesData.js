// Données complètes sur les calanques de Marseille
// Faune et flore par secteur et sentier

// === FAUNE DES CALANQUES ===
export const fauna = {
  // Faune marine
  marine: {
    poissons: [
      {
        nom: "Girelle paon",
        nomScientifique: "Thalassoma pavo",
        description: "Poisson coloré très commun dans les eaux des calanques",
        habitat: "Fonds rocheux, herbiers de posidonie",
        observation: "Facile à observer en snorkeling"
      },
      {
        nom: "Sar commun",
        nomScientifique: "Diplodus sargus",
        description: "Poisson argenté avec des bandes noires verticales",
        habitat: "Zones rocheuses littorales",
        observation: "Visible depuis les rochers"
      },
      {
        nom: "Poulpe commun",
        nomScientifique: "Octopus vulgaris",
        description: "Céphalopode intelligent, maître du camouflage",
        habitat: "Failles rocheuses, fonds sableux",
        observation: "Plutôt nocturne, se cache dans les anfractuosités"
      }
    ],
    crustaces: [
      {
        nom: "Oursin violet",
        nomScientifique: "Paracentrotus lividus",
        description: "Échinoderme aux piquants violets",
        habitat: "Rochers battus par les vagues",
        observation: "Très commun, attention aux piquants !"
      },
      {
        nom: "Bernard-l'ermite",
        nomScientifique: "Clibanarius erythropus",
        description: "Crustacé vivant dans des coquilles vides",
        habitat: "Zones de marée, flaques rocheuses",
        observation: "Dans les petites cuvettes d'eau"
      }
    ]
  },
  
  // Faune terrestre
  terrestre: {
    oiseaux: [
      {
        nom: "Goéland leucophée",
        nomScientifique: "Larus michahellis",
        description: "Grand goéland méditerranéen",
        habitat: "Falaises, côtes rocheuses",
        observation: "Très commun, nicheur sur les falaises"
      },
      {
        nom: "Faucon pèlerin",
        nomScientifique: "Falco peregrinus",
        description: "Rapace le plus rapide du monde",
        habitat: "Falaises abruptes",
        observation: "Rare mais spectaculaire en vol piqué"
      },
      {
        nom: "Fauvette mélanocéphale",
        nomScientifique: "Sylvia melanocephala",
        description: "Petit passereau à tête noire (mâle)",
        habitat: "Maquis, garrigues",
        observation: "Chant mélodieux dans les buissons"
      },
      {
        nom: "Monticole bleu",
        nomScientifique: "Monticola solitarius",
        description: "Passereau bleu-gris, amateur de rochers",
        habitat: "Falaises, éboulis rocheux",
        observation: "Perché sur les rochers ensoleillés"
      }
    ],
    mammiferes: [
      {
        nom: "Sanglier",
        nomScientifique: "Sus scrofa",
        description: "Suidé sauvage méditerranéen",
        habitat: "Maquis dense, zones boisées",
        observation: "Traces et souilles, plutôt crépusculaire"
      },
      {
        nom: "Renard roux",
        nomScientifique: "Vulpes vulpes",
        description: "Canidé adaptable et intelligent",
        habitat: "Tous types d'habitats",
        observation: "Nocturne, traces sur les sentiers"
      }
    ],
    reptiles: [
      {
        nom: "Lézard des murailles",
        nomScientifique: "Podarcis muralis",
        description: "Petit lézard brun-gris très agile",
        habitat: "Murs, rochers, éboulis",
        observation: "Très commun au soleil sur les pierres"
      },
      {
        nom: "Tarente de Maurétanie",
        nomScientifique: "Tarentola mauritanica",
        description: "Gecko nocturne aux doigts adhésifs",
        habitat: "Falaises, constructions",
        observation: "Nocturne, sous les surplombs rocheux"
      }
    ]
  }
};

// === FLORE DES CALANQUES ===
export const flora = {
  // Flore maritime
  maritime: [
    {
      nom: "Posidonie",
      nomScientifique: "Posidonia oceanica",
      description: "Plante marine endémique de Méditerranée, pas une algue !",
      habitat: "Fonds marins sableux de 0 à 40m",
      importance: "Poumon de la Méditerranée, nurserie pour les poissons",
      observation: "Herbiers visibles en snorkeling, feuilles sur les plages"
    },
    {
      nom: "Salicorne",
      nomScientifique: "Salicornia europaea",
      description: "Plante succulente des milieux salés",
      habitat: "Zones humides salées, bord de mer",
      particularite: "Comestible, goût iodé",
      observation: "Petites touffes vertes charnues"
    }
  ],
  
  // Maquis méditerranéen
  maquis: [
    {
      nom: "Chêne kermès",
      nomScientifique: "Quercus coccifera",
      description: "Petit chêne épineux sempervirent",
      habitat: "Maquis, garrigues calcaires",
      particularite: "Feuilles piquantes, glands comestibles",
      observation: "Buisson dense, feuilles brillantes"
    },
    {
      nom: "Arbousier",
      nomScientifique: "Arbutus unedo",
      description: "Arbuste aux fruits rouges comestibles",
      habitat: "Maquis, bords de ruisseaux",
      particularite: "Fruits mûrs en automne, fleurs et fruits simultanément",
      observation: "Écorce rousse qui s'exfolie"
    },
    {
      nom: "Ciste de Montpellier",
      nomScientifique: "Cistus monspeliensis",
      description: "Arbuste à fleurs blanches",
      habitat: "Maquis dégradé, terrains siliceux",
      particularite: "Feuilles collantes, résistant au feu",
      observation: "Fleurs blanches au printemps"
    },
    {
      nom: "Romarin",
      nomScientifique: "Rosmarinus officinalis",
      description: "Plante aromatique aux fleurs bleues",
      habitat: "Garrigues, maquis secs",
      particularite: "Très parfumé, propriétés médicinales",
      observation: "Feuilles en aiguilles, parfum intense"
    },
    {
      nom: "Thym",
      nomScientifique: "Thymus vulgaris",
      description: "Plante aromatique tapissante",
      habitat: "Pelouses sèches, garrigues",
      particularite: "Mellifère, propriétés antiseptiques",
      observation: "Petites touffes parfumées"
    },
    {
      nom: "Genêt épineux",
      nomScientifique: "Calicotome spinosa",
      description: "Légumineuse épineuse à fleurs jaunes",
      habitat: "Maquis, garrigues",
      particularite: "Fixateur d'azote, très épineux",
      observation: "Fleurs jaunes vives au printemps"
    }
  ],
  
  // Flore des falaises
  falaises: [
    {
      nom: "Immortelle",
      nomScientifique: "Helichrysum stoechas",
      description: "Plante aux fleurs jaunes persistantes",
      habitat: "Falaises, rochers ensoleillés",
      particularite: "Fleurs qui ne fanent pas, très parfumée",
      observation: "Touffes argentées, odeur de curry"
    },
    {
      nom: "Fenouil sauvage",
      nomScientifique: "Foeniculum vulgare",
      description: "Ombellifère géante au parfum anisé",
      habitat: "Bords de chemins, friches",
      particularite: "Entièrement comestible et parfumé",
      observation: "Grande plante aux feuilles filiformes"
    },
    {
      nom: "Câprier",
      nomScientifique: "Capparis spinosa",
      description: "Arbuste retombant aux grandes fleurs blanches",
      habitat: "Fissures de rochers, murs",
      particularite: "Boutons floraux = câpres comestibles",
      observation: "Fleurs spectaculaires, étamines violettes"
    }
  ]
};

// === DONNÉES PAR SENTIER ===
export const trailsData = {
  trail1: {
    nom: "Sugiton → Morgiou",
    distance: "5 km",
    duree: "1h30-2h",
    difficulte: "Modérée",
    couleur: "blue",
    ecosystemes: ["maquis", "falaises", "maritime"],
    
    // Faune observable sur ce sentier
    fauneObservable: {
      oiseaux: ["Goéland leucophée", "Fauvette mélanocéphale", "Monticole bleu"],
      reptiles: ["Lézard des murailles", "Tarente de Maurétanie"],
      marine: ["Girelle paon", "Oursin violet", "Posidonie"]
    },
    
    // Flore caractéristique
    floreCaracteristique: {
      maquis: ["Chêne kermès", "Arbousier", "Romarin", "Thym"],
      falaises: ["Immortelle", "Câprier"],
      maritime: ["Posidonie", "Salicorne"]
    },
    
    // Points d'intérêt naturaliste
    pointsInteret: [
      {
        nom: "Belvédère de Sugiton",
        description: "Vue panoramique sur la mer et observation des oiseaux marins",
        coordonnees: [43.2235, 5.4460],
        faune: ["Goéland leucophée", "Faucon pèlerin"],
        flore: ["Immortelle", "Câprier"]
      },
      {
        nom: "Calanque de Morgiou",
        description: "Petit port traditionnel et herbiers de posidonie",
        coordonnees: [43.2090, 5.4440],
        faune: ["Girelle paon", "Sar commun", "Oursin violet"],
        flore: ["Posidonie", "Salicorne"]
      }
    ]
  },
  
  trail2: {
    nom: "Morgiou → Sormiou",
    distance: "3 km",
    duree: "1h-1h15",
    difficulte: "Facile",
    couleur: "green",
    ecosystemes: ["maquis", "maritime"],
    
    // Faune observable sur ce sentier
    fauneObservable: {
      oiseaux: ["Fauvette mélanocéphale", "Goéland leucophée"],
      reptiles: ["Lézard des murailles"],
      marine: ["Poulpe commun", "Bernard-l'ermite"]
    },
    
    // Flore caractéristique
    floreCaracteristique: {
      maquis: ["Ciste de Montpellier", "Genêt épineux", "Thym", "Romarin"],
      maritime: ["Posidonie", "Fenouil sauvage"]
    },
    
    // Points d'intérêt naturaliste
    pointsInteret: [
      {
        nom: "Sentier du maquis",
        description: "Traversée du maquis méditerranéen typique",
        coordonnees: [43.2000, 5.4420],
        faune: ["Fauvette mélanocéphale", "Lézard des murailles"],
        flore: ["Ciste de Montpellier", "Genêt épineux", "Thym"]
      },
      {
        nom: "Calanque de Sormiou",
        description: "Plage familiale et maquis préservé",
        coordonnees: [43.1950, 5.4400],
        faune: ["Poulpe commun", "Bernard-l'ermite"],
        flore: ["Posidonie", "Arbousier"]
      }
    ]
  }
};

// === FONCTIONS UTILITAIRES ===

// Obtenir toute la faune d'un écosystème
export const getFaunaByEcosystem = (ecosystem) => {
  switch(ecosystem) {
    case 'maritime':
    case 'marine':
      return fauna.marine;
    case 'terrestre':
      return fauna.terrestre;
    default:
      return { ...fauna.marine, ...fauna.terrestre };
  }
};

// Obtenir toute la flore d'un type d'habitat
export const getFloraByHabitat = (habitat) => {
  return flora[habitat] || [];
};

// Obtenir les données complètes d'un sentier
export const getTrailData = (trailId) => {
  return trailsData[trailId] || null;
};

// Obtenir la faune observable sur un sentier
export const getTrailFauna = (trailId) => {
  const trail = trailsData[trailId];
  if (!trail) return null;
  
  const result = {};
  Object.keys(trail.fauneObservable).forEach(category => {
    result[category] = trail.fauneObservable[category].map(animalName => {
      // Rechercher l'animal dans les données de faune
      for (const ecosystem of Object.values(fauna)) {
        for (const group of Object.values(ecosystem)) {
          if (Array.isArray(group)) {
            const animal = group.find(a => a.nom === animalName);
            if (animal) return animal;
          }
        }
      }
      return { nom: animalName };
    });
  });
  
  return result;
};

// Obtenir la flore observable sur un sentier
export const getTrailFlora = (trailId) => {
  const trail = trailsData[trailId];
  if (!trail) return null;
  
  const result = {};
  Object.keys(trail.floreCaracteristique).forEach(habitat => {
    result[habitat] = trail.floreCaracteristique[habitat].map(plantName => {
      // Rechercher la plante dans les données de flore
      for (const group of Object.values(flora)) {
        if (Array.isArray(group)) {
          const plant = group.find(p => p.nom === plantName);
          if (plant) return plant;
        }
      }
      return { nom: plantName };
    });
  });
  
  return result;
};

// Rechercher une espèce par nom
export const searchSpecies = (searchTerm) => {
  const results = { faune: [], flore: [] };
  const term = searchTerm.toLowerCase();
  
  // Recherche dans la faune
  Object.values(fauna).forEach(ecosystem => {
    Object.values(ecosystem).forEach(group => {
      if (Array.isArray(group)) {
        group.forEach(animal => {
          if (animal.nom.toLowerCase().includes(term) || 
              animal.nomScientifique?.toLowerCase().includes(term)) {
            results.faune.push(animal);
          }
        });
      }
    });
  });
  
  // Recherche dans la flore
  Object.values(flora).forEach(group => {
    if (Array.isArray(group)) {
      group.forEach(plant => {
        if (plant.nom.toLowerCase().includes(term) || 
            plant.nomScientifique?.toLowerCase().includes(term)) {
          results.flore.push(plant);
        }
      });
    }
  });
  
  return results;
};

export default {
  fauna,
  flora,
  trailsData,
  getFaunaByEcosystem,
  getFloraByHabitat,
  getTrailData,
  getTrailFauna,
  getTrailFlora,
  searchSpecies
};
