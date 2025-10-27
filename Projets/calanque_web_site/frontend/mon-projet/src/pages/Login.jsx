import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { loginUser } from "../service/userService";
import marseilleCalanques from "../assets/marseille_calanques.jpg";
import logoCalanque from "../assets/logo calanque.jpg";
import Button from "../components/Button";
import Input from "../components/Input";
import Card from "../components/Card";
import Navbar from "../components/Navbar";
import "../styles/auth.css";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const response = await loginUser(email, password);

      if (!response.success) {
        setError(response.message);
        return;
      }

      navigate("/dashboard");
    } catch (err) {
      setError("Une erreur est survenue lors de la connexion");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div 
      className="login-container"
      style={{ backgroundImage: `url(${marseilleCalanques})` }}
    >
      {/* Overlay sombre pour améliorer la lisibilité */}
      <div className="login-overlay" />
      
      {/* Navigation */}
      <Navbar />

      <Card variant="default" padding="large" className="login-card">
        {/* En-tête avec logo et titre */}
        <div className="login-header">
          {/* Logo Calanque */}
          <div className="login-logo">
            <img 
              src={logoCalanque} 
              alt="Logo Calanque" 
            />
          </div>
          <h2 className="login-title">
            Calanques Paradise
          </h2>
          <p className="login-subtitle">
            Connectez-vous à votre compte
          </p>
        </div>

        <form onSubmit={handleSubmit} className="login-form">
          <Input
            label="Email"
            type="email"
            value={email}
            onChange={e => setEmail(e.target.value)}
            required
            placeholder="votre@email.com"
          />

          <Input
            label="Mot de passe"
            type="password"
            value={password}
            onChange={e => setPassword(e.target.value)}
            required
            placeholder="Votre mot de passe"
          />

          {error && (
            <div className="login-message error">
              {error}
            </div>
          )}

          <Button 
            type="submit" 
            variant="primary" 
            size="full"
            loading={loading}
            disabled={loading}
          >
            Se connecter
          </Button>
        </form>

        {/* Lien vers l'inscription */}
        <div className="login-footer">
          <p>
            Pas encore de compte ?{" "}
            <Button
              variant="outline"
              size="small"
              onClick={() => navigate("/register")}
              className="login-link"
            >
              S'inscrire
            </Button>
          </p>
        </div>
      </Card>
    </div>
  );
}

