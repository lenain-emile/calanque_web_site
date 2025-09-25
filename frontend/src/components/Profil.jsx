// components/Profil.jsx
import { Link } from "react-router-dom";

export default function Profil() {
  return (
    <div>
      <header>
        <Link to="/">
        <img 
          src="./src/assets/logo.png"   // Mets ton chemin d'image
          alt="Aller vers le profil"
        />
      </Link>
        <h1>Au coeur des calanques</h1>  
      </header>
      


      
    </div>
  );
}
