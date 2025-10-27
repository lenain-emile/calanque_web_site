import { useState, useEffect } from "react";
import { MapContainer, TileLayer, GeoJSON } from 'react-leaflet';
import { useNavigate } from "react-router-dom";
import Navbar from "../components/Navbar";
import Button from "../components/Button";
import { getAllTrails } from "../service/trailService";
import { getAllResources } from "../service/naturalResourceService";
import marseilleCalanques from "../assets/marseille_calanques.jpg";
import 'leaflet/dist/leaflet.css';
import "../styles/Sentiers.css";

export default function Sentiers() {
  const navigate = useNavigate();
  const [trails, setTrails] = useState([]);
  const [resources, setResources] = useState([]);
  const [selectedTrail, setSelectedTrail] = useState(null);
  const [selectedType, setSelectedType] = useState('ALL');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      
      // Récupérer les sentiers
      const trailsResponse = await getAllTrails();
      if (trailsResponse.success) {
        setTrails(trailsResponse.data || []);
      } else {
        setError(trailsResponse.message || 'Erreur lors du chargement des sentiers');
      }

      // Récupérer les ressources naturelles
      const resourcesResponse = await getAllResources();
      if (resourcesResponse.success) {
        setResources(resourcesResponse.data || []);
      }
      
    } catch (err) {
      console.error('Erreur:', err);
      setError('Impossible de charger les données');
    } finally {
      setLoading(false);
    }
  };

  const handleTrailClick = (trail) => {
    setSelectedTrail(trail);
    // Scroll vers la carte
    setTimeout(() => {
      document.getElementById('map-section')?.scrollIntoView({ behavior: 'smooth' });
    }, 100);
  };

  const getDifficultyBadge = (difficulty) => {
    const difficultyMap = {
      1: { label: 'FACILE', color: 'success' },
      2: { label: 'MOYEN', color: 'warning' },
      3: { label: 'DIFFICILE', color: 'danger' }
    };
    return difficultyMap[difficulty] || { label: 'INCONNU', color: 'secondary' };
  };

  const filterResourcesByType = () => {
    if (selectedType === 'ALL') return resources;
    return resources.filter(r => r.type === selectedType);
  };

  const getTypeIcon = (type) => {
    const icons = {
      'Faune': '🦎',
      'Flore': '🌿',
      'Géologie': '🪨',
      'Flore aquatique': '🌊',
      'Écosystème': '🌍'
    };
    return icons[type] || '📍';
  };

  if (loading) {
    return (
      <div 
        className="sentiers-container"
        style={{ backgroundImage: `url(${marseilleCalanques})` }}
      >
        <div className="sentiers-overlay" />
        <Navbar />
        <div className="sentiers-loading">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Chargement...</span>
          </div>
          <p className="mt-3">Chargement des sentiers...</p>
        </div>
      </div>
    );
  }

  return (
    <div 
      className="sentiers-container"
      style={{ backgroundImage: `url(${marseilleCalanques})` }}
    >
      <div className="sentiers-overlay" />
      
      <Navbar />

      <div className="sentiers-content container">
        {/* En-tête */}
        <div className="sentiers-header text-center mb-5">
          <h1 className="sentiers-title">🥾 SENTIERS DES CALANQUES</h1>
          <p className="sentiers-subtitle">
            Découvrez les sentiers et la biodiversité du Parc National des Calanques
          </p>
        </div>

        {error && (
          <div className="alert alert-danger text-center mb-4" role="alert">
            {error}
          </div>
        )}

        {/* Section Sentiers */}
        <div className="mb-5">
          <h2 className="section-title mb-4">Nos Sentiers</h2>
          
          {trails.length === 0 ? (
            <div className="alert alert-info text-center">
              Aucun sentier disponible pour le moment.
            </div>
          ) : (
            <div className="row g-4">
              {trails.map((trail) => {
                const difficulty = getDifficultyBadge(trail.difficulty);
                return (
                  <div key={trail.id} className="col-lg-4 col-md-6">
                    <div 
                      className={`trail-card ${selectedTrail?.id === trail.id ? 'trail-card-selected' : ''}`}
                      onClick={() => handleTrailClick(trail)}
                      role="button"
                      tabIndex={0}
                    >
                      <h3 className="trail-name">{trail.name}</h3>
                      <p className="trail-description">{trail.description}</p>
                      
                      <div className="trail-details">
                        <div className="detail-item">
                          <strong>📏 Distance:</strong> {trail.length_km} km
                        </div>
                        <div className="detail-item">
                          <strong>📊 Difficulté:</strong>{' '}
                          <span className={`badge bg-${difficulty.color}`}>
                            {difficulty.label}
                          </span>
                        </div>
                      </div>
                      
                      <Button variant="primary" size="small" className="w-100 mt-3">
                        Voir sur la carte 🗺️
                      </Button>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>

        {/* Carte Leaflet */}
        {selectedTrail && selectedTrail.path_locations && (
          <div id="map-section" className="mb-5">
            <div className="map-card">
              <div className="d-flex justify-content-between align-items-center mb-3">
                <h2 className="section-title mb-0">📍 {selectedTrail.name}</h2>
                <Button 
                  variant="secondary"
                  size="small"
                  onClick={() => setSelectedTrail(null)}
                >
                  Fermer la carte ✖️
                </Button>
              </div>
              
              <div className="map-container">
                <MapContainer 
                  center={[43.21, 5.44]} 
                  zoom={13} 
                  style={{ height: '500px', width: '100%', borderRadius: '15px' }}
                  className="sentiers-map"
                >
                  <TileLayer
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                  />
                  {selectedTrail.path_locations && (
                    <GeoJSON 
                      data={JSON.parse(selectedTrail.path_locations)}
                      style={() => ({
                        color: '#00AEEF',
                        weight: 4,
                        opacity: 0.8
                      })}
                    />
                  )}
                </MapContainer>
              </div>
            </div>
          </div>
        )}

        {/* Section Faune & Flore */}
        <div className="mb-5">
          <h2 className="section-title mb-4">🌿 Faune & Flore</h2>
          
          {/* Filtres */}
          <div className="mb-4 text-center">
            <div className="btn-group" role="group">
              <button 
                className={`btn ${selectedType === 'ALL' ? 'btn-primary' : 'btn-outline-primary'}`}
                onClick={() => setSelectedType('ALL')}
              >
                Tout
              </button>
              <button 
                className={`btn ${selectedType === 'Faune' ? 'btn-primary' : 'btn-outline-primary'}`}
                onClick={() => setSelectedType('Faune')}
              >
                🦎 Faune
              </button>
              <button 
                className={`btn ${selectedType === 'Flore' ? 'btn-primary' : 'btn-outline-primary'}`}
                onClick={() => setSelectedType('Flore')}
              >
                🌿 Flore
              </button>
              <button 
                className={`btn ${selectedType === 'Géologie' ? 'btn-primary' : 'btn-outline-primary'}`}
                onClick={() => setSelectedType('Géologie')}
              >
                🪨 Géologie
              </button>
            </div>
          </div>

          {/* Liste des ressources */}
          {filterResourcesByType().length === 0 ? (
            <div className="alert alert-info text-center">
              Aucune ressource trouvée pour ce type.
            </div>
          ) : (
            <div className="row g-4">
              {filterResourcesByType().map((resource) => (
                <div key={resource.id} className="col-lg-4 col-md-6">
                  <div className="resource-card">
                    <div className="d-flex justify-content-between align-items-start mb-2">
                      <h3 className="resource-name">
                        {getTypeIcon(resource.type)} {resource.name}
                      </h3>
                      <span className="badge bg-info">
                        {resource.type}
                      </span>
                    </div>
                    
                    <p className="resource-description">{resource.description}</p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Boutons d'action */}
        <div className="sentiers-actions text-center mt-5">
          <Button
            variant="secondary"
            size="large"
            onClick={() => navigate('/')}
          >
            🏠 Retour Accueil
          </Button>
        </div>
      </div>
    </div>
  );
}
