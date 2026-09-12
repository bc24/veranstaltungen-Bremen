import { Link } from "react-router-dom";

export default function SubmitOutcomePage({ outcome }) {
  const success = outcome === "erfolg";

  return (
    <div className="outcome-page">
      {success ? (
        <>
          <h1>Zahlung erfolgreich ✅</h1>
          <p>
            Vielen Dank! Dein Markteintrag wurde bezahlt und liegt jetzt zur redaktionellen Prüfung
            vor. Nach der Freigabe erscheint er im öffentlichen Verzeichnis.
          </p>
        </>
      ) : (
        <>
          <h1>Zahlung abgebrochen</h1>
          <p>
            Die Zahlung wurde nicht abgeschlossen. Ohne erfolgreiche Zahlung kann der Eintrag nicht
            veröffentlicht werden &ndash; es gibt keine kostenlose Basisversion. Du kannst es jederzeit
            erneut versuchen.
          </p>
        </>
      )}
      <Link to="/" className="btn-primary">
        Zurück zum Verzeichnis
      </Link>
    </div>
  );
}
