const API_BASE = "http://localhost/Projets/calanque_web_site/backend/public/";

export async function getUserSubscription(userId) {
  try {
    const res = await fetch(`${API_BASE}api/users/${userId}/subscription/active`, {
      method: "GET",
      credentials: "include",
      headers: { "Content-Type": "application/json" }
    });
    return await res.json();
  } catch (error) {
    console.error("Erreur lors de la récupération de l'abonnement:", error);
    return { success: false, message: "Erreur de connexion" };
  }
}

export async function createSubscription(subscriptionData) {
  try {
    const res = await fetch(`${API_BASE}api/subscriptions`, {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(subscriptionData)
    });
    return await res.json();
  } catch (error) {
    console.error("Erreur lors de la création de l'abonnement:", error);
    return { success: false, message: "Erreur de connexion" };
  }
}

export async function updateSubscription(id, subscriptionData) {
  try {
    const res = await fetch(`${API_BASE}api/subscriptions/${id}`, {
      method: "PUT",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(subscriptionData)
    });
    return await res.json();
  } catch (error) {
    console.error("Erreur lors de la mise à jour de l'abonnement:", error);
    return { success: false, message: "Erreur de connexion" };
  }
}

export async function cancelSubscription(id) {
  try {
    const res = await fetch(`${API_BASE}api/subscriptions/${id}/cancel`, {
      method: "POST",
      credentials: "include"
    });
    return await res.json();
  } catch (error) {
    console.error("Erreur lors de l'annulation de l'abonnement:", error);
    return { success: false, message: "Erreur de connexion" };
  }
}
