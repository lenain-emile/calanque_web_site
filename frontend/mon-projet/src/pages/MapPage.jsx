import React, { useEffect, useRef, useState } from 'react';
import MapService from '../service/mapService.js';
import { getTrailData, getTrailFauna, getTrailFlora } from '../service/callanquesData.js';
import '../assets/map.css';

// Composant pour afficher les détails naturalistes d'un sentier
const TrailNatureDetails = ({ trailId }) => {
  const trailData = getTrailData(trailId);
  const trailFauna = getTrailFauna(trailId);
  const trailFlora = getTrailFlora(trailId);

  if (!trailData) return null;

  return (
    <div className="trail-nature-details">
      {/* Informations générales du sentier */}
      <div className="trail-overview">
        <div className="trail-stats">
          <span className="stat-badge">📏 {trailData.distance}</span>
          <span className="stat-badge">⏱️ {trailData.duree}</span>
          <span className="stat-badge">🏔️ {trailData.difficulte}</span>
        </div>
        <p className="ecosystems">
          <strong>Écosystèmes traversés :</strong> {trailData.ecosystemes.join(', ')}
        </p>
      </div>

      {/* Faune observable */}
      <div className="nature-section">
        <h4 className="section-title">🦅 Faune Observable</h4>
        <div className="nature-grid">
          {trailFauna && Object.entries(trailFauna).map(([category, animals]) => (
            <div key={category} className="nature-category">
              <h5 className="category-title">{category.charAt(0).toUpperCase() + category.slice(1)}</h5>
              <div className="species-list">
                {animals.map((animal, index) => (
                  <div key={index} className="species-card">
                    <div className="species-name">{animal.nom}</div>
                    {animal.nomScientifique && (
                      <div className="species-scientific">{animal.nomScientifique}</div>
                    )}
                    {animal.description && (
                      <div className="species-description">{animal.description}</div>
                    )}
                    {animal.observation && (
                      <div className="species-observation">👁️ {animal.observation}</div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Flore caractéristique */}
      <div className="nature-section">
        <h4 className="section-title">🌱 Flore Caractéristique</h4>
        <div className="nature-grid">
          {trailFlora && Object.entries(trailFlora).map(([habitat, plants]) => (
            <div key={habitat} className="nature-category">
              <h5 className="category-title">{habitat.charAt(0).toUpperCase() + habitat.slice(1)}</h5>
              <div className="species-list">
                {plants.map((plant, index) => (
                  <div key={index} className="species-card">
                    <div className="species-name">{plant.nom}</div>
                    {plant.nomScientifique && (
                      <div className="species-scientific">{plant.nomScientifique}</div>
                    )}
                    {plant.description && (
                      <div className="species-description">{plant.description}</div>
                    )}
                    {plant.particularite && (
                      <div className="species-feature">✨ {plant.particularite}</div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Points d'intérêt */}
      {trailData.pointsInteret && (
        <div className="nature-section">
          <h4 className="section-title">📍 Points d'Intérêt Naturaliste</h4>
          <div className="points-interest">
            {trailData.pointsInteret.map((point, index) => (
              <div key={index} className="interest-point">
                <h5 className="point-name">{point.nom}</h5>
                <p className="point-description">{point.description}</p>
                <div className="point-species">
                  <div className="point-fauna">
                    <strong>Faune :</strong> {point.faune.join(', ')}
                  </div>
                  <div className="point-flora">
                    <strong>Flore :</strong> {point.flore.join(', ')}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

// Composant pour afficher un aperçu de tous les sentiers
const AllTrailsNatureOverview = () => {
  return (
    <div className="all-trails-overview">
      <div className="overview-grid">
        <TrailNatureDetails trailId="trail1" />
        <TrailNatureDetails trailId="trail2" />
      </div>
    </div>
  );
};

const MapPage = () => {
  const mapRef = useRef(null);
  const mapServiceRef = useRef(null);
  const [selectedTrail, setSelectedTrail] = useState(null);
  const [showNatureInfo, setShowNatureInfo] = useState(false);

  useEffect(() => {
    // Vérifier si Leaflet est disponible
    if (typeof window !== 'undefined' && window.L) {
      // Créer une instance du service de carte
      mapServiceRef.current = new MapService();
      
      // Initialiser la carte complète avec marqueurs et sentiers
      mapServiceRef.current.initFullMap('map');
    } else {
      console.warn('Leaflet n\'est pas chargé. Assurez-vous d\'inclure la bibliothèque Leaflet.');
    }

    // Cleanup function
    return () => {
      if (mapServiceRef.current && mapServiceRef.current.getMap()) {
        mapServiceRef.current.getMap().remove();
      }
    };
  }, []);

  const handleClearMarkers = () => {
    if (mapServiceRef.current) {
      mapServiceRef.current.clearMarkers();
      // Remettre les marqueurs par défaut
      mapServiceRef.current.addAllCalanqueMarkers();
    }
  };

  const handleShowTrail = (trailName) => {
    if (mapServiceRef.current) {
      mapServiceRef.current.addTrailByName(trailName);
      setSelectedTrail(trailName);
      setShowNatureInfo(true);
    }
  };

  const handleShowAllTrails = () => {
    if (mapServiceRef.current) {
      mapServiceRef.current.clearTrails();
      mapServiceRef.current.addAllTrails();
      setSelectedTrail('all');
      setShowNatureInfo(true);
    }
  };

  const handleFitView = () => {
    if (mapServiceRef.current) {
      mapServiceRef.current.fitAllCalanques();
    }
  };

  return (
    <div className="map-page">
      <div className="map-container">
        <h1 className="map-title">Carte des Calanques de Marseille</h1>
        <p className="map-description">
          Découvrez les magnifiques calanques de Sugiton et Morgiou avec le sentier qui les relie.
        </p>
        
        {/* Container de la carte */}
        <div id="map" ref={mapRef}></div>
        
        {/* Contrôles pour les marqueurs */}
        <div className="map-controls">
          <button 
            onClick={handleClearMarkers}
            className="btn-base btn-reset"
          >
            Réinitialiser les marqueurs
          </button>
          <button 
            onClick={handleFitView}
            className="btn-base btn-fit-view"
          >
            Ajuster la vue
          </button>
        </div>

        {/* Contrôles pour les sentiers */}
        <div className="trail-controls">
          <button 
            onClick={() => handleShowTrail('trail1')}
            className="btn-base btn-trail btn-trail-blue"
          >
            Sugiton → Morgiou
          </button>
          <button 
            onClick={() => handleShowTrail('trail2')}
            className="btn-base btn-trail btn-trail-green"
          >
            Morgiou → Sormiou
          </button>
          <button 
            onClick={handleShowAllTrails}
            className="btn-base btn-trail btn-trail-purple"
          >
            Tous les sentiers
          </button>
        </div>
        
        {/* Informations sur les calanques */}
        <div className="calanque-info">
          <h3 className="info-title">Informations sur les Calanques et Sentiers</h3>
          
          {/* Informations sur les calanques */}
          <div className="calanques-grid">
            <div className="calanque-item">
              <h4>🏖️ Calanque de Sugiton</h4>
              <p>
                Point de départ du parcours. Vue magnifique sur la Méditerranée.
              </p>
            </div>
            <div className="calanque-item">
              <h4>🌊 Calanque de Morgiou</h4>
              <p>
                Calanque centrale avec port de pêche. Connexion vers Sugiton et Sormiou.
              </p>
            </div>
            <div className="calanque-item">
              <h4>🏞️ Calanque de Sormiou</h4>
              <p>
                Calanque familiale avec plage de sable. Accès plus facile en voiture.
              </p>
            </div>
          </div>

          {/* Informations sur les sentiers */}
          <div className="trails-section">
            <h4 className="trails-title">🥾 Sentiers de Randonnée</h4>
            <div className="trails-grid">
              <div className="trail-info trail-info-blue">
                <strong>Sugiton → Morgiou</strong>
                <small>Distance: 5 km • Durée: 1h30-2h • Difficulté: Modérée</small>
              </div>
              <div className="trail-info trail-info-green">
                <strong>Morgiou → Sormiou</strong>
                <small>Distance: 3 km • Durée: 1h-1h15 • Difficulté: Facile</small>
              </div>
            </div>
          </div>
        </div>

        {/* Section Informations Naturalistes */}
        {showNatureInfo && selectedTrail && (
          <div className="nature-info">
            <div className="nature-header">
              <h3 className="nature-title">
                🌿 Faune & Flore {selectedTrail === 'all' ? 'de tous les sentiers' : `du sentier ${getTrailData(selectedTrail)?.nom}`}
              </h3>
              <button 
                className="btn-base btn-close-nature"
                onClick={() => setShowNatureInfo(false)}
              >
                ✕
              </button>
            </div>
            
            {selectedTrail !== 'all' ? (
              <TrailNatureDetails trailId={selectedTrail} />
            ) : (
              <AllTrailsNatureOverview />
            )}
          </div>
        )}
      </div>
      
      {/* Script pour charger Leaflet si pas encore chargé */}
      <script 
        src="https://unpkg.com/leaflet/dist/leaflet.js"
        onLoad={() => {
          if (!mapServiceRef.current && window.L) {
            mapServiceRef.current = new MapService();
            mapServiceRef.current.initFullMap('map');
          }
        }}
      />
    </div>
  );
};

export default MapPage;