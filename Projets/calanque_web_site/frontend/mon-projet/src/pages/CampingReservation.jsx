import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { getUser } from "../service/userService";
import { createReservationForCheckout, createReservationCheckoutSession, getCampingsAvailability } from "../service/reservationService";
import Navbar from "../components/Navbar";
import marseilleCalanques from "../assets/marseille_calanques.jpg";
import "../styles/CampingReservation.css";

export default function CampingReservation() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [reservationData, setReservationData] = useState({
    start_date: "",
    end_date: "",
    num_people: 1,
    reservation_name: "",
    camping_id: ""
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [availableCampings, setAvailableCampings] = useState([]);
  const [loadingCampings, setLoadingCampings] = useState(false);
  const [dateError, setDateError] = useState("");
  const navigate = useNavigate();

  // Max: aujourd'hui + 1 an
  const maxDateString = (() => {
    const d = new Date();
    d.setFullYear(d.getFullYear() + 1);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  })();

  useEffect(() => {
    const checkUser = async () => {
      try {
        const userResponse = await getUser();
        if (userResponse.success) {
          setUser(userResponse.data);
        } else {
          navigate("/login");
        }
      } catch (error) {
        console.error("Erreur lors de la vérification de l'utilisateur:", error);
        navigate("/login");
      } finally {
        setLoading(false);
      }
    };
    checkUser();
  }, [navigate]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setReservationData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  useEffect(() => {
    // Validation des dates: départ doit être > arrivée
    if (reservationData.start_date && reservationData.end_date) {
      const start = new Date(reservationData.start_date).getTime();
      const end = new Date(reservationData.end_date).getTime();
      const max = new Date(maxDateString).getTime();
      if (end <= start) {
        setDateError("La date de départ doit être postérieure à la date d'arrivée.");
      } else if (start > max || end > max) {
        setDateError("Les réservations ne peuvent pas dépasser 1 an à partir d'aujourd'hui.");
      } else {
        setDateError("");
      }
    } else {
      setDateError("");
    }

    const fetchCampings = async () => {
      if (!reservationData.start_date || !reservationData.end_date) {
        setAvailableCampings([]);
        setReservationData(prev => ({ ...prev, camping_id: "" }));
        return;
      }
      // Ne pas charger si dates invalides
      if (dateError) {
        setAvailableCampings([]);
        setReservationData(prev => ({ ...prev, camping_id: "" }));
        return;
      }
      try {
        setLoadingCampings(true);
        const res = await getCampingsAvailability(reservationData.start_date, reservationData.end_date);
        if (res.success) {
          // Filtrer campings avec capacité disponible > 0
          const campings = (res.data || []).filter(c => (parseInt(c.available_capacity ?? c.capacity, 10) || 0) > 0);
          setAvailableCampings(campings);
          // Reset si camping choisi n'est plus dispo
          setReservationData(prev => ({
            ...prev,
            camping_id: campings.some(c => String(c.id) === String(prev.camping_id)) ? prev.camping_id : ""
          }));
        } else {
          setAvailableCampings([]);
          setReservationData(prev => ({ ...prev, camping_id: "" }));
        }
      } catch (e) {
        console.error(e);
        setAvailableCampings([]);
        setReservationData(prev => ({ ...prev, camping_id: "" }));
      } finally {
        setLoadingCampings(false);
      }
    };
    fetchCampings();
  }, [reservationData.start_date, reservationData.end_date, dateError]);

  const calculateNights = () => {
    if (!reservationData.start_date || !reservationData.end_date) return 0;
    const startDate = new Date(reservationData.start_date);
    const endDate = new Date(reservationData.end_date);
    const diffTime = endDate.getTime() - startDate.getTime();
    if (diffTime <= 0) return 0;
    const nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return nights;
  };

  const calculateAmount = () => {
    const nights = calculateNights();
    if (nights <= 0) return 0;
    const pricePerPersonPerNight = 25;
    const people = Number(reservationData.num_people) || 0;
    return nights * people * pricePerPersonPerNight;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setSuccess("");
    setIsSubmitting(true);

    try {
      // Blocage si dates invalides
      if (dateError) {
        setError(dateError);
        return;
      }
      const amount = calculateAmount();

      // 1) Créer la réservation (enregistre les infos et le montant)
      const reservationPayload = {
        user_id: user.id,
        camping_id: parseInt(reservationData.camping_id, 10),
        start_date: reservationData.start_date,
        end_date: reservationData.end_date,
        num_people: parseInt(reservationData.num_people),
        reservation_name: reservationData.reservation_name || `${user.firstName} ${user.lastName}`,
        amount: amount
      };

      const createRes = await createReservationForCheckout(reservationPayload);
      if (!createRes.success || !createRes.data?.reservation_id) {
        const extra = createRes.data?.raw ? `\nDétails: ${createRes.data.raw}` : '';
        setError((createRes.message || "Erreur lors de la création de la réservation") + extra);
        return;
      }

      // 2) Créer la session Stripe Checkout
      const sessionRes = await createReservationCheckoutSession({
        user_id: user.id,
        reservation_id: createRes.data.reservation_id,
        amount: amount
      });

      if (!sessionRes.success || !sessionRes.data?.checkout_url) {
        const extra = sessionRes.data?.raw ? `\nDétails: ${sessionRes.data.raw}` : '';
        setError((sessionRes.message || "Erreur lors de la création de la session de paiement") + extra);
        return;
      }

      // 3) Rediriger vers Stripe
      window.location.href = sessionRes.data.checkout_url;
    } catch (error) {
      console.error("Erreur:", error);
      setError("Erreur de connexion. Veuillez réessayer.");
    } finally {
      setIsSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="camping-reservation-container" style={{ backgroundImage: `url(${marseilleCalanques})` }}>
        <div className="camping-reservation-overlay" />
        <div className="camping-reservation-loading">
          <div className="camping-reservation-loading-spinner"></div>
          <p>Chargement...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="camping-reservation-container" style={{ backgroundImage: `url(${marseilleCalanques})` }}>
      <div className="camping-reservation-overlay" />
      
      <Navbar />
      
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-lg-8">
            <div className="camping-reservation-card">
              <div className="text-center mb-4">
                <h1 className="camping-reservation-title">🏕️ Réservation Camping</h1>
                <p className="camping-reservation-subtitle">
                  Réservez votre place dans les calanques de Marseille
                </p>
              </div>

              {error && (
                <div className="alert alert-danger" role="alert">
                  {error}
                </div>
              )}

              {success && (
                <div className="alert alert-success" role="alert">
                  {success}
                </div>
              )}

              <form onSubmit={handleSubmit}>
                <div className="row">
                  <div className="col-md-6">
                    <div className="mb-3">
                      <label htmlFor="start_date" className="form-label">
                        Date d'arrivée
                      </label>
                      <input
                        type="date"
                        className="form-control"
                        id="start_date"
                        name="start_date"
                        value={reservationData.start_date}
                        onChange={handleInputChange}
                        min={new Date().toISOString().split('T')[0]}
                        max={maxDateString}
                        required
                      />
                      {dateError && (
                        <div className="form-text text-danger">{dateError}</div>
                      )}
                    </div>
                  </div>
                  
                  <div className="col-md-6">
                    <div className="mb-3">
                      <label htmlFor="end_date" className="form-label">
                        Date de départ
                      </label>
                      <input
                        type="date"
                        className="form-control"
                        id="end_date"
                        name="end_date"
                        value={reservationData.end_date}
                        onChange={handleInputChange}
                        min={reservationData.start_date || new Date().toISOString().split('T')[0]}
                        max={maxDateString}
                        required
                      />
                      {dateError && (
                        <div className="form-text text-danger">{dateError}</div>
                      )}
                    </div>
                  </div>
                </div>

                <div className="row">
                  <div className="col-md-6">
                    <div className="mb-3">
                      <label htmlFor="camping_id" className="form-label">
                        Camping disponible
                      </label>
                      <select
                        className="form-select"
                        id="camping_id"
                        name="camping_id"
                        value={reservationData.camping_id}
                        onChange={handleInputChange}
                        disabled={!reservationData.start_date || !reservationData.end_date || loadingCampings || availableCampings.length === 0}
                        required
                      >
                        <option value="" disabled>
                          {loadingCampings ? 'Chargement des campings...' : (reservationData.start_date && reservationData.end_date ? 'Sélectionnez un camping' : 'Choisissez d’abord vos dates')}
                        </option>
                        {availableCampings.map(c => (
                          <option key={c.id} value={c.id}>
                            {c.name} (capacité dispo: {c.available_capacity ?? c.capacity})
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>
                  <div className="col-md-6">
                    <div className="mb-3">
                      <label htmlFor="num_people" className="form-label">
                        Nombre de personnes
                      </label>
                      <select
                        className="form-select"
                        id="num_people"
                        name="num_people"
                        value={reservationData.num_people}
                        onChange={handleInputChange}
                        required
                      >
                        {[1, 2, 3, 4, 5, 6, 7, 8].map(num => (
                          <option key={num} value={num}>{num} personne{num > 1 ? 's' : ''}</option>
                        ))}
                      </select>
                    </div>
                  </div>
                  
                  <div className="col-md-6">
                    <div className="mb-3">
                      <label htmlFor="reservation_name" className="form-label">
                        Nom de la réservation (optionnel)
                      </label>
                      <input
                        type="text"
                        className="form-control"
                        id="reservation_name"
                        name="reservation_name"
                        value={reservationData.reservation_name}
                        onChange={handleInputChange}
                        placeholder={`${user?.firstName} ${user?.lastName}`}
                      />
                    </div>
                  </div>
                </div>

                <div className="reservation-summary">
                  <div className="summary-details">
                    <p><strong>Nuits:</strong> {calculateNights()}</p>
                    <p><strong>Prix par nuit:</strong> 25€ / personne</p>
                    <p><strong>Coût total:</strong> {calculateAmount()}€</p>
                  </div>
                </div>

                <div className="text-center mt-4">
                  <button
                    type="submit"
                    className="btn btn-primary btn-lg reservation-submit-btn"
                    disabled={isSubmitting || !!dateError || calculateAmount() <= 0 || !reservationData.start_date || !reservationData.end_date || !reservationData.camping_id}
                  >
                    <span className="d-inline-flex align-items-center">
                      {isSubmitting && (
                        <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      )}
                      <span>
                        {isSubmitting ? 'Traitement...' : `Réserver (${calculateAmount()}€)`}
                      </span>
                    </span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
