import { BrowserRouter as Router, Routes, Route } from "react-router-dom";

import Home from "./pages/Home";
import Login from "./pages/Login";
import Dashboard from "./pages/dashboard";
import Register from "./pages/Register";
import CampingReservation from "./pages/CampingReservation";
import Diving from "./pages/Diving";
import Sentiers from "./pages/Sentiers";

export default function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/reservation/camping" element={<CampingReservation />} />
        <Route path="/diving" element={<Diving />} />
        <Route path="/sentiers" element={<Sentiers />} />
        <Route path="*" element={<Home />} /> {/* redirection par défaut vers Home */}
      </Routes>
    </Router>
  );
}
