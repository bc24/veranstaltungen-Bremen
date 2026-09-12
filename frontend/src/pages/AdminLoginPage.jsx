import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { adminLogin } from "../api/client.js";

export default function AdminLoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  async function handleSubmit(e) {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const { token } = await adminLogin(email, password);
      localStorage.setItem("adminToken", token);
      navigate("/admin");
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="admin-login-page">
      <h1>Admin-Login</h1>
      <form onSubmit={handleSubmit} className="submit-form">
        <label>
          E-Mail
          <input
            type="email"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </label>
        <label>
          Passwort
          <input
            type="password"
            required
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
        </label>
        {error && <div className="error-box">{error}</div>}
        <button type="submit" className="btn-primary" disabled={loading}>
          {loading ? "Anmelden …" : "Anmelden"}
        </button>
      </form>
    </div>
  );
}
