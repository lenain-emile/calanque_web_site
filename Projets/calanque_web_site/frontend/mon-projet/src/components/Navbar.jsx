import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { getUser, logoutUser } from "../service/userService";
import logoCalanque from "../assets/logo calanque.jpg";
import "../styles/Navbar.css";

export default function Navbar() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

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

  const handleLogout = async () => {
    try {
      await logoutUser();
      setUser(null);
      navigate("/");
    } catch (error) {
      console.error("Erreur lors de la déconnexion:", error);
    }
  };

  const handleTitleClick = () => {
    navigate("/");
    window.location.reload();
  };

  if (loading) {
    return null; // Ne pas afficher la navbar pendant le chargement
  }

  return (
    <nav className="navbar navbar-expand-lg navbar-dark navbar-fixed">
      <div className="container">
        <div 
          className="navbar-brand d-flex align-items-center navbar-brand-clickable" 
          onClick={handleTitleClick}
        >
          <img 
            src={logoCalanque} 
            alt="Logo Calanque" 
            className="navbar-logo"
          />
          <span className="ms-2">Calanques Paradise</span>
        </div>
        
        {/* Boutons de navigation centrale */}
        <div className="navbar-center d-flex gap-4">
          <button 
            className="btn btn-outline-light navbar-center-btn" 
            onClick={() => navigate("/diving")}
          >
             Plongée
          </button>
          <button 
            className="btn btn-outline-light navbar-center-btn" 
            onClick={() => navigate("/sentiers")}
          >
             Sentiers
          </button>
          <button 
            className="btn btn-outline-light navbar-center-btn" 
            onClick={() => navigate("/reservation/camping")}
          >
             Réservation
          </button>
        </div>
        
        <div className="navbar-nav ms-auto">
          {user ? (
            <div className="nav-item dropdown">
              <button 
                className="btn btn-outline-light dropdown-toggle" 
                type="button" 
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                connected {user.firstName}
              </button>
              <ul className="dropdown-menu dropdown-menu-end">
                <li>
                  <button 
                    className="dropdown-item" 
                    onClick={() => navigate("/dashboard")}
                  >
                    📊 Dashboard
                  </button>
                </li>
                <li><hr className="dropdown-divider" /></li>
                <li>
                  <button 
                    className="dropdown-item text-danger" 
                    onClick={handleLogout}
                  >
                    🚪 Déconnexion
                  </button>
                </li>
              </ul>
            </div>
          ) : (
            <>
              <button 
                className="btn btn-outline-light me-2" 
                onClick={() => navigate("/login")}
              >
                Se connecter
              </button>
              <button 
                className="btn btn-primary" 
                onClick={() => navigate("/register")}
              >
                S'inscrire
              </button>
            </>
          )}
        </div>
      </div>
    </nav>
  );
}
