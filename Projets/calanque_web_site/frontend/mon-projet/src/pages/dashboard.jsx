import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { getUser, logoutUser, updateUser } from "../service/userService";
import { getUserReservations } from "../service/reservationService";
import { getUserSubscription } from "../service/subscriptionService";
import marseilleCalanques from "../assets/marseille_calanques.jpg";
import logoCalanque from "../assets/logo calanque.jpg";
import Button from "../components/Button";
import Input from "../components/Input";
import Card from "../components/Card";
import Navbar from "../components/Navbar";
import "../styles/Dashboard.css";

const getStatusText = (status) => {
  if (!status) return '';
  const statusMap = {
    'PENDING': 'En attente',
    'CONFIRMED': 'Confirmée',
    'CANCELLED': 'Annulée',
    'ACTIVE': 'En cours',
  };
  return statusMap[status.toUpperCase()] || status;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

export default function Dashboard() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [reservations, setReservations] = useState([]);
  const [subscription, setSubscription] = useState(null);
  const [showEditForm, setShowEditForm] = useState(false);
  const [editForm, setEditForm] = useState({
    email: '',
    password: '',
    confirmPassword: ''
  });
  const [editError, setEditError] = useState('');
  const [editSuccess, setEditSuccess] = useState('');
  const [editLoading, setEditLoading] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    // Lire le statut de paiement dans l'URL
    const params = new URLSearchParams(window.location.search);
    const status = params.get('status');
    const sessionId = params.get('session_id');

    const confirmPayment = async (sessionId) => {
      try {
        const response = await fetch('http://localhost/Projets/calanque_web_site/backend/public/api/reservation/checkout/confirm', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify({ session_id: sessionId })
        });

        if (!response.ok) {
          throw new Error('Erreur lors de la confirmation du paiement');
        }

        // Recharger les réservations pour mettre à jour l'affichage
        const userResponse = await getUser();
        if (userResponse.success) {
          const reservationsResponse = await getUserReservations(userResponse.data.id);
          if (reservationsResponse.success) {
            setReservations(reservationsResponse.data || []);
          }
        }
      } catch (error) {
        console.error("Erreur lors de la confirmation du paiement:", error);
      }
    };

    // Si on a un statut success et un session_id, on confirme le paiement
    if (status === 'success' && sessionId) {
      confirmPayment(sessionId);
    } else if (status === 'cancel') {
      console.warn('Paiement annulé');
    }

    const fetchUserData = async () => {
      try {
        // Récupérer les données utilisateur
        const userResponse = await getUser();
        if (userResponse.success) {
          setUser(userResponse.data);
          
          // Récupérer les vraies réservations depuis la base de données
          const reservationsResponse = await getUserReservations(userResponse.data.id);
          if (reservationsResponse.success) {
            setReservations(reservationsResponse.data || []);
          } else {
            console.warn("Impossible de récupérer les réservations:", reservationsResponse.message);
            setReservations([]);
          }
          
          // Récupérer le vrai abonnement depuis la base de données
          const subscriptionResponse = await getUserSubscription(userResponse.data.id);
          if (subscriptionResponse.success) {
            setSubscription(subscriptionResponse.data);
          } else {
            console.warn("Impossible de récupérer l'abonnement:", subscriptionResponse.message);
            setSubscription(null);
          }
      } else {
        navigate("/login"); 
      }
      } catch (error) {
        console.error("Erreur lors du chargement des données:", error);
        navigate("/login");
      } finally {
      setLoading(false);
      }
    };

    fetchUserData();
  }, [navigate]);

  const handleLogout = async () => {
    try {
    await logoutUser();
    navigate("/login");
    } catch (error) {
      console.error("Erreur lors de la déconnexion:", error);
    }
  };

  const handleEditSubmit = async (e) => {
    e.preventDefault();
    setEditError('');
    setEditSuccess('');
    setEditLoading(true);

    try {
      // Validation
      if (editForm.password && editForm.password !== editForm.confirmPassword) {
        setEditError('Les mots de passe ne correspondent pas');
        return;
      }

      // Préparer les données à envoyer
      const updateData = {};
      if (editForm.email) updateData.email = editForm.email;
      if (editForm.password) updateData.password = editForm.password;

      if (Object.keys(updateData).length === 0) {
        setEditError('Veuillez remplir au moins un champ');
        return;
      }

      const response = await updateUser(user.id, updateData);
      
      if (response.success) {
        setEditSuccess('Profil mis à jour avec succès !');
        setUser({ ...user, ...updateData });
        setEditForm({ email: '', password: '', confirmPassword: '' });
        setTimeout(() => {
          setShowEditForm(false);
          setEditSuccess('');
        }, 2000);
      } else {
        setEditError(response.message || 'Erreur lors de la mise à jour');
      }
    } catch (error) {
       console.error('Erreur lors de la mise à jour:', error);
      setEditError('Une erreur est survenue');
    } finally {
      setEditLoading(false);
    }
  };

  const handleEditCancel = () => {
    setShowEditForm(false);
    setEditForm({ email: '', password: '', confirmPassword: '' });
    setEditError('');
    setEditSuccess('');
  };
    // ...existing code...

  if (loading) {
    return (
      <div 
        className="dashboard-container"
        style={{ backgroundImage: `url(${marseilleCalanques})` }}
      >
        <div className="dashboard-overlay" />
        <div className="dashboard-loading">
          <div className="dashboard-loading-spinner"></div>
          <p>Chargement de votre tableau de bord...</p>
        </div>
      </div>
    );
  }

  if (!user) return null;

  return (
    <div 
      className="dashboard-container"
      style={{ backgroundImage: `url(${marseilleCalanques})` }}
    >
      {/* Overlay sombre pour améliorer la lisibilité */}
      <div className="dashboard-overlay" />
      
      {/* Navigation */}
      <Navbar />

      <div className="dashboard-content">
        {/* En-tête de bienvenue */}
        <div className="dashboard-header">
          <Card variant="default" padding="large" className="dashboard-welcome">
            <div className="dashboard-welcome-header">
              <img 
                src={logoCalanque} 
                alt="Logo Calanque" 
                className="dashboard-welcome-logo"
              />
      <h1>Bienvenue {user.firstName} {user.lastName} 👋</h1>
            </div>
            <p>Votre tableau de bord Calanques Paradise</p>
          </Card>
        </div>

        {/* Statistiques et informations */}
        <div className="dashboard-stats">
          {/* Informations utilisateur */}
          <Card variant="default" padding="medium" className="dashboard-card">
            <div className="dashboard-card-header">
              <h3>
                <span className="dashboard-card-icon">👤</span>
                Informations personnelles
              </h3>
              <Button
                variant="outline"
                size="small"
                onClick={() => setShowEditForm(!showEditForm)}
              >
                {showEditForm ? 'Annuler' : 'Modifier'}
              </Button>
            </div>
            
            {!showEditForm ? (
              <>
                <div className="dashboard-info-item">
                  <span className="dashboard-info-label">Email :</span>
                  <span className="dashboard-info-value">{user.email}</span>
                </div>
                <div className="dashboard-info-item">
                  <span className="dashboard-info-label">Membre depuis :</span>
                  <span className="dashboard-info-value">
                    {user.created_at ? formatDate(user.created_at) : 'Non disponible'}
                  </span>
                </div>
                <div className="dashboard-info-item">
                  <span className="dashboard-info-label">Dernière connexion :</span>
                  <span className="dashboard-info-value">
                    {user.updated_at ? formatDate(user.updated_at) : 'Aujourd\'hui'}
                  </span>
                </div>
              </>
            ) : (
              <form onSubmit={handleEditSubmit} className="dashboard-edit-form">
                <Input
                  label="Nouvel email"
                  type="email"
                  value={editForm.email}
                  onChange={e => setEditForm({...editForm, email: e.target.value})}
                  placeholder={user.email}
                />
                
                <Input
                  label="Nouveau mot de passe"
                  type="password"
                  value={editForm.password}
                  onChange={e => setEditForm({...editForm, password: e.target.value})}
                  placeholder="Laisser vide pour ne pas changer"
                />
                
                <Input
                  label="Confirmer le mot de passe"
                  type="password"
                  value={editForm.confirmPassword}
                  onChange={e => setEditForm({...editForm, confirmPassword: e.target.value})}
                  placeholder="Confirmer le nouveau mot de passe"
                />
                
                {editError && (
                  <div className="dashboard-message error">
                    {editError}
                  </div>
                )}
                
                {editSuccess && (
                  <div className="dashboard-message success">
                    {editSuccess}
                  </div>
                )}
                
                <div className="dashboard-edit-actions">
                  <Button
                    type="submit"
                    variant="primary"
                    size="medium"
                    loading={editLoading}
                    disabled={editLoading}
                  >
                    Mettre à jour
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="medium"
                    onClick={handleEditCancel}
                  >
                    Annuler
                  </Button>
                </div>
              </form>
            )}
          </Card>

          {/* Abonnement */}
          <Card variant="default" padding="medium" className="dashboard-card">
            <h3>
              <span className="dashboard-card-icon">💎</span>
              Abonnement
            </h3>
            {subscription ? (
              <>
                <div className="dashboard-info-item">
                  <span className="dashboard-info-label">Type :</span>
                  <span className="dashboard-info-value">{subscription.type}</span>
                </div>
                <div className="dashboard-info-item">
                  <span className="dashboard-info-label">Statut :</span>
                  <span className={`dashboard-status ${subscription.status.toLowerCase()}`}>
                    {getStatusText(subscription.status)}
                  </span>
                </div>
                <div className="dashboard-info-item">
                  <span className="dashboard-info-label">Début :</span>
                  <span className="dashboard-info-value">{formatDate(subscription.start_date)}</span>
                </div>
                <div className="dashboard-info-item">
                  <span className="dashboard-info-label">Fin :</span>
                  <span className="dashboard-info-value">{formatDate(subscription.end_date)}</span>
                </div>
                <div className="dashboard-info-item">
                  <span className="dashboard-info-label">Créé le :</span>
                  <span className="dashboard-info-value">{formatDate(subscription.created_at)}</span>
                </div>
              </>
            ) : (
              <p className="dashboard-no-data">
                Aucun abonnement actif
              </p>
            )}
          </Card>

          {/* Réservations en cours */}
          <Card variant="default" padding="medium" className="dashboard-card">
            <h3>
              <span className="dashboard-card-icon">📅</span>
              Réservations en cours
            </h3>
            {reservations.length > 0 ? (
              <div className="dashboard-reservations">
                {reservations.map((reservation) => (
                  <div key={reservation.id} className="dashboard-reservation-item">
                    <div className="dashboard-reservation-title">
                      {reservation.reservation_name || `Réservation #${reservation.id}`}
                    </div>
                    <div className="dashboard-reservation-date">
                      Du {formatDate(reservation.start_date)} au {formatDate(reservation.end_date)}
                      <br />
                      <span className="dashboard-reservation-people">
                        {reservation.num_people} personne{reservation.num_people > 1 ? 's' : ''}
                      </span>
                      <br />
                      <span className={`dashboard-status ${reservation.status.toLowerCase()}`}>
                        {getStatusText(reservation.status)}
                      </span>
                      <span className={`dashboard-status payment-${reservation.payment_status || 'pending'}`}>
                        {reservation.payment_status === 'paid' ? 'Payée' : 'En attente de paiement'}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="dashboard-no-data">
                Aucune réservation en cours
              </p>
            )}
          </Card>
        </div>

        {/* Actions */}
        <div className="dashboard-actions">
          <Button 
            variant="danger" 
            size="large"
            onClick={handleLogout}
          >
            🚪 Déconnexion
          </Button>
        </div>
      </div>
    </div>
  );
}
