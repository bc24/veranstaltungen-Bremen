import { Routes, Route, Link, NavLink } from "react-router-dom";
import HomePage from "./pages/HomePage.jsx";
import SubmitPage from "./pages/SubmitPage.jsx";
import SubmitOutcomePage from "./pages/SubmitOutcomePage.jsx";
import AdminLoginPage from "./pages/AdminLoginPage.jsx";
import AdminDashboardPage from "./pages/AdminDashboardPage.jsx";

export default function App() {
  return (
    <div className="app-shell">
      <header className="site-header">
        <div className="site-header-inner">
          <Link to="/" className="brand">
            <span className="brand-emoji" aria-hidden="true">🎪</span>
            <div>
              <div className="brand-title">Marktverzeichnis</div>
              <div className="brand-subtitle">Kuratierte Kürbis-, Weihnachts- &amp; Wochenmärkte</div>
            </div>
          </Link>
          <nav className="main-nav">
            <NavLink to="/" end>
              Verzeichnis
            </NavLink>
            <NavLink to="/einreichen">Markt eintragen</NavLink>
            <NavLink to="/admin">Admin</NavLink>
          </nav>
        </div>
      </header>

      <main>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/einreichen" element={<SubmitPage />} />
          <Route path="/einreichen/erfolg" element={<SubmitOutcomePage outcome="erfolg" />} />
          <Route path="/einreichen/abgebrochen" element={<SubmitOutcomePage outcome="abgebrochen" />} />
          <Route path="/admin/login" element={<AdminLoginPage />} />
          <Route path="/admin" element={<AdminDashboardPage />} />
        </Routes>
      </main>

      <footer className="site-footer">
        <p>
          Jeder Eintrag im Verzeichnis ist kostenpflichtig und wird vor Veröffentlichung redaktionell
          geprüft &ndash; es gibt keine kostenlose Basisversion.
        </p>
      </footer>
    </div>
  );
}
