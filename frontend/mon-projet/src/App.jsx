import { BrowserRouter as Router, Routes, Route, Link, Outlet, useParams } from "react-router-dom";

// --- Composants principaux ---
function Accueil() {
  return <h2>Bienvenue sur l'accueil</h2>;
}

function TableauDeBord() {
  return (
    <div>
      <h2>Tableau de bord</h2>
      <nav>
        <Link to="profil">Profil</Link> |{" "}
        <Link to="parametres">Paramètres</Link> |{" "}
        <Link to="produits">Produits</Link>
      </nav>

      {/* Outlet = sous-routes */}
      <Outlet />
    </div>
  );
}

// --- Sous-pages du dashboard ---
function Profil() {
  return <h3>Page Profil</h3>;
}

function Parametres() {
  return <h3>Page Paramètres</h3>;
}

function ListeProduits() {
  return (
    <div>
      <h3>Liste des produits</h3>
      <ul>
        <li><Link to="1">Produit 1</Link></li>
        <li><Link to="2">Produit 2</Link></li>
        <li><Link to="3">Produit 3</Link></li>
      </ul>
      <Outlet /> {/* sous-route pour un produit individuel */}
    </div>
  );
}

function Produit() {
  const { id } = useParams();
  return <h4>Détails du produit n°{id}</h4>;
}

// --- App principale ---
export default function App() {
  return (
    <Router>
      <nav>
        <Link to="/">Accueil</Link> |{" "}
        <Link to="/dashboard">Dashboard</Link>
      </nav>

      <Routes>
        <Route path="/" element={<Accueil />} />
        
        {/* Route imbriquée */}
        <Route path="/dashboard" element={<TableauDeBord />}>
          <Route path="profil" element={<Profil />} />
          <Route path="parametres" element={<Parametres />} />

          {/* Produits avec paramètre dynamique */}
          <Route path="produits" element={<ListeProduits />}>
            <Route path=":id" element={<Produit />} />
          </Route>
        </Route>
      </Routes>
    </Router>
  );
}
