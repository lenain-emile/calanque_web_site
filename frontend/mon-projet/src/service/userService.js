
const API_BASE = "http://localhost/Projets/calanque_web_site/backend/public/";


export async function loginUser(email, password) {
  const res = await fetch(`${API_BASE}api/users/login`, {
    method: "POST",
    credentials: "include", // envoie le cookie de session PHP
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password })
  });
  const result = await res.json();
  
  // Stocker les informations de l'utilisateur dans le localStorage si la connexion réussit
  if (result.success && result.data) {
    localStorage.setItem('user', JSON.stringify(result.data));
  }
  
  return result; // { success, message, data }
}


export async function logoutUser() {
  const res = await fetch(`${API_BASE}api/users/logout`, {
    method: "POST",
    credentials: "include"
  });
  
  // Supprimer les informations de l'utilisateur du localStorage
  localStorage.removeItem('user');
  
  return res.json();
}



export async function registerUser(firstName, lastName, email, password) {
  const res = await fetch(`${API_BASE}api/users`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ firstName, lastName, email, password })
  });
  return res.json();
}

export async function getUser() {
  // Récupérer les informations de l'utilisateur depuis le localStorage
  const userData = localStorage.getItem('user');
  
  if (userData) {
    try {
      const user = JSON.parse(userData);
      return { success: true, data: user };
    } catch (error) {
      localStorage.removeItem('user');
      return { success: false, message: 'Données utilisateur corrompues' };
    }
  }
  
  return { success: false, message: 'Utilisateur non connecté' };
}
