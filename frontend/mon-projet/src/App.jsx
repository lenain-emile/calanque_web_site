import { BrowserRouter as Router, Routes, Route } from "react-router-dom";

import Login from "./pages/Login";
import Dashboard from "./pages/dashboard";
import Register from "./pages/Register";

export default function App() {
  return (
    <Router>
      <Routes>
  <Route path="/login" element={<Login />} />
  <Route path="/register" element={<Register />} />
  <Route path="/dashboard" element={<Dashboard />} />
  <Route path="*" element={<Login />} /> {/* redirection par défaut */}
      </Routes>
    </Router>
  );
}
