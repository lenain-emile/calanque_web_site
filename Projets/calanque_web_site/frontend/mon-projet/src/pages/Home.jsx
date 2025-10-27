import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { getUser } from "../service/userService";
import marseilleCalanques from "../assets/marseille_calanques.jpg";
import camping from "../assets/camping.jpg";
import mobilome from "../assets/mobilome.jpg";
import plongeur from "../assets/plongeur.jpg";
import plongee from "../assets/plonge.jpg";
import randonnee from "../assets/randonne.jpg";
import sentier from "../assets/sentier.jpeg";
import logoCalanque from "../assets/logo calanque.jpg";
import Button from "../components/Button";
import Card from "../components/Card";
import Navbar from "../components/Navbar";
import "bootstrap/dist/css/bootstrap.min.css";
import "../styles/Home.css";

export default function Home() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  // Données du carrousel
  const carouselData = [
    {
      id: 1,
      title: "Camping dans les Calanques",
      description: "Vivez une expérience unique au cœur de la nature préservée des calanques de Marseille",
      image: camping,
      emoji: "🏕️"
    },
    {
      id: 2,
      title: "Plongée sous-marine",
      description: "Découvrez les fonds marins exceptionnels et la biodiversité des calanques",
      image: plongee,
      emoji: "🤿"
    },
    {
      id: 3,
      title: "Randonnée panoramique",
      description: "Explorez les sentiers côtiers et profitez de vues spectaculaires sur la Méditerranée",
      image: randonnee,
      emoji: "🥾"
    }
  ];

  useEffect(() => {
    const checkUser = async () => {
      try {
        const userResponse = await getUser();
        if (userResponse.success) {
          setUser(userResponse.data);
        }
      } catch (error) {
        console.error("Erreur lors de la vérification de l'utilisateur:", error);
      } finally {
        setLoading(false);
      }
    };

    checkUser();
  }, []);

  const handleGetStarted = () => {
    if (user) {
      navigate("/reservation/camping");
    } else {
      navigate("/register");
    }
  };

  const handleDivingClick = () => {
    navigate("/diving");
  };

  if (loading) {
    return (
      <div className="home-container" style={{ backgroundImage: `url(${marseilleCalanques})` }}>
        <div className="home-overlay" />
        <div className="home-loading">
          <div className="home-loading-spinner"></div>
          <p>Chargement...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="home-container" style={{ backgroundImage: `url(${marseilleCalanques})` }}>
      <div className="home-overlay" />
      
      {/* Navigation */}
      <Navbar />

      {/* Carrousel Section */}
      <div className="home-carousel-container">
        <div 
          id="homeCarousel" 
          className="carousel slide" 
          data-bs-ride="carousel"
          data-bs-interval="5000"
        >
          <div className="carousel-indicators">
            {carouselData.map((_, index) => (
              <button
                key={index}
                type="button"
                data-bs-target="#homeCarousel"
                data-bs-slide-to={index}
                className={index === 0 ? "active" : ""}
                aria-current={index === 0 ? "true" : "false"}
                aria-label={`Slide ${index + 1}`}
              ></button>
            ))}
          </div>
          
          <div className="carousel-inner">
            {carouselData.map((slide, index) => (
              <div 
                key={slide.id}
                className={`carousel-item ${index === 0 ? "active" : ""}`}
              >
                <div 
                  className="carousel-slide"
                  style={{ backgroundImage: `url(${slide.image})` }}
                >
                  <div className="carousel-overlay"></div>
                  <div className="carousel-content-left">
                    <h2 className="carousel-title">{slide.title}</h2>
                    <p className="carousel-description">{slide.description}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
          
          <button 
            className="carousel-control-prev" 
            type="button" 
            data-bs-target="#homeCarousel" 
            data-bs-slide="prev"
          >
            <span className="carousel-control-prev-icon" aria-hidden="true"></span>
            <span className="visually-hidden">Previous</span>
          </button>
          <button 
            className="carousel-control-next" 
            type="button" 
            data-bs-target="#homeCarousel" 
            data-bs-slide="next"
          >
            <span className="carousel-control-next-icon" aria-hidden="true"></span>
            <span className="visually-hidden">Next</span>
          </button>
        </div>
      </div>

      {/* Section Réservation */}
      <div className="container mt-5 mb-5">
        <div className="row align-items-center">
          <div className="col-md-6">
            <div className="reservation-image-container">
              <img 
                src={mobilome} 
                alt="Camping dans les Calanques" 
                className="reservation-image"
              />
            </div>
          </div>
          <div className="col-md-6">
            <div className="reservation-content">
              <h2 className="reservation-title">Réservez votre place</h2>
              <p className="reservation-description">
                Découvrez nos différentes options de réservation pour vivre une expérience inoubliable dans les calanques de Marseille. 
                Que ce soit pour un camping, une activité nautique ou une randonnée, nous avons ce qu'il vous faut.
              </p>
              
              <button 
                className="btn btn-primary btn-lg reservation-button"
                onClick={handleGetStarted}
              >
                Réserver maintenant
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Section Plongée */}
      <div className="container mt-8">
        <div className="row align-items-center">
          <div className="col-md-5">
            <div className="plongee-content">
              <h2 className="plongee-title">Plongée sous-marine</h2>
              <p className="plongee-description">
                Explorez les fonds marins exceptionnels des calanques de Marseille. 
                Découvrez une biodiversité unique et des paysages sous-marins à couper le souffle. 
                Nos guides expérimentés vous accompagneront dans cette aventure inoubliable.
              </p>
              <button 
                className="btn btn-primary btn-lg plongee-button"
                onClick={handleDivingClick}
              >
                Découvrir la plongée
              </button>
            </div>
          </div>
          <div className="col-md-7">
            <div className="plongee-image-container">
              <img 
                src={plongeur} 
                alt="Plongée dans les Calanques" 
                className="plongee-image"
              />
            </div>
          </div>
        </div>
      </div>

      {/* Section Sentiers */}
      <div className="container mt-8">
        <div className="row align-items-center">
          <div className="col-md-7">
            <div className="sentiers-image-container">
              <img 
                src={sentier} 
                alt="Sentiers des Calanques" 
                className="sentiers-image"
              />
            </div>
          </div>
          <div className="col-md-5">
            <div className="sentiers-content">
              <h2 className="sentiers-title">Sentiers des calanques</h2>
              <p className="sentiers-description">
                Parcourez les magnifiques sentiers côtiers des calanques de Marseille. 
                Découvrez des paysages époustouflants, des criques secrètes et une nature préservée. 
                Nos randonnées guidées vous feront découvrir les plus beaux points de vue de la région.
              </p>
              <button 
                className="btn btn-primary btn-lg sentiers-button"
                onClick={handleGetStarted}
              >
                Explorer les sentiers
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <footer className="home-footer mt-5">
        <div className="container">
          <div className="row">
            <div className="col-md-6">
              <div className="d-flex align-items-center">
                <img 
                  src={logoCalanque} 
                  alt="Logo Calanque" 
                  className="home-footer-logo me-3"
                />
                <div>
                  <h5 className="text-white mb-0">Calanques Paradise</h5>
                  <small className="text-light">Votre guide des calanques de Marseille</small>
                </div>
              </div>
            </div>
            <div className="col-md-6 text-md-end">
              <p className="text-light mb-0">
                © 2024 Calanques Paradise. Tous droits réservés.
              </p>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}
