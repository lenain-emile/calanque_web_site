import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { getUser, logoutUser } from "../service/userService";

export default function Dashboard() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    getUser().then(response => {
      if (response.success) {
        setUser(response.data);
      } else {
        navigate("/login"); 
      }
      setLoading(false);
    });
  }, []);

  const handleLogout = async () => {
    await logoutUser();
    navigate("/login");
  };
  

  if (loading) return <p>Chargement...</p>;
  if (!user) return null;

  return (
    <div className="container mt-5">
      <h1>Bienvenue {user.firstName} {user.lastName} 👋</h1>
      <p>Email : {user.email}</p>
      <button className="btn btn-danger mt-3" onClick={handleLogout}>
        Déconnexion
      </button>
    </div>
  );
}
