import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { registerUser } from "../service/userService";

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
    <div className="container mt-5" style={{ maxWidth: "400px" }}>
      <h2 className="mb-4 text-center">Inscription</h2>
      <form onSubmit={handleSubmit} className="card p-4 shadow-sm">
        <div className="mb-3">
          <label className="form-label">Prénom :</label>
          <input type="text" className="form-control" value={firstName} onChange={e => setFirstName(e.target.value)} required />
        </div>
        <div className="mb-3">
          <label className="form-label">Nom :</label>
          <input type="text" className="form-control" value={lastName} onChange={e => setLastName(e.target.value)} required />
        </div>
        <div className="mb-3">
          <label className="form-label">Email :</label>
          <input type="email" className="form-control" value={email} onChange={e => setEmail(e.target.value)} required />
        </div>
        <div className="mb-3">
          <label className="form-label">Mot de passe :</label>
          <input type="password" className="form-control" value={password} onChange={e => setPassword(e.target.value)} required />
        </div>
        {error && <div className="alert alert-danger">{error}</div>}
        {success && <div className="alert alert-success">{success}</div>}
        <button type="submit" className="btn btn-primary w-100">S'inscrire</button>
      </form>
    </div>
  );
}
