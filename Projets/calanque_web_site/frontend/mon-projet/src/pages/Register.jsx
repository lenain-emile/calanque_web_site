import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { registerUser } from "../service/userService";
import marseilleCalanques from "../assets/marseille_calanques.jpg";
import logoCalanque from "../assets/logo calanque.jpg";
import Button from "../components/Button";
import Input from "../components/Input";
import Card from "../components/Card";
import Navbar from "../components/Navbar";
import "../styles/auth.css";

export default function Register() {
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setSuccess("");
    const response = await registerUser(firstName, lastName, email, password);
    if (!response.success) {
      setError(response.message || "Erreur lors de l'inscription");
      return;
    }
    setSuccess("Inscription réussie ! Vous pouvez vous connecter.");
    setTimeout(() => navigate("/login"), 1500);
  };

  return (
    <div 
      className="register-container"
      style={{ backgroundImage: `url(${marseilleCalanques})` }}
    >
      {/* Overlay sombre pour améliorer la lisibilité */}
      <div className="register-overlay" />
      
      {/* Navigation */}
      <Navbar />

      <Card variant="default" padding="large" className="register-card">
        {/* En-tête avec logo et titre */}
        <div className="register-header">
          {/* Logo Calanque */}
          <div className="register-logo">
            <img 
              src={logoCalanque} 
              alt="Logo Calanque" 
            />
          </div>
          <h2 className="register-title">
            Calanques Paradise
          </h2>
          <p className="register-subtitle">
            Rejoignez l'aventure des calanques
          </p>
        </div>

        <form onSubmit={handleSubmit} className="register-form">
          <Input
            label="Prénom"
            type="text"
            value={firstName}
            onChange={e => setFirstName(e.target.value)}
            required
          />

          <Input
            label="Nom"
            type="text"
            value={lastName}
            onChange={e => setLastName(e.target.value)}
            required
          />

          <Input
            label="Email"
            type="email"
            value={email}
            onChange={e => setEmail(e.target.value)}
            required
          />

          <Input
            label="Mot de passe"
            type="password"
            value={password}
            onChange={e => setPassword(e.target.value)}
            required
          />

          {error && (
            <div className="register-message error">
              {error}
            </div>
          )}

          {success && (
            <div className="register-message success">
              {success}
            </div>
          )}

          <Button 
            type="submit" 
            variant="primary" 
            size="full"
          >
            Créer mon compte
          </Button>
        </form>

        {/* Lien vers la connexion */}
        <div className="register-footer">
          <p>
            Déjà un compte ?{" "}
            <Button
              variant="outline"
              size="small"
              onClick={() => navigate("/login")}
              className="register-link"
            >
              Se connecter
            </Button>
          </p>
        </div>
      </Card>
    </div>
  );
}
