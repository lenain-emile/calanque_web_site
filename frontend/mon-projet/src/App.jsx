import { BrowserRouter as Router, Routes, Route } from "react-router-dom";

import Login from "./pages/Login";
import Dashboard from "./pages/dashboard";
import Register from "./pages/Register";
import MapPage from "./pages/MapPage";

export default function App() {
  return (
    <Router>
      <Routes>
  <Route path="/login" element={<Login />} />
  <Route path="/register" element={<Register />} />
  <Route path="/dashboard" element={<Dashboard />} />
  <Route path="/map" element={<MapPage />} />
  <Route path="/" element={<Login />} />
  <Route path="*" element={<Login />} /> {/* redirection par défaut */}
      </Routes>
    </Router>
  );
}
