import { useNavigate } from "react-router-dom";
import Navbar from "../components/Navbar";
import Button from "../components/Button";
import fondplonge from "../assets/fondplonge.jpg";
import "../styles/Diving.css";

export default function Diving() {
  const navigate = useNavigate();

  return (
    <div 
      className="diving-container"
      style={{ backgroundImage: `url(${fondplonge})` }}
    >
      <div className="diving-overlay" />
      
      <Navbar />

      <div className="diving-content container">
        <div className="diving-header">
          <h1 className="diving-title">L'ATELIER DE LA MER</h1>
          <p className="diving-subtitle">
            Plongez à Marseille au cœur du parc national des calanques
          </p>
        </div>

        <div className="diving-info-cards">
          {/* Carte Nous Trouver */}
          <div className="diving-card">
            <h2>Nous Trouver</h2>
            <div className="diving-info">
              <p>
                <strong>Adresse :</strong><br />
                Port de la Pointe Rouge - Entrée N°2<br />
                13008 Marseille
              </p>
              <p>
                <strong>📞 Téléphone :</strong><br />
                <a href="tel:+33491725412">+33 4 91 72 54 12</a>
              </p>
              <p>
                <strong>✉️ Email :</strong><br />
                <a href="mailto:info@atelierdelamer.com">info@atelierdelamer.com</a>
              </p>
              <p>
                <strong>🕐 Horaires :</strong><br />
                Ouvert 7j/7 toute l'année<br />
                De 8h à 19h
              </p>
            </div>
          </div>

          {/* Carte À Propos */}
          <div className="diving-card">
            <h2>À Propos</h2>
            <p className="diving-description">
              L'Atelier de la Mer est votre centre de plongée sous-marine à Marseille, 
              idéalement situé au Port de la Pointe Rouge. Nous vous proposons des plongées 
              exceptionnelles au cœur du Parc National des Calanques, l'un des plus beaux 
              sites de plongée de la Méditerranée.
            </p>
            <p className="diving-description">
              Que vous soyez débutant ou plongeur confirmé, notre équipe de professionnels 
              certifiés vous accompagne pour découvrir les merveilles sous-marines de Marseille. 
              Formations, baptêmes de plongée, explorations... Vivez une expérience inoubliable 
              dans les eaux cristallines des calanques !
            </p>
          </div>
        </div>

        <div className="diving-actions">
          
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
