// App.jsx
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Home from "./components/Home";
import Profil from "./components/Profil";
import "./App.css";
import "./components/Home.css";
import "./components/Profil.css";
import "tailwindcss";

export default function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/profil" element={<Profil />} />
      </Routes>
    </Router>
  );
}
