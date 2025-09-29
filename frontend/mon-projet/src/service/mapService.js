// Service pour gérer la carte Leaflet des calanques
class MapService {
  constructor() {
    this.map = null;
    this.markers = [];
    this.trails = [];
    
    // Points des différentes calanques
    this.points = {
      sugiton: [43.2235, 5.4460],
      morgiou: [43.2090, 5.4440],
      sormiou: [43.1950, 5.4400]
    };
    
    // Configuration des sentiers
    this.trailsConfig = [
      {
        name: 'trail1',
        path: [
          this.points.sugiton,
          [43.2210, 5.4455],
          [43.2180, 5.4450],
          this.points.morgiou
        ],
        color: 'blue',
        weight: 4,
        popup: "Sugiton → Morgiou : 5 km, 1h30-2h"
      },
      {
        name: 'trail2',
        path: [
          this.points.morgiou,
          [43.2060, 5.4435],
          [43.2000, 5.4420],
          this.points.sormiou
        ],
        color: 'green',
        weight: 4,
        popup: "Morgiou → Sormiou : 3 km, 1h-1h15"
      }
    ];
  }

  // Initialiser la carte
  initMap(containerId, center = [43.2020, 5.4400], zoom = 13) {
    // Initialisation de la carte (centrée pour voir toutes les calanques)
    this.map = L.map(containerId).setView(center, zoom);

    // Ajouter les tuiles (OpenStreetMap ici)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
    }).addTo(this.map);

    return this.map;
  }

  // Ajouter un marqueur
  addMarker(lat, lng, popupText) {
    const marker = L.marker([lat, lng]).addTo(this.map);
    marker.bindPopup(popupText);
    this.markers.push(marker);
    return marker;
  }

  // Ajouter tous les marqueurs des calanques
  addAllCalanqueMarkers() {
    // Marqueur Sugiton
    this.addMarker(
      this.points.sugiton[0], 
      this.points.sugiton[1], 
      "<b>Calanque de Sugiton</b><br>Point de départ vers Morgiou"
    );
    
    // Marqueur Morgiou
    this.addMarker(
      this.points.morgiou[0], 
      this.points.morgiou[1], 
      "<b>Calanque de Morgiou</b><br>Connexion vers Sugiton et Sormiou"
    );
    
    // Marqueur Sormiou
    this.addMarker(
      this.points.sormiou[0], 
      this.points.sormiou[1], 
      "<b>Calanque de Sormiou</b><br>Terminus du parcours - Connexion depuis Morgiou"
    );
  }

  // Ajouter les marqueurs par défaut (pour compatibilité)
  addDefaultCalanqueMarkers() {
    this.addAllCalanqueMarkers();
  }

  // Ajouter un sentier spécifique
  addTrail(coordinates, options = {color: 'blue', weight: 4, opacity: 0.7}) {
    const trail = L.polyline(coordinates, options).addTo(this.map);
    this.trails.push(trail);
    return trail;
  }

  // Ajouter un sentier avec popup
  addTrailWithPopup(coordinates, options, popupText) {
    const trail = L.polyline(coordinates, options).addTo(this.map);
    if (popupText) {
      trail.bindPopup(popupText);
    }
    this.trails.push(trail);
    return trail;
  }

  // Ajouter tous les sentiers configurés
  addAllTrails() {
    this.trailsConfig.forEach(trailConfig => {
      this.addTrailWithPopup(
        trailConfig.path,
        {
          color: trailConfig.color,
          weight: trailConfig.weight,
          opacity: 0.7
        },
        trailConfig.popup
      );
    });
  }

  // Ajouter un sentier spécifique par nom
  addTrailByName(trailName) {
    const trailConfig = this.trailsConfig.find(trail => trail.name === trailName);
    if (trailConfig) {
      return this.addTrailWithPopup(
        trailConfig.path,
        {
          color: trailConfig.color,
          weight: trailConfig.weight,
          opacity: 0.7
        },
        trailConfig.popup
      );
    }
    return null;
  }

  // Ajouter le sentier par défaut (pour compatibilité)
  addDefaultTrail() {
    return this.addTrailByName('trail1');
  }

  // Initialiser la carte complète avec tous les marqueurs et sentiers
  initFullMap(containerId) {
    this.initMap(containerId);
    this.addAllCalanqueMarkers();
    this.addAllTrails();
    return this.map;
  }

  // Initialiser la carte avec seulement certains sentiers
  initMapWithTrails(containerId, trailNames = ['trail1']) {
    this.initMap(containerId);
    this.addAllCalanqueMarkers();
    trailNames.forEach(trailName => {
      this.addTrailByName(trailName);
    });
    return this.map;
  }

  // Nettoyer tous les marqueurs
  clearMarkers() {
    this.markers.forEach(marker => this.map.removeLayer(marker));
    this.markers = [];
  }

  // Nettoyer tous les sentiers
  clearTrails() {
    this.trails.forEach(trail => this.map.removeLayer(trail));
    this.trails = [];
  }

  // Nettoyer tout
  clearAll() {
    this.clearMarkers();
    this.clearTrails();
  }

  // Obtenir l'instance de la carte
  getMap() {
    return this.map;
  }

  // Obtenir les informations des sentiers
  getTrailsInfo() {
    return this.trailsConfig.map(trail => ({
      name: trail.name,
      popup: trail.popup,
      color: trail.color
    }));
  }

  // Centrer la carte pour voir toutes les calanques
  fitAllCalanques() {
    if (this.map) {
      const allPoints = Object.values(this.points);
      const bounds = L.latLngBounds(allPoints);
      this.map.fitBounds(bounds, { padding: [20, 20] });
    }
  }
}

// Exporter le service
export default MapService;
